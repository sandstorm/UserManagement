<?php
namespace Sandstorm\UserManagement\Domain\Service;

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Neos\Flow\Annotations as Flow;

/**
 * @api
 */
interface UserCreationServiceInterface
{

    /**
     * In this method, actually create the user / account.
     *
     * NOTE: After this method is called, the $registrationFlow is DESTROYED, so you need to store all attributes
     * in your object as you need them.
     *
     * @param RegistrationFlow $registrationFlow
     * @return void
     */
    public function createUserAndAccount(RegistrationFlow $registrationFlow);
}
