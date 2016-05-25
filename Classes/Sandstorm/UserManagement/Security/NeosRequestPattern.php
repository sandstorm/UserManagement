<?php
namespace Sandstorm\UserManagement\Security;

use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\RequestInterface;
use TYPO3\Flow\Security\RequestPatternInterface;

/**
 * A request pattern that can detect and match "frontend" and "backend" mode
 */
class NeosRequestPattern implements RequestPatternInterface
{

    const PATTERN_BACKEND = 'backend';
    const PATTERN_FRONTEND = 'frontend';

    /**
     * @var boolean
     */
    protected $shouldMatchBackend = TRUE;

    /**
     * Returns the set pattern
     *
     * @return string The set pattern
     */
    public function getPattern()
    {
        return $this->shouldMatchBackend ? self::PATTERN_BACKEND : self::PATTERN_FRONTEND;
    }

    /**
     * Sets the pattern (match) configuration
     *
     * @param object $pattern The pattern (match) configuration
     * @return void
     */
    public function setPattern($pattern)
    {
        $this->shouldMatchBackend = ($pattern === self::PATTERN_FRONTEND) ? FALSE : TRUE;
    }

    /**
     * Matches a \TYPO3\Flow\Mvc\RequestInterface against its set pattern rules
     *
     * @param RequestInterface $request The request that should be matched
     * @return boolean TRUE if the pattern matched, FALSE otherwise
     */
    public function matchRequest(RequestInterface $request)
    {
        if (!$request instanceof ActionRequest) {
            return FALSE;
        }
        $requestPath = $request->getHttpRequest()->getUri()->getPath();
        $requestPathMatchesBackend = substr($requestPath, 0, 5) === '/neos' || strpos($requestPath, '@') !== FALSE;
        return $this->shouldMatchBackend === $requestPathMatchesBackend;
    }

}
