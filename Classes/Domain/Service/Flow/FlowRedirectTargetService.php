<?php
namespace Sandstorm\UserManagement\Domain\Service\Flow;

use Sandstorm\UserManagement\Domain\Service\RedirectTargetServiceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerContext;

class FlowRedirectTargetService implements RedirectTargetServiceInterface
{

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
     * @return string|ActionRequest|NULL
     */
    public function onAuthenticationSuccess(ControllerContext $controllerContext, ActionRequest $originalRequest = null)
    {
        if (is_array($this->redirectAfterLogin)
            && array_key_exists('action', $this->redirectAfterLogin)
            && array_key_exists('controller', $this->redirectAfterLogin)
            && array_key_exists('package', $this->redirectAfterLogin)
        ) {
            $controllerArguments = [];
            if (array_key_exists('controllerArguments', $this->redirectAfterLogin) &&
                is_array($this->redirectAfterLogin['controllerArguments'])
            ) {
                $controllerArguments = $this->redirectAfterLogin['controllerArguments'];
            }

            return $controllerContext->getUriBuilder()
                ->reset()
                ->setCreateAbsoluteUri(true)
                ->uriFor($this->redirectAfterLogin['action'], $controllerArguments,
                    $this->redirectAfterLogin['controller'], $this->redirectAfterLogin['package']);
        }
    }

    /**
     * @param ControllerContext $controllerContext
     * @return string|ActionRequest|NULL
     */
    public function onLogout(ControllerContext $controllerContext)
    {
        if (is_array($this->redirectAfterLogout)
            && array_key_exists('action', $this->redirectAfterLogout)
            && array_key_exists('controller', $this->redirectAfterLogout)
            && array_key_exists('package', $this->redirectAfterLogout)
        ) {
            $controllerArguments = [];
            if (array_key_exists('controllerArguments', $this->redirectAfterLogout) &&
                is_array($this->redirectAfterLogout['controllerArguments'])
            ) {
                $controllerArguments = $this->redirectAfterLogout['controllerArguments'];
            }

            return $controllerContext->getUriBuilder()
                ->reset()
                ->setCreateAbsoluteUri(true)
                ->uriFor($this->redirectAfterLogout['action'], $controllerArguments,
                    $this->redirectAfterLogout['controller'], $this->redirectAfterLogout['package']);
        }
    }
}
