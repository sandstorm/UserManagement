<?php
namespace Sandstorm\UserManagement\ViewHelpers;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Authentication\TokenInterface;
use TYPO3\Flow\Security\Context;
use TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

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
    public function render($authenticationProviderName)
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
