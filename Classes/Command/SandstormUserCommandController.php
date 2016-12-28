<?php
namespace Sandstorm\UserManagement\Command;

use Sandstorm\UserManagement\Domain\Model\PasswordDto;
use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Sandstorm\UserManagement\Domain\Model\User;
use Sandstorm\UserManagement\Domain\Repository\RegistrationFlowRepository;
use Sandstorm\UserManagement\Domain\Repository\UserRepository;
use Sandstorm\UserManagement\Domain\Service\Neos\NeosUserCreationService;
use Sandstorm\UserManagement\Domain\Service\UserCreationServiceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Cli\Request;
use Neos\Flow\Cli\Response;
use Neos\Flow\Mvc\Dispatcher;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\AccountFactory;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Neos\Command\UserCommandController;

/**
 * @Flow\Scope("singleton")
 */
class SandstormUserCommandController extends CommandController
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
     * @Flow\Inject
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @Flow\Inject
     * @var RegistrationFlowRepository
     */
    protected $registrationFlowRepository;

    /**
     * @Flow\Inject
     * @var UserCreationServiceInterface
     */
    protected $userCreationService;

    /**
     * @Flow\Inject
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var HashService
     */
    protected $hashService;

    /**
     * Create User on the Command Line
     *
     * @param string $username The email address, which also serves as the username.
     * @param string $password This user's password.
     * @param string $additionalAttributes Additional attributes to pass to the registrationFlow as semicolon-separated list. Example: ./flow sandstormuser:create ... --additionalAttributes="customerType:CUSTOMER;color:blue"
     */
    public function createCommand($username, $password, $additionalAttributes = '')
    {
        // Parse additionalAttributes if they exist
        $attributes = [];
        if (strlen($additionalAttributes) > 0) {
            $attributesSplitBySeparator = explode(';', $additionalAttributes);
            array_map(function ($singleAttribute) use (&$attributes) {
                $splitAttribute = explode(':', $singleAttribute);
                $attributes[$splitAttribute[0]] = $splitAttribute[1];
            }, $attributesSplitBySeparator);
        }

        $passwordDto = new PasswordDto();
        $passwordDto->setPassword($password);
        $passwordDto->setPasswordConfirmation($password);
        $registrationFlow = new RegistrationFlow();
        $registrationFlow->setPasswordDto($passwordDto);
        $registrationFlow->setEmail($username);
        $registrationFlow->setAttributes($attributes);

        // Remove existing registration flows
        $alreadyExistingFlows = $this->registrationFlowRepository->findByEmail($registrationFlow->getEmail());
        if (count($alreadyExistingFlows) > 0) {
            foreach ($alreadyExistingFlows as $alreadyExistingFlow) {
                $this->registrationFlowRepository->remove($alreadyExistingFlow);
            }
        }
        $registrationFlow->storeEncryptedPassword();

        // Store the RF and persist so the activate command will find it
        $this->registrationFlowRepository->add($registrationFlow);
        $this->persistenceManager->persistAll();

        // Directly activate the account
        $this->activateRegistrationCommand($username);

        $this->outputLine('Added the User <b>"%s"</b> with password <b>"%s"</b>.', [$username, $password]);
    }

    /**
     * @param string $username The username identifying a pending registration flow.
     */
    public function activateRegistrationCommand($username)
    {
        /* @var $registrationFlow \Sandstorm\UserManagement\Domain\Model\RegistrationFlow */
        $registrationFlow = $this->registrationFlowRepository->findOneByEmail($username);

        if ($registrationFlow === null) {
            $this->outputLine('The user <b>' . $username . '</b> doesn\'t have a non-activated account.');
            $this->quit(1);
        }

        $this->userCreationService->createUserAndAccount($registrationFlow);
        $this->registrationFlowRepository->remove($registrationFlow);
    }


    /**
     * Set a new password for the given user
     *
     * @param string $username user to modify
     * @param string $password new password
     * @param string $authenticationProvider Name of the authentication provider to use for finding the user. Default: "Sandstorm.UserManagement:Login".
     * @return void
     */
    public function setPasswordCommand($username, $password, $authenticationProvider = 'Sandstorm.UserManagement:Login')
    {
        // If we're in Neos context, we simply forward the command to the Neos command controller.
        if ($this->shouldUseNeosService()) {
            $cliRequest = new Request($this->request);
            $cliRequest->setControllerObjectName(UserCommandController::class);
            $cliRequest->setControllerCommandName('setPassword');
            $cliRequest->setArguments([
                'username' => $username,
                'password' => $password,
                'authenticationProvider' => $authenticationProvider
            ]);
            $cliResponse = new Response($this->response);
            $this->dispatcher->dispatch($cliRequest, $cliResponse);
            $this->quit(0);
        }

        // Otherwise, we use our own logic.
        $account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username,
            $authenticationProvider);

        if ($account === null) {
            $this->outputLine('The user <b>' . $username . '</b> could not be found with auth provider <b>' .
                $authenticationProvider . '</b>.');
            $this->quit(1);
        }

        $encrypted = $this->hashService->hashPassword($password);
        $account->setCredentialsSource($encrypted);
        $this->accountRepository->update($account);
        $this->outputLine('Password for user <b>' . $username . '</b> changed.');
    }

    /**
     * Removes a user and his account.
     *
     * @param string $username user to remove
     * @return void
     */
    public function removeCommand($username)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneByEmail($username);
        if ($user === null) {
            $this->outputLine('The user <b>' . $username . '</b> could not be found.');
            $this->quit(1);
        }

        $this->userRepository->remove($user);
        $this->accountRepository->remove($user->getAccount());
        $this->outputLine('Removed the user <b>' . $username . '</b>.');
    }

    /**
     * Lists all available accounts.
     */
    public function listAccountsCommand()
    {
        /** @var Account[] $accounts */
        $accounts = $this->accountRepository->findAll()->toArray();
        usort($accounts, function ($a, $b) {
            /** @var Account $a */
            /** @var Account $b */
            return ($a->getAccountIdentifier() > $b->getAccountIdentifier());
        });

        $tableRows = [];
        $headerRow = ['Identifier', 'Authentication Provider', 'Role(s)'];

        foreach ($accounts as $account) {
            $tableRows[] = [
                $account->getAccountIdentifier(),
                $account->getAuthenticationProviderName(),
                implode(' ,', $account->getRoles())
            ];
        }

        $this->output->outputTable($tableRows, $headerRow);
        $this->outputLine(sprintf('  <b>%s accounts total.</b>', count($accounts)));
    }

    /**
     * Lists all available users.
     */
    public function listUsersCommand()
    {
        // If we're in Neos context, we pass on the command.
        if ($this->shouldUseNeosService()) {
            $cliRequest = new Request($this->request);
            $cliRequest->setControllerObjectName(UserCommandController::class);
            $cliRequest->setControllerCommandName('list');
            $cliResponse = new Response($this->response);
            $this->dispatcher->dispatch($cliRequest, $cliResponse);

            return;
        }
        /** @var User[] $users */
        $users = $this->userRepository->findAll()->toArray();
        usort($users, function ($a, $b) {
            /** @var User $a */
            /** @var User $b */
            return ($a->getEmail() > $b->getEmail());
        });

        $tableRows = [];
        $headerRow = ['Email', 'Name', 'Role(s)'];

        foreach ($users as $user) {
            $tableRows[] = [$user->getEmail(), $user->getFullName(), implode(' ,', $user->getAccount()->getRoles())];
        }

        $this->output->outputTable($tableRows, $headerRow);
        $this->outputLine(sprintf('  <b>%s users total.</b>', count($users)));
    }

    /**
     * We check if we're in the Neos context by checking if we're using the Neos user creation service.
     *
     * @return boolean
     */
    protected function shouldUseNeosService()
    {
        // The userCreationService is a DependencyProxy instance here, we can get the class name from it
        return get_class($this->userCreationService) === NeosUserCreationService::class;
    }
}
