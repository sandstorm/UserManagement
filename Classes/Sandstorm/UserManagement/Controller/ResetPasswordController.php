<?php
namespace Sandstorm\UserManagement\Controller;

use Sandstorm\UserManagement\Domain\Model\ResetPasswordFlow;
use Sandstorm\UserManagement\Domain\Repository\ResetPasswordFlowRepository;
use Sandstorm\UserManagement\Domain\Service\EmailService;
use Sandstorm\UserManagement\Domain\Service\UserCreationServiceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Security\AccountRepository;

/**
 * Handle the "forgot password" flow
 */
class ResetPasswordController extends ActionController
{

    /**
     * @Flow\Inject
     * @var ResetPasswordFlowRepository
     */
    protected $resetPasswordFlowRepository;

    /**
     * @Flow\Inject
     * @var UserCreationServiceInterface
     */
    protected $userCreationService;

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
     * @Flow\SkipCsrfProtection
     */
    public function indexAction()
    {
    }

    /**
     * @param ResetPasswordFlow $resetPasswordFlow
     */
    public function requestTokenAction(ResetPasswordFlow $resetPasswordFlow)
    {

        $account = $this->accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($resetPasswordFlow->getEmail(), 'Sandstorm.UserManagement:Login');

        if ($account !== NULL) {
            $alreadyExistingFlows = $this->resetPasswordFlowRepository->findByEmail($resetPasswordFlow->getEmail());
            if (count($alreadyExistingFlows) > 0) {
                foreach ($alreadyExistingFlows as $alreadyExistingFlow) {
                    $this->resetPasswordFlowRepository->remove($alreadyExistingFlow);
                }
            }

            // Send out a confirmation mail
            $resetPasswordLink = $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor(
                'insertNewPassword',
                ['token' => $resetPasswordFlow->getResetPasswordToken()],
                'ResetPassword');

            $this->emailService->sendTemplateBasedEmail(
                'ResetPasswordToken',
                'Passwort zurücksetzen für ' . $this->applicationName,
                [$this->senderEmailAddress => $this->applicationName],
                [$resetPasswordFlow->getEmail()],
                [
                    'resetPasswordLink' => $resetPasswordLink,
                    'applicationName' => $this->applicationName,
                    'resetPasswordFlow' => $resetPasswordFlow
                ]
            );

            $this->resetPasswordFlowRepository->add($resetPasswordFlow);
        }


        $this->view->assign('resetPasswordFlow', $resetPasswordFlow);
        $this->view->assign('account', $account);
    }

    /**
     * @param string $token
     */
    public function insertNewPasswordAction($token)
    {
        /* @var $resetPasswordFlow \Sandstorm\UserManagement\Domain\Model\ResetPasswordFlow */
        $resetPasswordFlow = $this->resetPasswordFlowRepository->findOneByResetPasswordToken($token);
        if (!$resetPasswordFlow) {
            $this->view->assign('tokenNotFound', true);
            return;
        }

        if (!$resetPasswordFlow->hasValidResetPasswordToken()) {
            $this->view->assign('tokenTimeout', true);
            return;
        }

        $this->view->assign('success', true);

        $this->view->assign('resetPasswordFlow', $resetPasswordFlow);
    }


    /**
     * @param ResetPasswordFlow $resetPasswordFlow
     */
    public function updatePasswordAction(ResetPasswordFlow $resetPasswordFlow)
    {
        $account = $this->accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($resetPasswordFlow->getEmail(), 'Sandstorm.UserManagement:Login');

        if (!$account) {
            $this->view->assign('accountNotFound', true);
            return;
        }

        $this->view->assign('success', true);
        $account->setCredentialsSource($resetPasswordFlow->getEncryptedPassword());
        $this->accountRepository->update($account);
        $this->resetPasswordFlowRepository->remove($resetPasswordFlow);
    }
}
