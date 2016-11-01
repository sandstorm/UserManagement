<?php
namespace Sandstorm\UserManagement\Domain\Repository;

/*                                                                             *
 * This script belongs to the TYPO3 Flow package "Sandstorm.UserManagement".   *
 *                                                                             *
 *                                                                             */

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\QueryResultInterface;
use TYPO3\Flow\Persistence\Repository;

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
