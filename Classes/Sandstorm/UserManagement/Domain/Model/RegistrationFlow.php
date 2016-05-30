<?php
namespace Sandstorm\UserManagement\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Security\Cryptography\HashService;
use TYPO3\Flow\Utility\Algorithms;

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
     * @Flow\Validate(type="NotEmpty")
     */
    protected $firstName;

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     */
    protected $lastName;

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
     * @param $cause int The cause of the object initilization.
     * @see http://flowframework.readthedocs.org/en/stable/TheDefinitiveGuide/PartIII/ObjectManagement.html#lifecycle-methods
     * @throws Exception
     */
    public function initializeObject($cause)
    {
        if ($cause === \TYPO3\Flow\Object\ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED) {
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
     * @throws Exception If the user has an account already
     */
    public function generateActivationToken()
    {
        $this->activationToken = Algorithms::generateRandomString(30);
        $this->activationTokenValidUntil = (new \DateTime())->add(\DateInterval::createFromDateString($this->activationTokenTimeout));
    }

    /**
     * Check if the user has a valid activation token.
     * @return bool
     */
    public function hasValidActivationToken()
    {
        if ($this->activationTokenValidUntil == NULL) {
            return FALSE;
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
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
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
