<?php  namespace Primat\Deployer\Entity\Svn; 
/**
 * Project: Deployer
 * Date: 05/06/15
 */

use Primat\Deployer\Entity;
use Primat\Deployer\Entity\RepositoryBranch;
use Primat\Deployer\Entity\Svn\SvnRepository;

/**
 * Class SvnBranch
 * @package Primat\Deployer\Entity\Svn
 */
class SvnBranch extends RepositoryBranch
{
	/** @var SvnRepository $repository  */
	public $repo;
	/** @var string $uri  */
	public $uri;

	/**
	 * Constructor
	 * @param SvnRepository $repo
	 * @param $uri
	 */
	public function __construct(SvnRepository $repo, $uri)
	{
		$this->repo = $repo;
		$this->uri = '/' . trim($uri, '\\/');
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->repo->baseUrl . $this->uri;
	}

	/**
	 * @return mixed|string
	 */
	public function getId()
	{
		return parent::getSlug($this->uri);
	}
}
