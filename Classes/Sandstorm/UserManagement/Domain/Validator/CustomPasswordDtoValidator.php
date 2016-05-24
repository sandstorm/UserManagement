<?php
namespace Sandstorm\UserManagement\Domain\Validator;

use Sandstorm\UserManagement\Domain\Model\PasswordDto;
use Sandstorm\UserManagement\Domain\Repository\UserRepository;
use TYPO3\Flow\Annotations as Flow;

/**
 * Validator for users
 */
class CustomPasswordDtoValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator
{

    /**
     * @var UserRepository
     * @Flow\Inject
     */
    protected $userRepository;

    /**
     * @param PasswordDto $value The value that should be validated
     * @return void
     * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException
     */
    protected function isValid($value)
    {
        if (!$value->arePasswordsEqual()) {
            $this->result->forProperty('password')->addError(new \TYPO3\Flow\Error\Error('Passwords do not match.', 1464086581));
        }
    }
}

?>
