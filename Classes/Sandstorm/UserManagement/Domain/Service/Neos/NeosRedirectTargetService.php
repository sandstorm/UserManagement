<?php
namespace Sandstorm\UserManagement\Domain\Service\Neos;

use Sandstorm\UserManagement\Domain\Service\RedirectTargetServiceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Controller\ControllerContext;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Neos\Service\LinkingService;

class NeosRedirectTargetService implements RedirectTargetServiceInterface
{

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function onAuthenticationSuccess(ControllerContext $controllerContext, \TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL)
    {
        /** @var ActionRequest $actionRequest */
        $actionRequest = $controllerContext->getRequest();
        if ($actionRequest->getInternalArgument('__redirectAfterLogin')) {
            return $this->getNodeLinkingService()->createNodeUri($controllerContext, $actionRequest->getInternalArgument('__redirectAfterLogin'));
        }
    }
    
    public function onLogout(ControllerContext $controllerContext){
        /** @var ActionRequest $actionRequest */
        $actionRequest = $controllerContext->getRequest();
        if ($actionRequest->getInternalArgument('__redirectAfterLogout')) {
            return $this->getNodeLinkingService()->createNodeUri($controllerContext, $actionRequest->getInternalArgument('__redirectAfterLogout'));
        }
    }

    /**
     * @return LinkingService
     */
    protected function getNodeLinkingService()
    {
        return $this->objectManager->get(LinkingService::class);
    }
}
