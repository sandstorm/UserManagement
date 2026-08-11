<?php
namespace Sandstorm\UserManagement\Tests\Unit\Domain\Service;

use Neos\Flow\Security\Account;
use Neos\Flow\Tests\UnitTestCase;
use Sandstorm\UserManagement\Domain\Service\FindEmailAddressForUserByAccountIdentifierService;

/**
 * Testcase for the FindEmailAddressForUserByAccountIdentifierService
 *
 */
class FindEmailAddressForUserByAccountIdentifierServiceTest extends UnitTestCase
{
    public function testReturnsTheAccountIdentifierWhenItIsAnEmailAddress()
    {
        $account = new Account();
        $account->setAccountIdentifier('user@example.com');

        $service = new FindEmailAddressForUserByAccountIdentifierService();

        $this->assertSame('user@example.com', $service->getEmailAddressByAccount($account));
    }

    public function testReturnsTheAccountIdentifierUnchangedWhenItIsAPlainUsername()
    {
        $account = new Account();
        $account->setAccountIdentifier('someuser');

        $service = new FindEmailAddressForUserByAccountIdentifierService();

        $this->assertSame('someuser', $service->getEmailAddressByAccount($account));
    }
}
