<?php
namespace Sandstorm\UserManagement\Domain\Validator;

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
     * @param RegistrationFlow $value The value that should be validated
     * @return void
     * @throws InvalidValidationOptionsException
     */
    protected function isValid($value)
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $existingAccount = $this->accountRepository->findOneByAccountIdentifier($value->getEmail());

        if ($existingAccount) {
            // todo: error message translatable
            $this->result->forProperty('email')->addError(
                new Error('Die Email-Adresse %s wird bereits verwendet!',
                    1336499566, [$value->getEmail()]));
        }

        // If a custom validation service is registered, call its validate method to allow custom validations during registration
        if ($this->objectManager->isRegistered(RegistrationFlowValidationServiceInterface::class)) {
            $instance = $this->objectManager->get(RegistrationFlowValidationServiceInterface::class);
            $instance->validateRegistrationFlow($value, $this);
        }
    }

    /**
     * The custom validation service might need to access the result directly, so it is exposed here
     *
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }
}
