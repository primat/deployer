<?php namespace Deployer\Build\Entity;

use \Deployer\Build\Exception;
use \Deployer\Build\Entity\Account;

/**
 * The SubversionRepository class represents what it is named after, a SVN repository
 */
class SubversionRepository
{
	/** @var \Deployer\Build\Entity\Account $account */
	public $account = NULL; // Accounts can also be set per branch/tag/trunk, etc
	/** @var string $account */
	public $baseUrl;
	/** @var \Deployer\Build\Entity\SubversionBranch[] $branches */
	public $branches;
	/** @var \Deployer\Build\Entity\SubversionBranch[] $tags */
	public $tags;
	/** @var \Deployer\Build\Entity\SubversionBranch $trunk */
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
	 * @param \Deployer\Build\Entity\SubversionBranch $account
	 */
	public function addBranch($uri, $alias, $account = NULL)
	{
		$uri = trim($uri, '/');
		if ($account instanceof \Deployer\Build\Entity\Account) {
			$this->account = $account;
		}
		$this->branches[] = new \Deployer\Build\Entity\SubversionBranch("/branches/$uri", $alias);
	}

	/**
	 * @param string $uri
	 * @param string $alias
	 * @param \Deployer\Build\Entity\SubversionBranch $account
	 */
	public function addTag($uri, $alias, $account = NULL)
	{
		$uri = trim($uri, '/');
		if ($account instanceof \Deployer\Build\Entity\Account) {
			$this->account = $account;
		}
		$this->tags[] = new \Deployer\Build\Entity\SubversionBranch("/tags/$uri", $alias);
	}

	/**
	 * @param string $uri
	 * @param string $alias
	 * @param \Deployer\Build\Entity\SubversionBranch $account
	 */
	public function setTrunk($uri, $alias = 'trunk', $account = NULL)
	{
		$uri = trim($uri, '/');
		if ($account instanceof \Deployer\Build\Entity\Account) {
			$this->account = $account;
		}
		$this->trunk = new \Deployer\Build\Entity\SubversionBranch("/trunk/$uri", $alias);
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
