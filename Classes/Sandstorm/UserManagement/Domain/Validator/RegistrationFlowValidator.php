<?php
namespace Sandstorm\UserManagement\Domain\Validator;

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\AccountRepository;
use TYPO3\Flow\Validation\Error;

/**
 * Validator for ensuring uniqueness of users, ensuring no new registration flows for existing users can be created.
 */
class RegistrationFlowValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator
{

    /**
     * @var AccountRepository
     * @Flow\Inject
     */
    protected $accountRepository;

    /**
     * @param RegistrationFlow $value The value that should be validated
     * @return void
     * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException
     */
    protected function isValid($value)
    {

        $existingAccount = $this->accountRepository->findOneByAccountIdentifier($value->getEmail());

        if ($existingAccount) {
            $this->result->forProperty('email')->addError(new Error('Die Email-Adresse %s wird bereits verwendet!', 1336499566, array($value->getEmail())));
        }
    }
}

?>
