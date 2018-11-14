<?php
namespace Sandstorm\UserManagement\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Security\Cryptography\HashService;

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

    /**
     * @return bool
     */
    public function arePasswordsEqual()
    {
        return !empty($this->password) && !empty(trim($this->password)) &&
            ($this->password === $this->passwordConfirmation);
    }

    /**
     * @param $length
     * @return bool
     */
    public function isPasswordMinLength($length)
    {
        return strlen($this->password) >= $length;
    }

    /**
     * @param $length
     * @return bool
     */
    public function isPasswordMaxLength($length)
    {
        return strlen($this->password) <= $length;
    }

    /**
     * @param int $minAmount
     * @return bool
     */
    public function doesPasswordContainLowercaseLetters($minAmount)
    {
        return $this->doesPasswordContain('/[a-z]{1}/', $minAmount);
    }

    /**
     * @param int $minAmount
     * @return bool
     */
    public function doesPasswordContainUppercaseLetters($minAmount)
    {
        return $this->doesPasswordContain('/[A-Z]{1}/', $minAmount);
    }

    /**
     * @param int $minAmount
     * @return bool
     */
    public function doesPasswordContainNumbers($minAmount)
    {
        return $this->doesPasswordContain('/[\d]{1}/', $minAmount);
    }

    /**
     * @param int $minAmount
     * @return bool
     */
    public function doesPasswordContainSpecialCharacters($minAmount)
    {
        // Regexes do not work reliably here - we simply kick out all letters and numbers
        $replacedPassword = preg_replace('/[a-zA-Z\d]/', '', $this->password);
        return strlen($replacedPassword) >= $minAmount;
    }

    /**
     * Helper method
     *
     * @param string $regex
     * @return bool
     */
    private function doesPasswordContain($regex, $minAmount)
    {
        $matches = [];
        preg_match_all($regex, $this->password, $matches);
        return count($matches[0]) >= $minAmount;
    }

    public function getEncryptedPasswordAndRemoveNonencryptedVersion()
    {
        if (!$this->arePasswordsEqual()) {
            throw new Exception('Passwords are not equal; so it is not allowed to call getEncryptedPassword().',
                1464087097);
        }

        $encrypted = $this->hashService->hashPassword($this->password);
        $this->password = null;
        $this->passwordConfirmation = null;

        return $encrypted;
    }
}
