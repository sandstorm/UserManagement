<?php
namespace Sandstorm\UserManagement\Domain\Validator;

use Neos\Flow\I18n\Translator;
use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Sandstorm\UserManagement\Domain\Service\RegistrationFlowValidationServiceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Result;
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Validation\Error;
use Neos\Flow\Validation\Exception\InvalidValidationOptionsException;
use Neos\Flow\Validation\Validator\AbstractValidator;

/**
 * Validator for ensuring uniqueness of users, ensuring no new registration flows for existing users can be created.
 */
class RegistrationFlowValidator extends AbstractValidator
{

    /**
     * @var AccountRepository
     * @Flow\Inject
     */
    protected $accountRepository;

    /**
     * @var ObjectManager
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @var Translator
     * @Flow\Inject
     */
    protected $translator;

    /**
     * @param RegistrationFlow $value The value that should be validated
     * @return void
     * @throws InvalidValidationOptionsException
     */
    protected function isValid($value)
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $existingAccount = $this->accountRepository->findOneByAccountIdentifier($value->getEmail());

        if ($existingAccount) {
            $message = $this->translator->translateById('validations.registrationflow.email', [$value->getEmail()], null, null, 'Main', 'Sandstorm.UserManagement');
            $this->getResult()->forProperty('email')->addError(new Error($message, 1336499566));
        }

        // If a custom validation service is registered, call its validate method to allow custom validations during registration
        if ($this->objectManager->isRegistered(RegistrationFlowValidationServiceInterface::class)) {
            $instance = $this->objectManager->get(RegistrationFlowValidationServiceInterface::class);
            $instance->validateRegistrationFlow($value, $this);
        }
		
		// for >= flow 6
		if ($this->objectManager->isRegistered(RegistrationFlow6ValidationServiceInterface::class)) {
		    $instance = $this->objectManager->get(RegistrationFlow6ValidationServiceInterface::class);
		    $instance->validateRegistrationFlow6($value, $this->getResult());
	    }
    }

}
