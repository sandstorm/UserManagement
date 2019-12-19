<?php
namespace Sandstorm\UserManagement\Controller;

use Neos\Error\Messages\Error;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Security\Exception\AuthenticationRequiredException;
use Sandstorm\UserManagement\Domain\Service\RedirectTargetServiceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController;
use Neos\Flow\Mvc\FlashMessage\FlashMessageContainer;

use Neos\Flow\Core\Bootstrap;

class LoginController extends AbstractAuthenticationController
{

    /**
     * Bootstrap for retrieving the current HTTP request
     *
     * @Flow\Inject
     * @var Bootstrap
     */
    protected $bootstrap;

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
     * @var FlashMessageContainer
     */
    protected $flashMessageContainer;

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

        $this->flashMessageContainer = new FlashMessageContainer();

        //TODO: This does not work yet. There is no error but it will not be displayed.
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
            $this->redirectToUriAndShutdown($result);
        } elseif ($result instanceof ActionRequest) {
            $this->redirectToRequest($result);
        } else if ($result === null) {
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
     * @param ActionRequest $originalRequest
     * @Flow\Signal
     */
    protected function emitAuthenticationSuccess(ControllerContext $controllerContext, ActionRequest $originalRequest = null)
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

    /**
     * @param string $result
     */
    protected function redirectToUriAndShutdown(string $result)
    {
        $escapedUri = htmlentities($result, ENT_QUOTES, 'utf-8');


        //TODO: Switch to ActionRequest. This does not work anymore.
        $response = $this->bootstrap->getActiveRequestHandler()->getHttpResponse(); /** @var  \Neos\Flow\Http\Response $response*/

        //TODO: When switched to ActionRequest then you can make this in one line with ->setRedirectUri()
        $response->setHeader('Location', $escapedUri);
        $response->setHeader('Status', '303');

        $response->setContent('<html><head><meta http-equiv="refresh" content="0;url=' . $escapedUri . '"/></head></html>');
        $response->send();

        $this->bootstrap->shutdown(Bootstrap::RUNLEVEL_RUNTIME);
        exit();
    }
}
