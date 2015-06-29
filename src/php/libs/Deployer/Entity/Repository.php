<?php  namespace Primat\Deployer\Entity;
/**
 *
 */

use Primat\Deployer\Entity;

/**
 * Class Repository
 * @package Primat\Deployer\Entity
 */
class Repository extends Entity
{
	/** @var Account $account */
	public $account;
	/** @var string $baseUrl */
	public $baseUrl;

	/**
	 * Constructor
	 * @param string $baseUrl
	 * @param Account $account
	 */
	public function __construct($baseUrl, Account $account = null)
	{
		$this->baseUrl = rtrim($baseUrl, '/');
		$this->account = $account;
	}
}
