<?php
namespace Sandstorm\UserManagement\Domain\Service;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Account;
use TYPO3\Flow\Security\AccountFactory;
use TYPO3\Flow\Security\AccountRepository;
use Sandstorm\UserManagement\Domain\Model\User;
use Sandstorm\UserManagement\Package;
use TYPO3\Flow\Security\Policy\Role;

/**
 * @Flow\Scope("singleton")
 */
class UserManagementService
{

    /**
     * @Flow\Inject
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @Flow\Inject
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @var \TYPO3\Flow\Security\Context
     * @Flow\Inject
     */
    protected $securityContext;

    /**
     * @param User $user
     * @param $password
     * @param $roles array An array of roles.
     * @throws \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function createAccount(User $user, $password, $roles)
    {
        // The provider used here must be configured via Settings.yaml
        $account = $this->accountFactory->createAccountWithPassword($user->getAccountName(), $password, $roles, 'ApplicationAuthenticationProvider');
        $this->accountRepository->add($account);
        $user->linkAccount($account);
    }
}
