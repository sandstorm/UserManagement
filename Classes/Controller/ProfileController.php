<?php
namespace Sandstorm\UserManagement\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Context;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Neos\Domain\Service\UserService;
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
     * @var PartyRepository
     * @Flow\Inject
     */
    protected $partyRepository;

    /**
     * @var UserRepository
     * @Flow\Inject
     */
    protected $userRepository;

    /**
     * @var UserService
     * @Flow\Inject
     */
    protected $userService;


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
        $user = $this->userService->getUser($account->getAccountIdentifier(), $account->getAuthenticationProviderName());
        $password = array_shift($password);
        if (strlen(trim(strval($password))) > 0) {
            $this->userService->setUserPassword($user, $password);
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

}
