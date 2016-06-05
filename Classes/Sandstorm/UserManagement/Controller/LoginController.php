<?php
namespace Sandstorm\UserManagement\Controller;

use Sandstorm\UserManagement\Domain\Service\RedirectTargetServiceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController;

class LoginController extends AbstractAuthenticationController
{

    /**
     * @Flow\Inject
     * @var RedirectTargetServiceInterface
     */
    protected $redirectTargetService;

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
     * @throws \TYPO3\Flow\Exception
     * @return string
     */
    protected function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL)
    {
        $result = $this->redirectTargetService->onAuthenticationSuccess($this->controllerContext, $originalRequest);

        if (is_string($result)) {
            $this->redirectToUri($result);
        } elseif ($result instanceof ActionRequest) {
            $this->redirectToRequest($result);
        }

        if ($result === null) {
            $this->view->assign('account', $this->securityContext->getAccount());
        } else {
            throw new Exception('RedirectTargetServiceInterface::onAuthenticationSuccess must return either null, an URL string or an ActionRequest object, but was: ' . gettype($result) . ' - ' . get_class($result), 1464164500);
        }
    }

    /**
     * Logs all active tokens out.
     */
    public function logoutAction()
    {
        parent::logoutAction();
        $result = $this->redirectTargetService->onLogout($this->controllerContext);

        if (is_string($result)) {
            $this->redirectToUri($result);
        } elseif ($result instanceof ActionRequest) {
            $this->redirectToRequest($result);
        }

        if ($result === null) {
            // Default: redirect to login
            $this->redirect('login');
        } else {
            throw new Exception('RedirectTargetServiceInterface::onLogout must return either null, an URL string or an ActionRequest object, but was: ' . gettype($result) . ' - ' . get_class($result), 1464164500);
        }
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
