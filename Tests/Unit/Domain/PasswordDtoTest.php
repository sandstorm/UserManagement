<?php
namespace Sandstorm\UserManagement\Tests\Unit\Domain;

use Neos\Flow\Tests\UnitTestCase;
use Sandstorm\UserManagement\Domain\Model\PasswordDto;

/**
 * Testcase for PasswordDto
 *
 */
class PasswordDtoTest extends UnitTestCase
{
    public function setUp(): void
    {
    }

    public function testEqualPasswordsAreEqual()
    {
        $passwordDto = new PasswordDto();
        $passwordDto->setPassword('foobar');
        $passwordDto->setPasswordConfirmation('foobar');

        $this->assertTrue($passwordDto->arePasswordsEqual());
    }

    public function testInequalPasswordsAreNotEqual()
    {
        $passwordDto = new PasswordDto();
        $passwordDto->setPassword('FOOBAR');
        $passwordDto->setPasswordConfirmation('foobar');

        $this->assertFalse($passwordDto->arePasswordsEqual());
    }

    public function testPasswordMinLength()
    {
        $passwordDto = new PasswordDto();
        $passwordDto->setPassword('6chars');
        $passwordDto->setPasswordConfirmation('6chars');

        $this->assertTrue($passwordDto->isPasswordMinLength(6));
        $this->assertFalse($passwordDto->isPasswordMinLength(7));
    }

    public function testPasswordMaxLength()
    {
        $passwordDto = new PasswordDto();
        $passwordDto->setPassword('6chars');
        $passwordDto->setPasswordConfirmation('6chars');

        $this->assertTrue($passwordDto->isPasswordMaxLength(6));
        $this->assertFalse($passwordDto->isPasswordMaxLength(5));
    }

    public function testPasswordContainsLowercaseLetters()
    {
        $passwordDto = new PasswordDto();
        $passwordDto->setPassword('4loweRCASELETTERS');
        $passwordDto->setPasswordConfirmation('4loweRCASELETTERS');

        $this->assertTrue($passwordDto->doesPasswordContainLowercaseLetters(3));
        $this->assertTrue($passwordDto->doesPasswordContainLowercaseLetters(4));
        $this->assertFalse($passwordDto->doesPasswordContainLowercaseLetters(5));
    }

    public function testPasswordContainsUppercaseLetters()
    {
        $passwordDto = new PasswordDto();
        $passwordDto->setPassword('4UPPErcaseletters');
        $passwordDto->setPasswordConfirmation('4UPPErcaseletters');

        $this->assertTrue($passwordDto->doesPasswordContainUppercaseLetters(3));
        $this->assertTrue($passwordDto->doesPasswordContainUppercaseLetters(4));
        $this->assertFalse($passwordDto->doesPasswordContainUppercaseLetters(5));
    }

    public function testPasswordContainsNumbers()
    {
        $passwordDto = new PasswordDto();
        $passwordDto->setPassword('fournumbers1234');
        $passwordDto->setPasswordConfirmation('fournumbers1234');

        $this->assertTrue($passwordDto->doesPasswordContainNumbers(3));
        $this->assertTrue($passwordDto->doesPasswordContainNumbers(4));
        $this->assertFalse($passwordDto->doesPasswordContainNumbers(5));
    }

    public function testPasswordContainsSpecialCharacters()
    {
        $passwordDto = new PasswordDto();
        $passwordDto->setPassword('4specialCHARS!"%$');
        $passwordDto->setPasswordConfirmation('4specialCHARS!"%$');

        $this->assertTrue($passwordDto->doesPasswordContainSpecialCharacters(3));
        $this->assertTrue($passwordDto->doesPasswordContainSpecialCharacters(4));
        $this->assertFalse($passwordDto->doesPasswordContainSpecialCharacters(5));
    }
}
