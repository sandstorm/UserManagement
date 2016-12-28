<?php
namespace Sandstorm\UserManagement\Domain\Validator;

use Sandstorm\UserManagement\Domain\Model\PasswordDto;
use Sandstorm\UserManagement\Domain\Repository\UserRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Error;
use Neos\Flow\Validation\Exception\InvalidValidationOptionsException;
use Neos\Flow\Validation\Validator\AbstractValidator;

/**
 * Validator for users
 */
class CustomPasswordDtoValidator extends AbstractValidator
{

    /**
     * @var UserRepository
     * @Flow\Inject
     */
    protected $userRepository;

    /**
     * @param PasswordDto $value The value that should be validated
     * @return void
     * @throws InvalidValidationOptionsException
     */
    protected function isValid($value)
    {
        if (!$value->arePasswordsEqual()) {
            $this->result->forProperty('password')->addError(new Error('Passwords do not match.', 1464086581));
        }
    }
}
