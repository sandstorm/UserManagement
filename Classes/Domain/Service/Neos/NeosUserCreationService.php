<?php
namespace Sandstorm\UserManagement\Domain\Service\Neos;

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Sandstorm\UserManagement\Domain\Service\UserCreationServiceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Policy\Role;
use Neos\Neos\Domain\Model\User;
use Neos\Party\Domain\Model\PersonName;
use Neos\Party\Domain\Repository\PartyRepository;
use Neos\Party\Domain\Service\PartyService;

/**
 * @Flow\Scope("singleton")
 */
class NeosUserCreationService implements UserCreationServiceInterface
{

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

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
     * @return User
     */
    public function createUserAndAccount(RegistrationFlow $registrationFlow)
    {
        // Create the account
        $account = new Account();
        $account->setAccountIdentifier($registrationFlow->getEmail());
        $account->setCredentialsSource($registrationFlow->getEncryptedPassword());
        $account->setAuthenticationProviderName('Sandstorm.UserManagement:Login');

        // Assign preconfigured roles
        foreach ($this->rolesForNewUsers as $roleString) {
            $account->addRole(new Role($roleString));
        }

        // Create the user
        $user = new User();
        $name = new PersonName('', $registrationFlow->getAttributes()['firstName'], '',
            $registrationFlow->getAttributes()['lastName'], '', $registrationFlow->getEmail());
        $user->setName($name);

        // Assign them to each other and persist
        $this->getPartyService()->assignAccountToParty($account, $user);
        $this->getPartyRepository()->add($user);
        $this->accountRepository->add($account);
        $this->persistenceManager->whitelistObject($user);
        $this->persistenceManager->whitelistObject($user->getPreferences());
        $this->persistenceManager->whitelistObject($name);
        $this->persistenceManager->whitelistObject($account);

        // Return the user so the controller can directly use it
        return $user;
    }

    /**
     * This method exists to ensure the code runs outside Neos.
     * We do not fetch this via injection so it works also in Flow when the class is not present
     *
     * @return PartyService
     */
    protected function getPartyService()
    {
        return $this->objectManager->get(PartyService::class);
    }

    /**
     * This method exists to ensure the code runs outside Neos.
     * We do not fetch this via injection so it works also in Flow when the class is not present
     *
     * @return PartyRepository
     */
    protected function getPartyRepository()
    {
        return $this->objectManager->get(PartyRepository::class);
    }
}
