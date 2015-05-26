<?php  namespace Primat\Deployer\Entity; 
/**
 * Project: Deployer
 * Date: 26/05/15
 */

/**
 * Class RepositoryBranch
 * @package Primat\Deployer\Entity
 */
class RepositoryBranch
{
	/** @var Repository $repository  */
	public $repository;
	/** @var string $uri  */
	public $uri;

	/**
	 * @param Repository $repository
	 * @param $uri
	 */
	public function __construct($uri, Repository $repository)
	{
		$this->repository = $repository;
		$this->uri = $uri;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->repository->getBaseUrl() . $this->uri;
	}
}
