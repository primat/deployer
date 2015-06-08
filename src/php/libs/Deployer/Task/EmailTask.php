<?php namespace Primat\Deployer\Task;

use \Primat\Deployer\Entity\Email;
use \Primat\Deployer\Entity\Email\Connector;
use \Primat\Deployer\Entity\Email\SmtpConnector;
use \Primat\Deployer\Exception\TaskException;

/**
 * The email task class takes care of sending emails
 */
class EmailTask
{
	/** @var bool $debugLevel */
	protected $debugLevel = 0;
	/**  @var bool $isCli */
	protected $isCli;
	/**  @var \Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;

	/**
	 * Constructor
	 * @param OutputTask $outputTask
	 * @param bool $isCli
	 */
	public function __construct(OutputTask $outputTask, $isCli)
	{
		$this->outputTask = $outputTask;
		$this->isCli = $isCli;
	}

	/**
	 * A basic PHP mail() wrapper
	 * @param $sendToList
	 * @param $subject
	 * @param $body
	 * @param array $headers
	 * @throws TaskException
	 */
	public function send($sendToList, $subject, $body, $headers = array())
	{
		$this->outputTask->log("-- Sending email notifications...\n");
		$headers =  implode("\r\n", $headers). "\r\n";
		$result = mail($sendToList, $subject, $body, $headers);
		if (! $result) {
			throw new TaskException('Email notifications failed');
		}
		$this->outputTask->log("-- Email notifications sent successfully\n");
	}

	/**
	 * Send an email using PHPMailer
	 * @param Connector $connector
	 * @param Email $emailData
	 * @throws TaskException
	 */
	public function sendEmail(Connector $connector, Email $emailData)
	{
		$multipleRecipients = (count($emailData->to) > 1);
		$this->outputTask->log("- Sending email {$emailData->subject}\n");

		$mail = new \PHPMailer();
		if ($connector instanceof SmtpConnector) {
			//Tell PHPMailer to use SMTP
			$mail->isSMTP();
			//Enable SMTP debugging
			// 0 = off (for production use)
			// 1 = client messages
			// 2 = client and server messages
			$mail->SMTPDebug = $this->debugLevel;
			//Ask for HTML-friendly debug output
			$mail->Debugoutput = $this->isCli ? 'html' : 'echo';
			//Set the hostname of the mail server
			$mail->Host = $connector->host->getHostname();
			//Set the SMTP port number - likely to be 25, 465 or 587
			$mail->Port = $connector->port;
			//Whether to use SMTP authentication
			$mail->SMTPAuth = $connector->auth;
			// SMTP auth is currently unsupported
			if ($connector->auth) {
				$mail->Username = $connector->host->getAccount()->username;
				$mail->Password = $connector->host->getAccount()->password;
				$mail->SMTPSecure = $connector->secure;
				//$mail->Port = $connector->port;
			}
		}
		else {
			throw new TaskException("Unsupported email connector");
		}

		// Set the From address and name
		$mail->setFrom($emailData->fromAddress, $emailData->fromName);

		//Set a Reply-to address, if there is one
		if (! empty($emailData->replyAddress)) {
			$mail->addReplyTo($emailData->replyAddress, $emailData->replyName);
		}

		foreach($emailData->to as $i => $toAddress) {
			$mail->addAddress($toAddress);
		}

		// Set the subject, HTML body and text
		$mail->Subject = $emailData->subject;
		$mail->msgHTML($emailData->bodyHtml);
		$mail->AltBody = $emailData->bodyText;

		// Set attachments
		if (! empty($emailData->attachments)) {
			foreach($emailData->attachments as $i => $attachment) {
				$mail->addAttachment($attachment);
			}
		}

		$mail->CharSet = $emailData->encoding;

		// Send the message, check for errors
		if ($mail->send()) {
			$this->outputTask->log("Email notification" . ($multipleRecipients ? 's' : '') . " sent\n\n");
		}
		else {
			throw new TaskException("Mailer error: " . $mail->ErrorInfo);
		}
	}

	/**
	 * Sets the debug level when sending emails
	 * @param int $level
	 */
	public function setDebugLevel($level)
	{
		if ($level < 0) {
			$this->debugLevel = 0;
		}
		else if ($level > 2) {
			$this->debugLevel = 2;
		}
		else {
			$this->debugLevel = $level;
		}
	}
}
