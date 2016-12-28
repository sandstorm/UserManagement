<?php
namespace Sandstorm\UserManagement\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerContext;

/**
 * @api
 */
interface RedirectTargetServiceInterface
{
    /**
     * implement this method to customize the redirection target after successful login.
     * You need to return an absolute or relative URL as string, if you want to redirect.
     * Alternatively, you can return a Request object to redirect to this request.
     * Otherwise, just return NULL.
     *
     * @param ControllerContext $controllerContext
     * @param ActionRequest $originalRequest The request that was intercepted by the security framework before authentication, NULL if there was none
     * @return string|ActionRequest|NULL
     */
    public function onAuthenticationSuccess(
        ControllerContext $controllerContext,
        ActionRequest $originalRequest = null
    );


    /**
     * implement this method to customize the redirection target after logout.
     * You need to return an absolute or relative URL as string, if you want to redirect.
     * Alternatively, you can return a Request object to redirect to this request.
     * Otherwise, just return NULL.
     *
     * @param ControllerContext $controllerContext
     * @return string|ActionRequest|NULL
     */
    public function onLogout(ControllerContext $controllerContext);
}
