<?php

namespace WHMCS\Module\Registrar\Zispa;

class ZispaMailer {

	/**
	 * SwiftMailer instance
	 *
	 * @var object
	 */
	private $swiftMailer;

	private $zispaEmailSubject;
	private $zispaEmailBody;
	private $submissionEmail;

//https://docs.microsoft.com/en-us/azure/store-sendgrid-php-how-to-send-email
	/**
	 * Class constructor
	 *
	 * @param string[] 	$params 	Configuration parameters
	 *
	 * @return void
	 */
	public function __construct($params)
	{
		require_once __DIR__ . '/swiftmailer/swift_required.php';

	    $testMode = $params['TestMode'];
	    $testModeSubmissionsEmail = $params['TestModeZISPASubmissionsEmail'];
	    $adminEmail = $params['AdminEmail'];
	    $zispaSubmissionsEmail = $params['ZISPASubmissionsEmail'];
	    $zispaEmailSubject = $params['ZISPAEmailSubject'];
	    $zispaEmailBody = $params['ZISPAEmailBody'];
	    $smtpHost = $params['SMTPHost'];
	    $smtpPort = $params['SMTPPort'];
	    $smtpUsername = $params['SMTPUsername'];
	    $smtpPassword = $params['SMTPPassword'];

		// Setup Swift mailer parameters
		$transport = \Swift_SmtpTransport::newInstance($smtpHost, $smtpPort);
		$transport->setUsername($smtpUsername);
		$transport->setPassword($smtpPassword);
		$this->swiftMailer = \Swift_Mailer::newInstance($transport);

		if ($testMode === "yes") {
			$this->submissionEmail = $testModeSubmissionsEmail;
		} else {
			$this->submissionEmail = $zispaSubmissionsEmail;
		}

		$this->zispaEmailSubject = $zispaEmailSubject;
		$this->zispaEmailBody = $zispaEmailBody;
		$this->adminEmail = $adminEmail;
	}

	/**
	 *
	 *
	 *
	 */
	public function submit($template, $sld, $tld)
	{
		// Create a message (subject)
		$message = new \Swift_Message($this->zispaEmailSubject);

		// attach the body of the email
		$message->setFrom($this->adminEmail);
		$message->setBody($this->zispaEmailBody, 'text/plain');
		$message->setTo([
			$this->submissionEmail,
			$this->adminEmail
		]);

		$filename = sprintf("%s.%s.txt", $sld, $tld);
		$attachment = new \Swift_Attachment($template, $filename, 'text/plain');
		$message->attach($attachment);

		// send message
		if (!$recipients = $this->swiftMailer->send($message, $failures)) {
		    throw new Exception("Failed to complete submission", 1);
		}
	}

}
