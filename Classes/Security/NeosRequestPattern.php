<?php
namespace Sandstorm\UserManagement\Security;

use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\Security\RequestPatternInterface;

/**
 * A request pattern that can detect and match "frontend" and "backend" mode
 */
class NeosRequestPattern implements RequestPatternInterface
{
    const AREA_BACKEND = 'backend';
    const AREA_FRONTEND = 'frontend';

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Matches a \Neos\Flow\Mvc\RequestInterface against its set pattern rules
     *
     * @param RequestInterface $request The request that should be matched
     * @return boolean TRUE if the pattern matched, FALSE otherwise
     */
    public function matchRequest(RequestInterface $request)
    {
        $shouldMatchBackend = ($this->options['area'] === self::AREA_FRONTEND) ? false : true;

        if (!$request instanceof ActionRequest) {
            return false;
        }
        $requestPath = $request->getHttpRequest()->getUri()->getPath();
        $requestPathMatchesBackend = substr($requestPath, 0, 5) === '/neos' || substr($requestPath, 0, 6) === '/setup' || strpos($requestPath, '@') !== false;

        return $shouldMatchBackend === $requestPathMatchesBackend;
    }
}
