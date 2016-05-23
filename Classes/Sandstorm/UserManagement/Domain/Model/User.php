<?php
namespace Sandstorm\UserManagement\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Security\Cryptography\HashService;
use TYPO3\Flow\Utility\Algorithms;

/**
 * @Flow\Entity
 * @ORM\InheritanceType("JOINED")
 */
class User
{
    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="EmailAddress")
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(nullable=TRUE)
     */
    protected $gender;

    /**
     * @var string
     * @ORM\Column(nullable=TRUE)
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(nullable=TRUE)
     */
    protected $lastName;

    /**
     * @var string
     * @ORM\Column(nullable=TRUE)
     */
    protected $companyName;

    /**
     * @var \TYPO3\Flow\Security\Account
     * @ORM\OneToOne(cascade={"persist", "remove"})
     */
    protected $account;

    /**
     * @var string
     * @ORM\Column(nullable=TRUE)
     */
    protected $resetPasswordToken;

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
     * @var \DateTime
     * @ORM\Column(nullable=TRUE)
     */
    protected $resetPasswordTokenValidUntil;

    /**
     * @var string
     * @Flow\Transient
     * @Flow\InjectConfiguration(path="activationTokenTimeout")
     */
    protected $activationTokenTimeout;

    /**
     * @var string
     * @Flow\Transient
     * @Flow\InjectConfiguration(path="resetPasswordTokenTimeout")
     */
    protected $resetPasswordTokenTimeout;

    /**
     * @var boolean
     * @ORM\Column(nullable=TRUE)
     */
    protected $newActivationTokenRequested;

    /**
     * @Flow\Inject
     * @Flow\Transient
     * @var HashService
     */
    protected $hashService;

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
     * Get the account name of an user.
     * @return string
     */
    public function getAccountName()
    {
        if ($this->account == NULL) {
            return $this->email;
        } else {
            return $this->account->getAccountIdentifier();
        }
    }

    /**
     * Get the full name of an user.
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName.' '.$this->lastName.($this->companyName ? ', '.$this->companyName : '');
    }

    /**
     * Link a new account to this user.
     * @param \TYPO3\Flow\Security\Account $account
     */
    public function linkAccount($account)
    {
        $this->activationToken = NULL;
        $this->activationTokenValidUntil = NULL;
        $this->newActivationTokenRequested = NULL;
        $this->account = $account;
    }

    /**
     * Check if the user is active.
     * @return bool
     */
    public function isActive()
    {
        return $this->account !== NULL;
    }

    /**
     * Generate a new activation token
     * @throws Exception If the user has an account already
     */
    public function generateActivationToken()
    {
        if ($this->account == NULL) {
            $this->activationToken = Algorithms::generateRandomString(30);
            $this->activationTokenValidUntil = (new \DateTime())->add(\DateInterval::createFromDateString($this->activationTokenTimeout));
            $this->newActivationTokenRequested = FALSE;
        } else {
            throw new Exception('Cannot generate activation token if user is already activated.', 1447321213);
        }
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
     * Generate a new password reset token
     * @throws Exception If the user doesn't have an account yet
     */
    public function generateResetPasswordToken()
    {
        if ($this->account != NULL) {
            $this->getAccount()->setExpirationDate(new \DateTime());
            $this->resetPasswordToken = Algorithms::generateRandomString(30);
            $this->resetPasswordTokenValidUntil = (new \DateTime())->add(\DateInterval::createFromDateString($this->resetPasswordTokenTimeout));
        } else {
            throw new Exception('Cannot generate reset password token if user is not activated yet.', 1447669137);
        }
    }

    /**
     * Check if the user has a valid reset password token.
     * @return bool
     */
    public function hasValidResetPasswordToken()
    {
        if ($this->resetPasswordTokenValidUntil == NULL) {
            return FALSE;
        }
        return $this->resetPasswordTokenValidUntil->getTimestamp() > time();
    }

    /**
     * Updates the password for a user and makes thr account accessible again.
     *
     * @param $password
     * @throws \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function updatePassword($password){
        $hashedPassword = $this->hashService->hashPassword($password, 'default');
        $this->setResetPasswordToken(NULL);
        $this->setResetPasswordTokenValidUntil(NULL);
        $this->getAccount()->setCredentialsSource($hashedPassword);
        $this->getAccount()->setExpirationDate(NULL);
    }


    // ---- Only getters and setters follow


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
     * @return string
     */
    public function getCompanyName() {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName) {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender) {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @return \TYPO3\Flow\Security\Account
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * @param \TYPO3\Flow\Security\Account $account
     */
    public function setAccount($account) {
        $this->account = $account;
    }

    /**
     * @return string
     */
    public function getResetPasswordToken() {
        return $this->resetPasswordToken;
    }

    /**
     * @param string $resetPasswordToken
     */
    public function setResetPasswordToken($resetPasswordToken) {
        $this->resetPasswordToken = $resetPasswordToken;
    }

    /**
     * @return string
     */
    public function getActivationToken() {
        return $this->activationToken;
    }

    /**
     * @param string $activationToken
     */
    public function setActivationToken($activationToken) {
        $this->activationToken = $activationToken;
    }

    /**
     * @return \DateTime
     */
    public function getActivationTokenValidUntil() {
        return $this->activationTokenValidUntil;
    }

    /**
     * @param \DateTime $activationTokenValidUntil
     */
    public function setActivationTokenValidUntil($activationTokenValidUntil) {
        $this->activationTokenValidUntil = $activationTokenValidUntil;
    }

    /**
     * @return boolean
     */
    public function isNewActivationTokenRequested() {
        return $this->newActivationTokenRequested;
    }

    /**
     * @param boolean $newActivationTokenRequested
     */
    public function setNewActivationTokenRequested($newActivationTokenRequested) {
        $this->newActivationTokenRequested = $newActivationTokenRequested;
    }

    /**
     * @return \DateTime
     */
    public function getResetPasswordTokenValidUntil() {
        return $this->resetPasswordTokenValidUntil;
    }

    /**
     * @param \DateTime $resetPasswordTokenValidUntil
     */
    public function setResetPasswordTokenValidUntil($resetPasswordTokenValidUntil) {
        $this->resetPasswordTokenValidUntil = $resetPasswordTokenValidUntil;
    }
}
