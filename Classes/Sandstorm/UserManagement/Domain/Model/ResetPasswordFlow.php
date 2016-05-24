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
class ResetPasswordFlow
{
    /**
     * @var string
     */
    protected $accountIdentifier;

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
    public function updatePassword($password)
    {
        $hashedPassword = $this->hashService->hashPassword($password, 'default');
        $this->setResetPasswordToken(NULL);
        $this->setResetPasswordTokenValidUntil(NULL);
        $this->getAccount()->setCredentialsSource($hashedPassword);
        $this->getAccount()->setExpirationDate(NULL);
    }
}
