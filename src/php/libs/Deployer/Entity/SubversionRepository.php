<?php namespace Primat\Deployer\Entity;

use \Primat\Deployer\Exception;
use \Primat\Deployer\Entity\Account;

/**
 * The SubversionRepository class represents what it is named after, a SVN repository
 */
class SubversionRepository
{
	/** @var \Primat\Deployer\Entity\Account $account */
	public $account = NULL; // Accounts can also be set per branch/tag/trunk, etc
	/** @var string $account */
	public $baseUrl;
	/** @var \Primat\Deployer\Entity\SubversionBranch[] $branches */
	public $branches;
	/** @var \Primat\Deployer\Entity\SubversionBranch[] $tags */
	public $tags;
	/** @var \Primat\Deployer\Entity\SubversionBranch $trunk */
	public $trunk;

	/**
	 * Constructor
	 * @param $baseUrl
	 * @param $account
	 */
	public function __construct($baseUrl, Account $account = NULL)
	{
		$this->baseUrl = trim($baseUrl, '/');
		$this->account = $account;
	}

	/**
	 * @param string $uri
	 * @param string $alias
	 * @param \Primat\Deployer\Entity\SubversionBranch $account
	 */
	public function addBranch($uri, $alias, $account = NULL)
	{
		$uri = trim($uri, '/');
		if ($account instanceof \Primat\Deployer\Entity\Account) {
			$this->account = $account;
		}
		$this->branches[] = new \Primat\Deployer\Entity\SubversionBranch("/branches/$uri", $alias);
	}

	/**
	 * @param string $uri
	 * @param string $alias
	 * @param \Primat\Deployer\Entity\SubversionBranch $account
	 */
	public function addTag($uri, $alias, $account = NULL)
	{
		$uri = trim($uri, '/');
		if ($account instanceof \Primat\Deployer\Entity\Account) {
			$this->account = $account;
		}
		$this->tags[] = new \Primat\Deployer\Entity\SubversionBranch("/tags/$uri", $alias);
	}

	/**
	 * @param string $uri
	 * @param string $alias
	 * @param \Primat\Deployer\Entity\SubversionBranch $account
	 */
	public function setTrunk($uri, $alias = 'trunk', $account = NULL)
	{
		$uri = trim($uri, '/');
		if ($account instanceof \Primat\Deployer\Entity\Account) {
			$this->account = $account;
		}
		$this->trunk = new \Primat\Deployer\Entity\SubversionBranch("/trunk/$uri", $alias);
	}

	/**
	 * @param string $alias
	 * @return SubversionBranch
	 */
	public function getTag($alias)
	{
		return isset($this->tags[$alias]) ? $this->tags[$alias] : NULL;
	}

	/**
	 * @param string $alias
	 * @return SubversionBranch
	 */
	public function getBranch($alias)
	{
		return isset($this->branches[$alias]) ? $this->branches[$alias] : NULL;
	}

	/**
	 * @return SubversionBranch
	 */
	public function getTrunk()
	{
		return $this->trunk;
	}

}
