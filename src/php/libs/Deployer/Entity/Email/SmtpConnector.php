<?php namespace Primat\Deployer\Entity\Email;

use \Primat\Deployer\Entity\Host;

/**
 * An email connector class used to indicate how to send emails (e.g. smtp, sendmail or PHP's mail() function).
 * This is the SMTP connector
 */
class SmtpConnector extends Connector
{
	/** @var \Primat\Deployer\Entity\Host $host */
	public $host;
	public $port = 25;
	public $auth = FALSE;

	/**
	 * Constructor
	 * @param \Primat\Deployer\Entity\Host $host
	 * @param int $port
	 */
	public function __construct(Host $host, $port = 25)
	{
		$this->host = $host;
		$this->port = $port;
	}
}
