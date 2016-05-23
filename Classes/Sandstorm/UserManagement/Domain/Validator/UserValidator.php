<?php
namespace Sandstorm\UserManagement\Domain\Validator;

use Sandstorm\UserManagement\Domain\Model\User;
use Sandstorm\UserManagement\Domain\Repository\UserRepository;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Validation\Error;

/**
 * Validator for users
 */
class UserValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator {

	/**
	 * @var UserRepository
	 * @Flow\Inject
	 */
	protected $userRepository;

	/**
	 * @param User $value The value that should be validated
	 * @return void
	 * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException
	 */
	protected function isValid($value) {

		//Check if email is unique
		$query = $this->userRepository->createQuery();
		$results = $query->matching($query->equals('email', $value->getEmail()))->execute();

		//EMail doesnt exist yet
		if ($results->count() == 0) {
			return;
		}

		//If it exists, make sure it's the same user we're currently working with
		$existingUser = $results->getFirst();
		if ($existingUser->getId() != $value->getId()) {
			$this->result->forProperty('email')->addError(new Error('Die Email-Adresse %s wird bereits verwendet!', 1336499566, array($value->getEmail())));
		}
	}
}

?>