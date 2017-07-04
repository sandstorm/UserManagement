<?php
namespace Sandstorm\UserManagement\Controller;

use Neos\Error\Messages\Error;
use Neos\Flow\Http\Request;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Security\Exception\AuthenticationRequiredException;
use Sandstorm\UserManagement\Domain\Service\RedirectTargetServiceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController;

class LoginController extends AbstractAuthenticationController
{

    /**
     * @Flow\Inject
     * @var RedirectTargetServiceInterface
     */
    protected $redirectTargetService;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="authFailedMessage.title")
     */
    protected $loginFailedTitle;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="authFailedMessage.body")
     */
    protected $loginFailedBody;

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
        $this->view->assign('node', $this->request->getInternalArgument('__node'));
    }

    /**
     * Is called after a request has been authenticated.
     *
     * @param \Neos\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
     * @throws \Neos\Flow\Exception
     * @return string
     */
    protected function onAuthenticationSuccess(ActionRequest $originalRequest = null)
    {
        $this->emitAuthenticationSuccess($this->controllerContext, $originalRequest);

        $result = $this->redirectTargetService->onAuthenticationSuccess($this->controllerContext, $originalRequest);
        if (is_string($result)) {
            $this->redirectToUri($result);
        } elseif ($result instanceof ActionRequest) {
            $this->redirectToRequest($result);
        }

        if ($result === null) {
            $this->view->assign('account', $this->securityContext->getAccount());
        } else {
            throw new Exception('RedirectTargetServiceInterface::onAuthenticationSuccess must return either null, an URL string or an ActionRequest object, but was: ' .
                gettype($result) . ' - ' . get_class($result), 1464164500);
        }
    }

    /**
     * Is called if authentication failed.
     *
     * Override this method in your login controller to take any
     * custom action for this event. Most likely you would want
     * to redirect to some action showing the login form again.
     *
     * @param \Neos\Flow\Security\Exception\AuthenticationRequiredException $exception The exception thrown while the authentication process
     * @return void
     */
    protected function onAuthenticationFailure(AuthenticationRequiredException $exception = null)
    {
        $this->emitAuthenticationFailure($this->controllerContext, $exception);

        $this->flashMessageContainer->addMessage(new Error($this->loginFailedBody,
            ($exception === null ? 1347016771 : $exception->getCode()), [], $this->loginFailedTitle));
    }

    /**
     * Logs all active tokens out.
     */
    public function logoutAction()
    {
        parent::logoutAction();

        $this->emitLogout($this->controllerContext);

        $result = $this->redirectTargetService->onLogout($this->controllerContext);

        if (is_string($result)) {
            // This might be an issue in Neos; when embedding this as a plugin on a login-protected page that is no longer visible after logout.
            // It seems that $this->redirectToUri() does not work, because the parent response is still rendered (which leads to exceptions).
            // So we build our own version of redirectToUri() and die() afterwards to prevent the response bubbling.
            $escapedUri = htmlentities($result, ENT_QUOTES, 'utf-8');
            header('Location: ' . $escapedUri);
            header('Status: ' . 303);
            echo '<html><head><meta http-equiv="refresh" content="' . intval(0) . ';url=' . $escapedUri .
                '"/></head></html>';
            die();
        } elseif ($result instanceof ActionRequest) {
            $this->redirectToRequest($result);
        }

        if ($result === null) {
            // Default: redirect to login
            $this->redirect('login');
        } else {
            throw new Exception('RedirectTargetServiceInterface::onLogout must return either null, an URL string or an ActionRequest object, but was: ' .
                gettype($result) . ' - ' . get_class($result), 1464164500);
        }
    }

    /**
     * Disable the default error flash message
     *
     * @return boolean
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

    /**
     * @param ControllerContext $controllerContext
     * @param Request $originalRequest
     * @Flow\Signal
     */
    protected function emitAuthenticationSuccess(ControllerContext $controllerContext, Request $originalRequest = null)
    {
    }

    /**
     * @param ControllerContext $controllerContext
     * @param AuthenticationRequiredException $exception
     * @Flow\Signal
     */
    protected function emitAuthenticationFailure(ControllerContext $controllerContext, AuthenticationRequiredException $exception = null)
    {
    }

    /**
     * @param ControllerContext $controllerContext
     * @Flow\Signal
     */
    protected function emitLogout(ControllerContext $controllerContext)
    {
    }
}
