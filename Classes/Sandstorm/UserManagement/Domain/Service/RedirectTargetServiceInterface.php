<?php
namespace Sandstorm\UserManagement\Domain\Service;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ControllerContext;

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
     * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework before authentication, NULL if there was none
     * @return string|\TYPO3\Flow\Mvc\ActionRequest|NULL
     */
    public function onAuthenticationSuccess(ControllerContext $controllerContext, \TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL);


    /**
     * implement this method to customize the redirection target after logout.
     * You need to return an absolute or relative URL as string, if you want to redirect.
     * Alternatively, you can return a Request object to redirect to this request.
     * Otherwise, just return NULL.
     *
     * @param ControllerContext $controllerContext
     * @return string|\TYPO3\Flow\Mvc\ActionRequest|NULL
     */
    public function onLogout(ControllerContext $controllerContext);
}
