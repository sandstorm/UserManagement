<?php
namespace Sandstorm\UserManagement\Domain\Service;

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Neos\Error\Messages\Result as ErrorResult;
use Neos\Flow\Annotations as Flow;

/**
 * @api
 */
interface RegistrationFlow6ValidationServiceInterface
{

	/**
	 * You can implement custom validations for your registration flows by implementing this method in your own service.
	 *
	 * @param RegistrationFlow $registrationFlow
	 * @param ErrorResult $validatorResult
	 * @return void
	 */
	public function validateRegistrationFlow6(RegistrationFlow $registrationFlow, ErrorResult $validatorResult);
}