<?php
namespace Sandstorm\UserManagement\Domain\Repository;

/*                                                                             *
 * This script belongs to the TYPO3 Flow package "Sandstorm.UserManagement".   *
 *                                                                             *
 *                                                                             */

use Sandstorm\UserManagement\Domain\Model\User;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 * @method User findOneByEmail(string $email)
 */
class UserRepository extends Repository
{

    // add customized methods here
}
