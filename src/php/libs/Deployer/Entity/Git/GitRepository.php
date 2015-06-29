<?php  namespace Primat\Deployer\Entity\Git;
/**
 *
 */

use Primat\Deployer\Entity\Account;
use Primat\Deployer\Entity\Repository;

/**
 * Class GitRepository
 * @package Primat\Deployer\Entity\Svn
 */
class GitRepository extends Repository
{
	/** @var string $repoName */
	protected $repoName;

	/**
	 * Constructor
	 * @see https://www.kernel.org/pub/software/scm/git/docs/git-clone.html for types of URLs
	 * @param Account $account
	 * @param string $baseUrl
	 * @param string $repoName
	 */
	public function __construct(Account $account, $baseUrl, $repoName)
	{
		parent::__construct($baseUrl, $account);
		$this->repoName = $repoName;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->repoName;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->baseUrl . '/' . $this->repoName . '.git';
	}
}
