<?php
namespace Sandstorm\UserManagement\Domain\Service;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class EmailService {
	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var string
	 * @Flow\InjectConfiguration(path="emailService.templatePackage")
	 */
	protected $templatePackage;


	/**
	 * @param string $templateIdentifier name of the email template to use @see renderEmailBody()
	 * @param string $subject subject of the email
	 * @param array $sender sender of the email in the format array('<emailAddress>' => '<name>')
	 * @param array $recipient recipient of the email in the format array('<emailAddress>' => '<name>')
	 * @param array $variables variables that will be available in the email template. in the format array('<key>' => '<value>', ....)
	 * @return boolean TRUE on success, otherwise FALSE
	 */
	public function sendTemplateBasedEmail($templateIdentifier, $subject, array $sender, array $recipient, array $variables = array()) {
		$plaintextBody = $this->renderEmailBody($templateIdentifier, 'txt', $variables);
		$htmlBody = $this->renderEmailBody($templateIdentifier, 'html', $variables);
		$mail = new \TYPO3\SwiftMailer\Message();
		$mail
			->setFrom($sender)
			->setTo($recipient)
			->setSubject($subject)
			->setBody($plaintextBody)
			->addPart($htmlBody, 'text/html');
		return $this->sendMail($mail);
	}

	/**
	 * @param string $templateIdentifier
	 * @param string $format
	 * @param array $variables
	 * @return string
	 */
	protected function renderEmailBody($templateIdentifier, $format, array $variables) {

		// Default package to use
		$templatePackage = $this->templatePackage ? $this->templatePackage : 'Sandstorm.UserManagement';

		$standaloneView = new \TYPO3\Fluid\View\StandaloneView();
		$request = $standaloneView->getRequest();
		$request->setControllerPackageKey($templatePackage);
		$templatePathAndFilename = sprintf('resource://' . $templatePackage . '/Private/EmailTemplates/%s.%s', $templateIdentifier, $format);
		$standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
		$standaloneView->assignMultiple($variables);
		return $standaloneView->render();
	}

	/**
	 * Sends a mail and creates a system logger entry if sending failed
	 *
	 * @param \TYPO3\SwiftMailer\Message $mail
	 * @return boolean TRUE on success, otherwise FALSE
	 */
	protected function sendMail(\TYPO3\SwiftMailer\Message $mail) {
		$numberOfRecipients = 0;
		// ignore exceptions but log them
		$exceptionMessage = '';
		try {
			$numberOfRecipients = $mail->send();
		} catch (\Exception $e) {
			$this->systemLogger->logException($e);
			$exceptionMessage = $e->getMessage();
		}
		if ($numberOfRecipients < 1) {
			$this->systemLogger->log('Could not send notification email "' . $mail->getSubject() . '"', LOG_ERR, array(
				'exception' => $exceptionMessage,
				'message' => $mail->getSubject(),
				'id' => (string) $mail->getHeaders()->get('Message-ID')
			));
			return FALSE;
		}
		return TRUE;
	}
}
