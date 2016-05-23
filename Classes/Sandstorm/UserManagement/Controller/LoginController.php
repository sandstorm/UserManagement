<?php
namespace Sandstorm\UserManagement\Controller;

use Sandstorm\UserManagement\Domain\Service\UserManagementService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController;

class LoginController extends AbstractAuthenticationController {

	/**
	 * @var UserManagementService
	 * @Flow\Inject
	 */
	protected $userManagementService;

	/**
	 * Is called after a request has been authenticated.
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return string
	 */
	protected function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL) {

		// TODO Make this configurable
		if ($originalRequest !== NULL) {
			$this->redirectToRequest($originalRequest);
		}

		//Determine if the logged-in user is a regular user or an admin, and redirect to the correct area
		if ($this->userManagementService->isAdministrator($this->securityContext->getAccount())) {
			$this->redirect('index', 'Administration\Administration');
		}
		$this->redirect('index', 'UserArea\UserArea');
	}

	/**
	 * Logs all active tokens out.
	 */
	public function logoutAction() {
		parent::logoutAction();
		$this->addFlashMessage('Sie wurden ausgeloggt.', 'Logout', Message::SEVERITY_OK);
		$this->redirect('login', 'Login');
	}

	/**
	 * Disable the default error flash message
	 *
	 * @return boolean
	 */
	protected function getErrorFlashMessage() {
		return FALSE;
	}

}
