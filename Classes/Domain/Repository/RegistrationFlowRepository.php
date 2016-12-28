<?php
namespace Sandstorm\UserManagement\Domain\Repository;

/*                                                                             *
 * This script belongs to the Flow package "Sandstorm.UserManagement".         *
 *                                                                             *
 *                                                                             */

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 * @method QueryResultInterface findByEmail(string $email)
 * @method RegistrationFlow findOneByEmail(string $email)
 * @method RegistrationFlow findOneByActivationToken(string $token)
 */
class RegistrationFlowRepository extends Repository
{

    // add customized methods here
}
