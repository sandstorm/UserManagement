<?php
namespace Sandstorm\UserManagement\Domain\Repository;

/*                                                                             *
 * This script belongs to the Neos Flow package "Sandstorm.UserManagement".    *
 *                                                                             *
 *                                                                             */

use Neos\Flow\Security\Account;
use Sandstorm\UserManagement\Domain\Model\User;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 * @method User findOneByEmail(string $email)
 * @method User findOneByAccount(Account $account)
 */
class UserRepository extends Repository
{

    // add customized methods here
}
