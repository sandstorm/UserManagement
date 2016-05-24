<?php
namespace Sandstorm\UserManagement\Controller;

use Sandstorm\UserManagement\Domain\Model\User;
use Sandstorm\UserManagement\Domain\Repository\UserRepository;
use Sandstorm\UserManagement\Domain\Service\UserManagementService;
use Sandstorm\UserManagement\Domain\Service\EmailService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\Flow\Security\AccountRepository;
use TYPO3\Flow\Utility\Now;


/**
 * Class AccountController
 *
 * Handles the "Forgotten Password" and the "Account Activation" processes.
 */
class AccountController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var Now
	 */
	protected $now;

	/**
	 * @Flow\Inject
	 * @var UserRepository
	 */
	protected $userRepository;

	/**
	 * @Flow\Inject
	 * @var AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var EmailService
	 */
	protected $emailService;

	/**
	 * @var string
	 * @Flow\InjectConfiguration(path="senderEmailAddress")
	 */
	protected $senderEmailAddress;

	/**
	 * @var string
	 * @Flow\InjectConfiguration(path="applicationName")
	 */
	protected $applicationName;

	/**
	 * @var string
	 * @Flow\InjectConfiguration(path="userController.targetPackage")
	 */
	protected $targetPackage;

	/**
	 * @var string
	 * @Flow\InjectConfiguration(path="userController.targetController")
	 */
	protected $targetController;


	// ------ The following methods manage the "reset password" process

	/**
	 * Show a "forgot password" form.
	 * @return string
	 */
	public function requestPasswordTokenAction() {

	}

	/**
	 * Send a password reset token.
	 * @param string $accountName
	 * @return string
	 */
	public function sendPasswordTokenAction($accountName) {
		$account = $this->accountRepository->findOneByAccountIdentifier($accountName);

		if ($account == NULL) {
			$this->addFlashMessage('Der Account mit der Email-Adresse "%s" wurde nicht gefunden. Bitte prüfen Sie Ihre Eingabe!', 'Dieser Account existiert nicht', Message::SEVERITY_WARNING, array($accountName));
			$this->redirect('requestPasswordToken');
		}

		/* @var $user User */
		$user = $this->userRepository->findOneByAccount($account);
		$user->generateResetPasswordToken();
		$this->userRepository->update($user);

		$resetPasswordLink = $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor(
			'resetPassword',
			['token' => $user->getResetPasswordToken()],
			$this->targetController,
			$this->targetPackage);

		$this->emailService->sendTemplateBasedEmail(
			'ResetPasswordToken',
			'Passwort-Änderung Ihres Accounts',
			[$this->senderEmailAddress => $this->applicationName . ' Passwort-Änderung'],
			[$user->getEmail() => $user->getFullName()],
			[
				'resetPasswordLink' => $resetPasswordLink,
				'applicationName' => $this->applicationName,
				'user' => $user
			]
		);

		$this->view->assign('user', $user);
	}

	/**
	 * Show a form to enter a new password.
	 *
	 * @param string $token
	 */
	public function resetPasswordAction($token) {
		/* @var $user User */
		$user = $this->userRepository->findOneByResetPasswordToken($token);

		$this->view->assign('token', $token);
		$this->view->assign('tokenValid', $user && $user->hasValidResetPasswordToken());
	}

	/**
	 * Update a password.
	 *
	 * @param string $token
	 * @param string $password
	 * @param string $passwordconfirmation
	 * @return string
	 * @throws \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
	 */
	public function updatePasswordAction($token, $password, $passwordconfirmation) {

		/* @var $user User */
		$user = $this->userRepository->findOneByResetPasswordToken($token);
		if ($user === NULL) {
			$this->addFlashMessage('Dieser Link ist leider ungültig. Der Link zum Zurücksetzen des Passworts ist nur 24 Stunden lang gültig. Bitte fordern Sie ggf. einen neuen Link an!', 'Ungültiger Passwort-Link', Message::SEVERITY_ERROR);
			$this->redirect('requestPasswordToken');
		}

		if (strlen($password) < 8) {
			$this->addFlashMessage('Dieses Passwort ist zu kurz. Bitte verwenden Sie mindestens eine Länge von 8 Zeichen.', 'Passwort zu kurz', Message::SEVERITY_ERROR);
			$this->redirect('resetPassword', null, null, ['token' => $token]);
		}

		if ($password !== $passwordconfirmation) {
			$this->addFlashMessage('Die Passwörter stimmen nicht überein. Bitte prüfen Sie Ihre Eingaben.', 'Passwörter stimmen nicht überein', Message::SEVERITY_ERROR);
			$this->redirect('resetPassword', null, null, ['token' => $token]);
		}

		$user->updatePassword($password);
		$this->userRepository->update($user);

		$this->addFlashMessage('Ihr Passwort wurde zurückgesetzt. Sie können sich jetzt einloggen.', 'Passwort geändert', Message::SEVERITY_OK);
		$this->redirect('login', 'Login');
	}


	// ------ The following methods manage the "account activation" process

	/**
	 * Requests a new activation token for this user.
	 *
	 * @return string
	 */
	public function requestActivationTokenAction() {

	}

	/**
	 * Sends out a new activation token for this user.
	 *
	 * @param string $accountName
	 * @return string
	 */
	public function sendActivationTokenAction($accountName) {

		/* @var $user User */
		$user = $this->userRepository->findOneByEmail($accountName);

		if ($user == NULL) {
			$this->addFlashMessage('Der Account mit der Email-Adresse "%s" wurde nicht gefunden. Bitte prüfen Sie Ihre Eingabe!', 'Dieser Account existiert nicht', Message::SEVERITY_WARNING, array($accountName));
			$this->redirect('requestActivationToken');
		}

		if ($user->isActive()) {
			$this->addFlashMessage('Der Account mit der Email-Adresse "%s" wurde nicht gefunden. Bitte prüfen Sie Ihre Eingabe!', 'Dieser Account existiert nicht', Message::SEVERITY_WARNING, array($accountName));
			$this->redirect('requestActivationToken');
		}

		$user->setNewActivationTokenRequested(TRUE);
		$user->generateActivationToken();
		$this->userRepository->update($user);

		$activationLink = $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor(
			'activate',
			['token' => $user->getActivationToken()],
			$this->targetController,
			$this->targetPackage);

		$this->emailService->sendTemplateBasedEmail(
			'ActivationToken',
			'Account-Aktivierung für ' . $this->applicationName,
			[$this->senderEmailAddress => $this->applicationName],
			[$user->getEmail() => $user->getFullName()],
			[
				'activationLink' => $activationLink,
				'applicationName' => $this->applicationName,
				'user' => $user
			]
		);

		$this->view->assign('user', $user);
	}

	/**
	 * Show an account activation (=password entry) form.
	 *
	 * @param string $token
	 */
	public function activateAction($token) {
		/* @var $user User */
		$user = $this->userRepository->findOneByActivationToken($token);

		if ($user === NULL) {
			$this->addFlashMessage('Dieser Link ist ungültig. Bitte fordern Sie ggf. einen neuen Link an!', 'Ungültiger Aktivierungslink', Message::SEVERITY_ERROR);
			$this->redirect('requestActivationToken');
		}

		$this->view->assign('tokenValid', $user->hasValidActivationToken());
		$this->view->assign('token', $token);
		$this->view->assign('user', $user);
	}

	/**
	 * Create the account.
	 *
	 * @param string $token
	 * @param string $password
	 * @param string $passwordconfirmation
	 * @return string
	 * @throws \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
	 */
	public function createAction($token, $password, $passwordconfirmation) {
		/* @var $user User */
		$user = $this->userRepository->findOneByActivationToken($token);
		if ($user === NULL) {
			$this->addFlashMessage('Dieser Link ist ungültig. Bitte fordern Sie ggf. einen neuen Link an!', 'Ungültiger Aktivierungslink', Message::SEVERITY_ERROR);
			$this->redirect('requestActivationToken');
		}

		if (!$user->hasValidActivationToken()) {
			$this->addFlashMessage('Dieser Link ist abgelaufen. Aktivierungslinks sind nur eine begrenzte Zeitspanne lang gültig. Bitte fordern Sie ggf. einen neuen Link an!', 'Ungültiger Aktivierungslink', Message::SEVERITY_ERROR);
			$this->redirect('requestActivationToken');
		}

		if (strlen($password) < 8) {
			$this->addFlashMessage('Dieses Passwort ist zu kurz. Bitte verwenden Sie mindestens eine Länge von 8 Zeichen.', 'Passwort zu kurz', Message::SEVERITY_ERROR);
			$this->redirect('activate', null, null, ['token' => $token]);
		}

		if ($password !== $passwordconfirmation) {
			$this->addFlashMessage('Die Passwörter stimmen nicht überein. Bitte prüfen Sie Ihre Eingaben.', 'Passwörter stimmen nicht überein', Message::SEVERITY_ERROR);
			$this->redirect('activate', null, null, ['token' => $token]);
		}

//		$this->userManagementService->createUserAccount($user, $password);
		$this->userRepository->update($user);

		$this->addFlashMessage('Ihr Account ist jetzt aktiv. Sie können sich mit ihrem gewählten Passwort einloggen.', 'Account aktiviert', Message::SEVERITY_OK);
		$this->redirect('login', 'Login');
	}
}
