<?php


namespace Sandstorm\UserManagement\Domain\Service;


use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;

/**
 * @api
 */
interface FindEmailAddressForUserServiceInterface
{
    /**
     * @param Account $account
     * @return string|null
     */
    public function getEmailAddressByAccount(Account $account);
}
