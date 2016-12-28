<?php
namespace Sandstorm\UserManagement\Domain\Service;

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Sandstorm\UserManagement\Domain\Validator\RegistrationFlowValidator;
use Neos\Flow\Annotations as Flow;

/**
 * @api
 */
interface RegistrationFlowValidationServiceInterface
{

    /**
     * You can implement custom validations for your registration flows by implementing this method in your own service.
     *
     * @param RegistrationFlow $registrationFlow
     * @param RegistrationFlowValidator $validator
     * @return void
     */
    public function validateRegistrationFlow(RegistrationFlow $registrationFlow, RegistrationFlowValidator $validator);
}
