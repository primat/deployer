<?php namespace Deployer\Build\Entity;

use \Deployer\Build\Exception;

/**
 * The SubversionRepository class represents what it is named after, a SVN repository
 */
class SubversionBranch
{
	/** @var \Deployer\Build\Entity\Account $account */
	public $account = NULL;
	/** @var string $alias */
	public $alias;
	/** @var string $path */
	public $uri;

	/**
	 * Constructor
	 * @param string $uri
	 * @param string $alias
	 * @param \Deployer\Build\Entity\Account $account
	 */
	public function __construct($uri, $alias, \Deployer\Build\Entity\Account $account)
	{
		$this->uri = '/' . trim($uri, '/');
		$this->alias = $alias;
		$this->account = $account;
	}

	public function getUri()
	{
		return $this->uri;
	}
}
