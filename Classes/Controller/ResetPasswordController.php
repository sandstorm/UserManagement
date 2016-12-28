<?php
namespace Sandstorm\UserManagement\Controller;

use Sandstorm\UserManagement\Domain\Model\ResetPasswordFlow;
use Sandstorm\UserManagement\Domain\Repository\ResetPasswordFlowRepository;
use Sandstorm\UserManagement\Domain\Service\EmailService;
use Sandstorm\UserManagement\Domain\Service\UserCreationServiceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Security\AccountRepository;

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
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @Flow\Inject
     * @var EmailService
     */
    protected $emailService;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="email.senderAddress")
     */
    protected $emailSenderAddress;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="email.senderName")
     */
    protected $emailSenderName;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="email.subjectResetPassword")
     */
    protected $subjectResetPassword;


    /**
     * @Flow\SkipCsrfProtection
     */
    public function indexAction()
    {
    }

    /**
     * @param ResetPasswordFlow $resetPasswordFlow
     */
    public function requestTokenAction(ResetPasswordFlow $resetPasswordFlow)
    {
        $account = $this->accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($resetPasswordFlow->getEmail(),
            'Sandstorm.UserManagement:Login');

        if ($account !== null) {
            $alreadyExistingFlows = $this->resetPasswordFlowRepository->findByEmail($resetPasswordFlow->getEmail());
            if (count($alreadyExistingFlows) > 0) {
                foreach ($alreadyExistingFlows as $alreadyExistingFlow) {
                    $this->resetPasswordFlowRepository->remove($alreadyExistingFlow);
                }
            }

            // Send out a confirmation mail
            $resetPasswordLink = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)->uriFor(
                'insertNewPassword',
                ['token' => $resetPasswordFlow->getResetPasswordToken()],
                'ResetPassword');

            $this->emailService->sendTemplateBasedEmail(
                'ResetPasswordToken',
                $this->subjectResetPassword,
                [$this->emailSenderAddress => $this->emailSenderName],
                [$resetPasswordFlow->getEmail()],
                [
                    'resetPasswordLink' => $resetPasswordLink,
                    'applicationName' => $this->emailSenderName,
                    'resetPasswordFlow' => $resetPasswordFlow,
                    // BaseUri can be used to embed resources (images) into the email
                    'baseUri' => htmlspecialchars($this->controllerContext->getRequest()
                        ->getHttpRequest()
                        ->getBaseUri())
                ]
            );

            $this->resetPasswordFlowRepository->add($resetPasswordFlow);
        }


        $this->view->assign('resetPasswordFlow', $resetPasswordFlow);
        $this->view->assign('account', $account);
    }

    /**
     * @param string $token
     */
    public function insertNewPasswordAction($token)
    {
        /* @var $resetPasswordFlow ResetPasswordFlow */
        $resetPasswordFlow = $this->resetPasswordFlowRepository->findOneByResetPasswordToken($token);
        if (!$resetPasswordFlow) {
            $this->view->assign('tokenNotFound', true);

            return;
        }

        if (!$resetPasswordFlow->hasValidResetPasswordToken()) {
            $this->view->assign('tokenTimeout', true);

            return;
        }

        $this->view->assign('success', true);

        $this->view->assign('resetPasswordFlow', $resetPasswordFlow);
    }


    /**
     * @param ResetPasswordFlow $resetPasswordFlow
     */
    public function updatePasswordAction(ResetPasswordFlow $resetPasswordFlow)
    {
        $account = $this->accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($resetPasswordFlow->getEmail(),
            'Sandstorm.UserManagement:Login');

        if (!$account) {
            $this->view->assign('accountNotFound', true);

            return;
        }

        $this->view->assign('success', true);
        $account->setCredentialsSource($resetPasswordFlow->getEncryptedPassword());
        $this->accountRepository->update($account);
        $this->resetPasswordFlowRepository->remove($resetPasswordFlow);
    }

    /**
     * Disable the default error flash message
     *
     * @return boolean
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }
}
