<?php
namespace Sandstorm\UserManagement\Domain\Service\Flow;

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Sandstorm\UserManagement\Domain\Repository\UserRepository;
use Sandstorm\UserManagement\Domain\Service\UserCreationServiceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Security\Policy\Role;
use Sandstorm\UserManagement\Domain\Model\User;

/**
 * @Flow\Scope("singleton")
 */
class FlowUserCreationService implements UserCreationServiceInterface
{

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @Flow\InjectConfiguration(path="rolesForNewUsers")
     */
    protected $rolesForNewUsers;

    /**
     * In this method, actually create the user / account.
     *
     * NOTE: After this method is called, the $registrationFlow is DESTROYED, so you need to store all attributes
     * in your object as you need them.
     *
     * @param RegistrationFlow $registrationFlow
     * @return void
     */
    public function createUserAndAccount(RegistrationFlow $registrationFlow)
    {
        // Create the account
        $account = new \TYPO3\Flow\Security\Account();
        $account->setAccountIdentifier($registrationFlow->getEmail());
        $account->setCredentialsSource($registrationFlow->getEncryptedPassword());
        $account->setAuthenticationProviderName('Sandstorm.UserManagement:Login');

        // Assign preconfigured roles
        foreach ($this->rolesForNewUsers as $roleString){
            $account->addRole(new Role($roleString));
        }

        // Create the user
        $user = new User();
        $user->setFirstName($registrationFlow->getFirstName());
        $user->setLastName($registrationFlow->getLastName());
        $user->setEmail($registrationFlow->getEmail());
        $user->setAccount($account);

        // Persist user
        $this->userRepository->add($user);
        $this->persistenceManager->whitelistObject($user);
        $this->persistenceManager->whitelistObject($account);
    }
}
