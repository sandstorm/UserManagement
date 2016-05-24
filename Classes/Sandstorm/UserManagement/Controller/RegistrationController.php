<?php
namespace Sandstorm\UserManagement\Controller;

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Sandstorm\UserManagement\Domain\Repository\RegistrationFlowRepository;
use Sandstorm\UserManagement\Domain\Service\EmailService;
use Sandstorm\UserManagement\Domain\Service\UserManagementService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController;

class RegistrationController extends ActionController
{

    /**
     * @Flow\Inject
     * @var RegistrationFlowRepository
     */
    protected $registrationFlowRepository;

    /**
     * @Flow\Inject
     * @var EmailService
     */
    protected $emailService;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="senderEmailAddress")
     */
    protected $senderEmailAddress;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="applicationName")
     */
    protected $applicationName;


    public function indexAction()
    {
    }

    /**
     * @param RegistrationFlow $registrationFlow
     */
    public function registerAction(RegistrationFlow $registrationFlow)
    {
        $registrationFlow->storeEncryptedPassword();

        // Send out a confirmation mail
        $activationLink = $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor(
            'createAccount',
            ['token' => $registrationFlow->getActivationToken()],
            'Account');

        $this->emailService->sendTemplateBasedEmail(
            'ActivationToken',
            'Account-Aktivierung für ' . $this->applicationName,
            [$this->senderEmailAddress => $this->applicationName],
            [$registrationFlow->getEmail()],
            [
                'activationLink' => $activationLink,
                'applicationName' => $this->applicationName,
                'registrationFlow' => $registrationFlow
            ]
        );


        $this->registrationFlowRepository->add($registrationFlow);
    }
}
