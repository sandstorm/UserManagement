<?php
namespace Sandstorm\UserManagement\Controller;

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Sandstorm\UserManagement\Domain\Model\ResetPasswordFlow;
use Sandstorm\UserManagement\Domain\Repository\RegistrationFlowRepository;
use Sandstorm\UserManagement\Domain\Service\EmailService;
use Sandstorm\UserManagement\Domain\Service\UserCreationServiceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * Handle the "forgot password" flow
 */
class ResetPasswordController extends ActionController
{

    /**
     * @Flow\Inject
     * @var ResetPasswordFlowRepository
     */
    protected $resetPasswordFlowRepository;

    /**
     * @Flow\Inject
     * @var UserCreationServiceInterface
     */
    protected $userCreationService;

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


    /**
     * @Flow\SkipCsrfProtection
     */
    public function indexAction()
    {
    }

    /**
     * @param ResetPasswordFlow $resetPasswordFlow
     */
    public function resetPasswordAction(ResetPasswordFlow $resetPasswordFlow)
    {
        // We remove
        $alreadyExistingFlows = $this->resetPasswordFlowRepository->findByEmail($resetPasswordFlow->getEmail());
        if (count($alreadyExistingFlows) > 0) {
            foreach ($alreadyExistingFlows as $alreadyExistingFlow) {
                $this->resetPasswordFlowRepository->remove($alreadyExistingFlow);
            }
        }

        // Send out a confirmation mail
        $activationLink = $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor(
            'insertNewPassword',
            ['token' => $resetPasswordFlow->getActivationToken()],
            'ResetPassword');

        $this->emailService->sendTemplateBasedEmail(
            'ResetPasswordToken',
            'Passwort zurücksetzen für ' . $this->applicationName,
            [$this->senderEmailAddress => $this->applicationName],
            [$resetPasswordFlow->getEmail()],
            [
                'activationLink' => $activationLink,
                'applicationName' => $this->applicationName,
                'resetPasswordFlow' => $resetPasswordFlow
            ]
        );


        $this->resetPasswordFlowRepository->add($resetPasswordFlow);
    }

    /**
     * @param string $token
     */
    public function insertNewPasswordAction($token)
    {
        /* @var $resetPasswordFlow \Sandstorm\UserManagement\Domain\Model\ResetPasswordFlow */
        $resetPasswordFlow = $this->resetPasswordFlowRepository->findOneByActivationToken($token);
        if (!$resetPasswordFlow) {
            $this->view->assign('tokenNotFound', true);
            return;
        }

        if (!$resetPasswordFlow->hasValidActivationToken()) {
            $this->view->assign('tokenTimeout', true);
            return;
        }

        $this->view->assign('success', true);

        $this->view->assign('resetPasswordFlow', $resetPasswordFlow);
    }
}
