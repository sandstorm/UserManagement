<?php
namespace Sandstorm\UserManagement\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Exception;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Utility\Algorithms;

/**
 * @Flow\Entity
 */
class RegistrationFlow
{
    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="EmailAddress")
     */
    protected $email;

    /**
     * @var string
     */
    protected $encryptedPassword;

    /**
     * @Flow\Transient
     * @var PasswordDto
     * @Flow\Validate(type="Sandstorm\UserManagement\Domain\Validator\CustomPasswordDtoValidator", validationGroups={"Controller"})
     */
    protected $passwordDto;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $attributes = [];

    /**
     * @var string
     * @ORM\Column(nullable=TRUE)
     */
    protected $activationToken;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=TRUE)
     */
    protected $activationTokenValidUntil;

    /**
     * @var string
     * @Flow\Transient
     * @Flow\InjectConfiguration(path="activationTokenTimeout")
     */
    protected $activationTokenTimeout;

    /**
     * @param $cause int The cause of the object initialization.
     * @see http://flowframework.readthedocs.org/en/stable/TheDefinitiveGuide/PartIII/ObjectManagement.html#lifecycle-methods
     * @throws Exception
     */
    public function initializeObject($cause)
    {
        if ($cause === ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED) {
            $this->generateActivationToken();
        }
    }

    /**
     * @param PasswordDto $passwordDto
     */
    public function setPasswordDto(PasswordDto $passwordDto)
    {
        $this->passwordDto = $passwordDto;
    }

    /**
     * Generate a new activation token
     *
     * @throws Exception If the user has an account already
     */
    public function generateActivationToken()
    {
        $this->activationToken = Algorithms::generateRandomString(30);
        $this->activationTokenValidUntil = (new \DateTime())->add(\DateInterval::createFromDateString($this->activationTokenTimeout));
    }

    /**
     * Check if the user has a valid activation token.
     *
     * @return bool
     */
    public function hasValidActivationToken()
    {
        if ($this->activationTokenValidUntil == null) {
            return false;
        }

        return $this->activationTokenValidUntil->getTimestamp() > time();
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
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function storeEncryptedPassword()
    {
        $this->encryptedPassword = $this->passwordDto->getEncryptedPasswordAndRemoveNonencryptedVersion();
    }

    /**
     * @return string
     */
    public function getEncryptedPassword()
    {
        return $this->encryptedPassword;
    }

    /**
     * @return string
     */
    public function getActivationToken()
    {
        return $this->activationToken;
    }
}
