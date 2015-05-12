<?php namespace Primat\Deployer\Entity;

use Primat\Deployer\Entity;
/**
 *
 */
class Account extends Entity
{
	public $username;
	public $password;
	public $emailAddress;

	/**
	 * @param $username
	 * @param string $password
	 * @param string $emailAddress
	 */
	public function __construct($username, $password = '', $emailAddress = '')
	{
		parent::__construct();

		$this->username = $username;
		$this->password = $password;
		$this->emailAddress = $emailAddress;
	}
}
