<?php namespace Primat\Deployer\Entity\Email;

use \Primat\Deployer\Entity\Host;

/**
 * An email connector class used to indicate how to send emails (e.g. smtp, sendmail or PHP's mail() function).
 * This is the SMTP connector
 * Class SmtpConnector
 * @package Primat\Deployer\Entity\Email
 */
class SmtpConnector extends Connector
{
	/** @var \Primat\Deployer\Entity\Host $host */
	public $host;
	public $port = 25;
	public $auth = false;
	public $secure = 'ssl';

	/**
	 * Constructor
	 * @param \Primat\Deployer\Entity\Host $host
	 * @param int $port
	 * @param bool $auth
	 * @param string $secure
	 */
	public function __construct(Host $host, $port = 25, $auth = false, $secure = 'ssl')
	{
		$this->host = $host;
		$this->port = $port;
		$this->auth = $auth;
		$this->secure = $secure;
	}
}
