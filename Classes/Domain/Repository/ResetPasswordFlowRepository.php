<?php
namespace Sandstorm\UserManagement\Domain\Repository;

/*                                                                             *
 * This script belongs to the Flow package "Sandstorm.UserManagement".         *
 *                                                                             *
 *                                                                             */

use Sandstorm\UserManagement\Domain\Model\ResetPasswordFlow;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 * @method QueryResultInterface findByEmail(string $email)
 * @method ResetPasswordFlow findOneByResetPasswordToken(string $token)
 */
class ResetPasswordFlowRepository extends Repository
{

    // add customized methods here
}
