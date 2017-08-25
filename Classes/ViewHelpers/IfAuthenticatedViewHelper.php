<?php
namespace Sandstorm\UserManagement\ViewHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Context;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class IfAuthenticatedViewHelper extends AbstractConditionViewHelper
{

    /**
     * Renders <f:then> child if any account is currently authenticated, otherwise renders <f:else> child.
     *
     * @param string $authenticationProviderName
     * @return string the rendered string
     * @api
     */
    public function render($authenticationProviderName = 'Sandstorm.UserManagement:Login')
    {
        if (static::evaluateCondition($this->arguments, $this->renderingContext)) {
            return $this->renderThenChild();
        }

        return $this->renderElseChild();
    }

    /**
     * @param null $arguments
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    protected static function evaluateCondition($arguments = null, RenderingContextInterface $renderingContext)
    {
        $objectManager = $renderingContext->getObjectManager();
        /** @var Context $securityContext */
        $securityContext = $objectManager->get(Context::class);
        $activeTokens = $securityContext->getAuthenticationTokens();


        /** @var $token TokenInterface */
        foreach ($activeTokens as $token) {
            if ($token->getAuthenticationProviderName() === $arguments['authenticationProviderName'] && $token->isAuthenticated()) {
                return true;
            }
        }
        return false;
    }
}
