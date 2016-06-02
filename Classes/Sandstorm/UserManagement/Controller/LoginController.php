<?php
namespace Sandstorm\UserManagement\Controller;

use Sandstorm\UserManagement\Domain\Service\LoginRedirectTargetServiceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController;

class LoginController extends AbstractAuthenticationController
{

    /**
     * @Flow\Inject
     * @var LoginRedirectTargetServiceInterface
     */
    protected $loginRedirectTargetService;

    /**
     * SkipCsrfProtection is needed here because we will have errors otherwise if we render multiple
     * plugins on the same page
     *
     * @return void
     * @Flow\SkipCsrfProtection
     */
    public function loginAction()
    {
        $this->view->assign('account', $this->securityContext->getAccount());
    }

    /**
     * Is called after a request has been authenticated.
     *
     * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
     * @return string
     */
    protected function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL)
    {
        $result = $this->loginRedirectTargetService->onAuthenticationSuccess($this->controllerContext, $originalRequest);

        if (is_string($result)) {
            $this->redirectToUri($result);
        } elseif ($result instanceof ActionRequest) {
            $this->redirectToRequest($result);
        }

        if ($result === null) {
            $this->view->assign('account', $this->securityContext->getAccount());
        } else {
            throw new Exception('LoginRedirectTargetServiceInterface::onAuthenticationSuccess must return either null, an URL string or an ActionRequest object, but was: ' . gettype($result) . ' - ' . get_class($result), 1464164500);
        }
    }

    /**
     * Logs all active tokens out.
     */
    public function logoutAction()
    {
        parent::logoutAction();
        $this->addFlashMessage('Sie wurden ausgeloggt.', 'Logout', Message::SEVERITY_OK);
        $this->redirect('login', 'Login');
    }

    /**
     * Disable the default error flash message
     *
     * @return boolean
     */
    protected function getErrorFlashMessage()
    {
        return FALSE;
    }

}
