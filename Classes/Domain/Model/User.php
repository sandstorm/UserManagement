<?php
namespace Sandstorm\UserManagement\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Security\Cryptography\HashService;

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
     * @var \Neos\Flow\Security\Account
     * @ORM\OneToOne(cascade={"persist", "remove"})
     */
    protected $account;

    /**
     * @Flow\Inject
     * @Flow\Transient
     * @var HashService
     */
    protected $hashService;

    /**
     * Get the account name of an user.
     *
     * @return string
     */
    public function getAccountName()
    {
        if ($this->account == null) {
            return $this->email;
        } else {
            return $this->account->getAccountIdentifier();
        }
    }

    /**
     * Get the full name of an user.
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Check if the user is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->account !== null;
    }


    // ---- Only getters and setters follow


    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
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
     * @return \Neos\Flow\Security\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param \Neos\Flow\Security\Account $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }
}
