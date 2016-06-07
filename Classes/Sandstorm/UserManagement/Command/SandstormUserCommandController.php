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
     * @param string $firstName First name of the user.
     * @param string $lastName Last name of the user.
     * @param string $additionalAttributes Additional attributes to pass to the registrationFlow as semicolon-separated list. Example: ./flow sandstormuser:create ... --additionalAttributes="customerType:CUSTOMER;color:blue"
     */
    public function createCommand($email, $password, $firstName, $lastName, $additionalAttributes = '')
    {
        // Parse additionalattrs if they exist
        $attributes = [];
        if(strlen($additionalAttributes) > 0){
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
        $registrationFlow->setEmail($email);
        $registrationFlow->setFirstName($firstName);
        $registrationFlow->setLastName($lastName);
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
        $this->activateRegistrationCommand($email);

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
