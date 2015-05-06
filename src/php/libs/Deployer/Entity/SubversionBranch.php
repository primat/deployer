<?php namespace Deployer\Entity;

use \Deployer\Exception;

/**
 * The SubversionRepository class represents what it is named after, a SVN repository
 */
class SubversionBranch
{
	/** @var \Deployer\Entity\Account $account */
	public $account = NULL;
	/** @var string $alias */
	public $alias;
	/** @var string $path */
	public $uri;

	/**
	 * Constructor
	 * @param string $uri
	 * @param string $alias
	 * @param \Deployer\Entity\Account $account
	 */
	public function __construct($uri, $alias, \Deployer\Entity\Account $account)
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
