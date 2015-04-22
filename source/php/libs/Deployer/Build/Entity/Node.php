<?php namespace Deployer\Build\Entity;
/**
 *
 */
class Node
{
	public $username;
	public $password;
	public $hostname;

	/**
	 * @param $account
	 * @param $hostname
	 */
	public function __construct(Account $account, $hostname)
	{
		$this->username = $account->username;
		$this->password = $account->password;
		$this->hostname = $hostname;
	}
}
