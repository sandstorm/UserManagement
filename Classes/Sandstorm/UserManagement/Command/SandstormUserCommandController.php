<?php
namespace Sandstorm\UserManagement\Command;

use Sandstorm\UserManagement\Domain\Model\PasswordDto;
use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Sandstorm\UserManagement\Domain\Repository\RegistrationFlowRepository;
use Sandstorm\UserManagement\Domain\Repository\UserRepository;
use Sandstorm\UserManagement\Domain\Service\UserCreationServiceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Doctrine\PersistenceManager;
use TYPO3\Flow\Security\AccountFactory;
use TYPO3\Flow\Security\AccountRepository;
use Sandstorm\UserManagement\Domain\Model\User;

/**
 * @Flow\Scope("singleton")
 */
class SandstormUserCommandController extends \TYPO3\Flow\Cli\CommandController
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
     * Create User on the Command Line
     *
     * @param string $email The email address, which also serves as the username.
     * @param string $password This user's password.
     */
    public function createCommand($email, $password)
    {
        $passwordDto = new PasswordDto();
        $passwordDto->setPassword($password);
        $passwordDto->setPasswordConfirmation($password);
        $registrationFlow = new RegistrationFlow();
        $registrationFlow->setPasswordDto($passwordDto);
        $registrationFlow->setEmail($email);

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
        $this->activateRegistrationCommand($email);

        // TODO Set the user's Name and roles
        //        // Persist again so we can find the user  via repo
        //        $this->persistenceManager->persistAll();

        $this->outputLine('Added the User <b>"%s"</b> with password <b>"%s"</b>.', array($email, $password));
    }

    /**
     * @param string $email
     */
    public function activateRegistrationCommand($email)
    {
        /* @var $registrationFlow \Sandstorm\UserManagement\Domain\Model\RegistrationFlow */
        $registrationFlow = $this->registrationFlowRepository->findOneByEmail($email);

        $this->userCreationService->createUserAndAccount($registrationFlow);
        $this->registrationFlowRepository->remove($registrationFlow);
    }
}
