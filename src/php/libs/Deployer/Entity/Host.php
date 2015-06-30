<?php namespace Primat\Deployer\Entity;

use Primat\Deployer\Entity;

/**
 * Class Host
 * @package Primat\Deployer\Entity
 */
class Host extends Entity
{
	/** @var \Primat\Deployer\Entity\Account $account */
	public $account;
	/** @var string $hostname */
	public $hostname;
	/** @var string $name */
	public $name;
	/** @var string $homeDirPath */
	public $homeDirPath = '';
	/** @var string $privateKeyPath */
	public $privateKeyPath = '';

	/**
	 * @param $account
	 * @param $hostname
	 * @param $name
	 */
	public function __construct($hostname, Account $account = null, $name = '')
	{
		if (empty($account)) {
			$account = null;
		}
		$this->account = $account;
		$this->hostname = $hostname;
		$this->name = $name;
	}

	/**
	 * Destroy any temporary private keys used in connecting to a host
 	 */
//	public function __destruct()
//	{
//		if (! empty($this->privateKeyPath) && strpos($this->privateKeyPath, BUILD_TMP_DIR) !== FALSE &&
//			is_file($this->privateKeyPath)) {
//			unlink($this->privateKeyPath);
//		}
//	}

	/**
	 * @param \Primat\Deployer\Entity\Account $account
	 */
	public function setAccount($account)
	{
		$this->account = $account;
	}

	/**
	 * @return \Primat\Deployer\Entity\Account
	 */
	public function getAccount()
	{
		return $this->account;
	}

	/**
	 * @param string $hostname
	 */
	public function setHostname($hostname)
	{
		$this->hostname = $hostname;
	}

	/**
	 * @return string
	 */
	public function getHostname()
	{
		return $this->hostname;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
}
