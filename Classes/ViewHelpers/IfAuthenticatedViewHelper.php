<?php
namespace Sandstorm\UserManagement\ViewHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Context;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractConditionViewHelper;

class IfAuthenticatedViewHelper extends AbstractConditionViewHelper
{
    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;


    /**
     * Renders <f:then> child if any account is currently authenticated, otherwise renders <f:else> child.
     *
     * @param string $authenticationProviderName
     * @return string the rendered string
     * @api
     */
    public function render($authenticationProviderName = 'Sandstorm.UserManagement:Login')
    {
        $activeTokens = $this->securityContext->getAuthenticationTokens();
        /** @var $token TokenInterface */
        foreach ($activeTokens as $token) {
            if ($token->getAuthenticationProviderName() === $authenticationProviderName && $token->isAuthenticated()) {
                return $this->renderThenChild();
            }
        }

        return $this->renderElseChild();
    }
}
