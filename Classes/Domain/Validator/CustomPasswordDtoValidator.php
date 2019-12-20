<?php
namespace Sandstorm\UserManagement\Domain\Validator;

use Neos\Flow\I18n\Translator;
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
     * @var array
     * @Flow\InjectConfiguration(path="passwordConstraints")
     */
    protected $passwordConstraints;


    /**
     * @var Translator
     * @Flow\Inject
     */
    protected $translator;

    /**
     * @param PasswordDto $value The value that should be validated
     * @return void
     * @throws InvalidValidationOptionsException
     */
    protected function isValid($value)
    {
        //TODO: result can't be resolved. Something like $result = new result(); do not throw an error but don't give back the msg.

        // Matching PW and PW confirmation
        if (!$value->arePasswordsEqual()) {
            $message = $this->translator->translateById('validations.password.matching', [], null, null, 'Main', 'Sandstorm.UserManagement');
            $this->result->forProperty('password')->addError(new Error($message, 1464086581));
        }

        // Min length
        if (!$value->isPasswordMinLength($this->passwordConstraints['minLength'])) {
            $message = $this->translator->translateById('validations.password.minlength', [$this->passwordConstraints['minLength']], null, null, 'Main', 'Sandstorm.UserManagement');
            $this->result->forProperty('password')->addError(new Error($message, 1542220177));
        }

        // Max length
        if (!$value->isPasswordMaxLength($this->passwordConstraints['maxLength'])) {
            $message = $this->translator->translateById('validations.password.maxlength', [$this->passwordConstraints['maxLength']], null, null, 'Main', 'Sandstorm.UserManagement');
            $this->result->forProperty('password')->addError(new Error($message, 1542220177));
        }

        // minNumberOfLowercaseLetters
        if (!$value->doesPasswordContainLowercaseLetters($this->passwordConstraints['minNumberOfLowercaseLetters'])) {
            $message = $this->translator->translateById('validations.password.lowercase', [$this->passwordConstraints['minNumberOfLowercaseLetters']], $this->passwordConstraints['minNumberOfLowercaseLetters'], null, 'Main', 'Sandstorm.UserManagement');
            $this->result->forProperty('password')->addError(new Error($message, 1542220177));
        }

        // minNumberOfUppercaseLetters
        if (!$value->doesPasswordContainUppercaseLetters($this->passwordConstraints['minNumberOfUppercaseLetters'])) {
            $message = $this->translator->translateById('validations.password.uppercase', [$this->passwordConstraints['minNumberOfUppercaseLetters']], $this->passwordConstraints['minNumberOfUppercaseLetters'], null, 'Main', 'Sandstorm.UserManagement');
            $this->result->forProperty('password')->addError(new Error($message, 1542220177));
        }

        // minNumberOfNumbers
        if (!$value->doesPasswordContainNumbers($this->passwordConstraints['minNumberOfNumbers'])) {
            $message = $this->translator->translateById('validations.password.numbers', [$this->passwordConstraints['minNumberOfNumbers']], $this->passwordConstraints['minNumberOfNumbers'], null, 'Main', 'Sandstorm.UserManagement');
            $this->result->forProperty('password')->addError(new Error($message, 1542220177));
        }

        // minNumberOfSpecialCharacters
        if (!$value->doesPasswordContainSpecialCharacters($this->passwordConstraints['minNumberOfSpecialCharacters'])) {
            $message = $this->translator->translateById('validations.password.special', [$this->passwordConstraints['minNumberOfSpecialCharacters']], $this->passwordConstraints['minNumberOfSpecialCharacters'], null, 'Main', 'Sandstorm.UserManagement');
            $this->result->forProperty('password')->addError(new Error($message, 1542220177));
        }
    }
}
