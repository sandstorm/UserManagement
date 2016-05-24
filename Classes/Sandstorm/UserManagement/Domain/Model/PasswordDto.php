<?php
/**
 * Created by IntelliJ IDEA.
 * User: sebastian
 * Date: 24.05.16
 * Time: 12:35
 */

namespace Sandstorm\UserManagement\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Cryptography\HashService;

class PasswordDto
{

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $passwordConfirmation;

    /**
     * @Flow\Inject
     * @var HashService
     */
    protected $hashService;

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $passwordConfirmation
     */
    public function setPasswordConfirmation($passwordConfirmation)
    {
        $this->passwordConfirmation = $passwordConfirmation;
    }

    public function arePasswordsEqual()
    {
        return !empty($this->password) && !empty(trim($this->password)) && ($this->password === $this->passwordConfirmation);
    }

    public function getEncryptedPasswordAndRemoveNonencryptedVersion()
    {
        if (!$this->arePasswordsEqual()) {
            throw new \TYPO3\Flow\Exception('Passwords are not equal; so it is not allowed to call getEncryptedPassword().', 1464087097);
        }

        $encrypted = $this->hashService->hashPassword($this->password);
        $this->password = NULL;
        $this->passwordConfirmation = NULL;

        return $encrypted;
    }
}