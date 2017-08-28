<?php
namespace Sandstorm\UserManagement\Domain\Repository;

/*                                                                             *
 * This script belongs to the Neos Flow package "Sandstorm.UserManagement".    *
 *                                                                             *
 *                                                                             */

use Sandstorm\UserManagement\Domain\Model\User;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 * @method User findOneByEmail(string $email)
 */
class UserRepository extends Repository
{

    // add customized methods here
}
