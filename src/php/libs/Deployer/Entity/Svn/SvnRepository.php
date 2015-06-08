<?php  namespace Primat\Deployer\Entity\Svn; 
/**
 * Project: Cogeco deployer scripts
 * Date: 05/06/15
 */

use Primat\Deployer\Entity\Account;
use Primat\Deployer\Entity\Repository;

/**
 * Class SvnRepository
 * @package Primat\Deployer\Entity\Svn
 */
class SvnRepository extends Repository
{
	/** @var string $uriPrefix */
	public $uriPrefix;
	/** @var string $branchesUri */
	protected $branchesUri;
	/** @var string $tagsUri */
	protected $tagsUri;
	/** @var string $trunkUri */
	protected $trunkUri;

	/**
	 * Constructor
	 * @param Account $account
	 * @param string $baseUrl
	 * @param string $uriPrefix
	 * @param string $trunkUri
	 * @param string $branchesUri
	 * @param string $tagsUri
	 */
	public function __construct(Account $account, $baseUrl, $uriPrefix = '', $trunkUri = 'trunk',
		$branchesUri = 'branches', $tagsUri = 'tags')
	{
		parent::__construct($baseUrl, $account);
		$this->uriPrefix = '/' . trim($uriPrefix, '/');
		$this->trunkUri = $trunkUri;
		$this->branchesUri = $branchesUri;
		$this->tagsUri = $tagsUri;
	}

	/**
	 * @return string
	 */
	public function getBranchesUri()
	{
		return $this->uriPrefix . '/' . $this->branchesUri;
	}

	/**
	 * @return string
	 */
	public function getTagsUri()
	{
		return $this->uriPrefix . '/' . $this->tagsUri;
	}

	/**
	 * @return string
	 */
	public function getTrunkUri()
	{
		return $this->baseUrl . $this->uriPrefix . '/' . $this->trunkUri;
	}

	/**
	 * @return string
	 */
	public function getBranchesUrl()
	{
		return $this->baseUrl . $this->uriPrefix . '/' . $this->branchesUri;
	}

	/**
	 * @return string
	 */
	public function getTagsUrl()
	{
		return $this->baseUrl . $this->uriPrefix . '/' . $this->tagsUri;
	}

	/**
	 * @return string
	 */
	public function getTrunkUrl()
	{
		return $this->uriPrefix . '/' . $this->trunkUri;
	}
}
