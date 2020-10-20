<?php
namespace Sandstorm\UserManagement\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Authentication\TokenAndProviderFactoryInterface;
use Neos\Flow\Security\Authentication\Token\UsernamePassword;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Party\Domain\Repository\PartyRepository;
use Sandstorm\UserManagement\Domain\Model\User;
use Sandstorm\UserManagement\Domain\Repository\UserRepository;

/**
 */
class ProfileController extends ActionController
{

    /**
     * @var Context
     * @Flow\Inject
     */
    protected $securityContext;

    /**
     * @var UserRepository
     * @Flow\Inject
     */
    protected $userRepository;

	/**
	 * @Flow\Inject
	 * @var TokenAndProviderFactoryInterface
	 */
	protected $tokenAndProviderFactoryInterface;

    /**
     * @Flow\Inject
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @Flow\Inject
     * @var HashService
     */
    protected $hashService;


    public function indexAction()
    {
        $pluginArguments = $this->request->getInternalArguments();
        $account = $this->securityContext->getAccount();
        $user = $this->userRepository->findOneByAccount($account);
        $this->view->assign('account', $account);
        $this->view->assign('user', $user);
        $this->view->assign('pluginArguments', $pluginArguments);
    }

    /**
     * @param User $user
     */
    public function editProfileAction(User $user)
    {
        $this->userRepository->update($user);
        $this->redirect('index');
    }

    /**
     * @param Account $account
     * @param array $password Expects an array in the format array('<password>', '<password confirmation>')
     * @Flow\Validate(argumentName="password", type="\Neos\Neos\Validation\Validator\PasswordValidator", options={ "allowEmpty"=1, "minimum"=1, "maximum"=255 })
     */
    public function setNewPasswordAction(Account $account, array $password = array()) {
        $user = $this->userRepository->findOneByAccount($account);
        $password = array_shift($password);
        if (strlen(trim(strval($password))) > 0) {
            $this->setPassword($account, $password);
        }
        $this->redirect('index');

    }

    /**
     * Disable the technical error flash message
     *
     * @return boolean
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

    /**
     * Sets a new password for the given account
     *
     * @param Account $account The user to set the password for
     * @param string $password A new password
     * @return void
     * @api
     */
    protected function setPassword(Account $account, $password)
    {
        $tokens = $this->tokenAndProviderFactoryInterface->getTokens();
        $indexedTokens = array();
        foreach ($tokens as $token) {
            /** @var TokenInterface $token */
            $indexedTokens[$token->getAuthenticationProviderName()] = $token;
        }

        /** @var Account $account */
        $authenticationProviderName = $account->getAuthenticationProviderName();
        if (isset($indexedTokens[$authenticationProviderName]) && $indexedTokens[$authenticationProviderName] instanceof UsernamePassword) {
            $account->setCredentialsSource($this->hashService->hashPassword($password));
            $this->accountRepository->update($account);
        }
    }
}
