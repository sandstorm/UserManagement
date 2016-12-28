<?php
namespace Sandstorm\UserManagement\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Exception;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Flow\Utility\Algorithms;

/**
 * @Flow\Entity
 */
class ResetPasswordFlow
{
    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="EmailAddress")
     */
    protected $email;

    /**
     * @Flow\Transient
     * @var PasswordDto
     * @Flow\Validate(type="Sandstorm\UserManagement\Domain\Validator\CustomPasswordDtoValidator", validationGroups={"Controller"})
     */
    protected $passwordDto;

    /**
     * @var string
     * @ORM\Column(nullable=TRUE)
     */
    protected $resetPasswordToken;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=TRUE)
     */
    protected $resetPasswordTokenValidUntil;

    /**
     * @var string
     * @Flow\Transient
     * @Flow\InjectConfiguration(path="resetPasswordTokenTimeout")
     */
    protected $resetPasswordTokenTimeout;

    /**
     * @Flow\Inject
     * @Flow\Transient
     * @var HashService
     */
    protected $hashService;

    /**
     * @param $cause int The cause of the object initialization.
     * @see http://flowframework.readthedocs.org/en/stable/TheDefinitiveGuide/PartIII/ObjectManagement.html#lifecycle-methods
     * @throws Exception
     */
    public function initializeObject($cause)
    {
        if ($cause === ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED) {
            $this->generateResetPasswordToken();
        }
    }

    /**
     * Generate a new password reset token
     *
     * @throws Exception If the user doesn't have an account yet
     */
    protected function generateResetPasswordToken()
    {
        $this->resetPasswordToken = Algorithms::generateRandomString(30);
        $this->resetPasswordTokenValidUntil = (new \DateTime())->add(\DateInterval::createFromDateString($this->resetPasswordTokenTimeout));
    }

    /**
     * Check if the user has a valid reset password token.
     *
     * @return bool
     */
    public function hasValidResetPasswordToken()
    {
        if ($this->resetPasswordTokenValidUntil == null) {
            return false;
        }

        return $this->resetPasswordTokenValidUntil->getTimestamp() > time();
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getResetPasswordToken()
    {
        return $this->resetPasswordToken;
    }

    /**
     * @param PasswordDto $passwordDto
     */
    public function setPasswordDto(PasswordDto $passwordDto)
    {
        $this->passwordDto = $passwordDto;
    }

    public function getEncryptedPassword()
    {
        return $this->passwordDto->getEncryptedPasswordAndRemoveNonencryptedVersion();
    }
}
