<?php
namespace Sandstorm\UserManagement\Domain\Service\Flow;

use Sandstorm\UserManagement\Domain\Service\RedirectTargetServiceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Controller\ControllerContext;

class FlowRedirectTargetService implements RedirectTargetServiceInterface {

    /**
     * @Flow\InjectConfiguration(path="redirect.afterLogin")
     * @var string
     */
    protected $redirectAfterLogin;

    /**
     * @Flow\InjectConfiguration(path="redirect.afterLogout")
     * @var string
     */
    protected $redirectAfterLogout;

    /**
     * @param ControllerContext $controllerContext
     * @param ActionRequest|NULL $originalRequest
     * @return string|\TYPO3\Flow\Mvc\ActionRequest|NULL
     */
    public function onAuthenticationSuccess(ControllerContext $controllerContext, \TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL) {
        if (is_array($this->redirectAfterLogin)
            && array_key_exists('action', $this->redirectAfterLogin)
            && array_key_exists('controller', $this->redirectAfterLogin)
            && array_key_exists('package', $this->redirectAfterLogin)
        ) {
            return $controllerContext->getUriBuilder()->reset()->setCreateAbsoluteUri(TRUE)->uriFor($this->redirectAfterLogin['action'], [], $this->redirectAfterLogin['controller'], $this->redirectAfterLogin['package']);
        }
    }

    /**
     * @param ControllerContext $controllerContext
     * @return string|\TYPO3\Flow\Mvc\ActionRequest|NULL
     */
    public function onLogout(ControllerContext $controllerContext) {
        if (is_array($this->redirectAfterLogout)
            && array_key_exists('action', $this->redirectAfterLogout)
            && array_key_exists('controller', $this->redirectAfterLogout)
            && array_key_exists('package', $this->redirectAfterLogout)
        ) {
            return $controllerContext->getUriBuilder()->reset()->setCreateAbsoluteUri(TRUE)->uriFor($this->redirectAfterLogout['action'], [], $this->redirectAfterLogout['controller'], $this->redirectAfterLogout['package']);
        }
    }
}
