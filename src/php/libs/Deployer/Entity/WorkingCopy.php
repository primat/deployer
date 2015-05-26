<?php namespace Primat\Deployer\Entity;

use Primat\Deployer\Entity;
use Primat\Deployer\Exception;

/**
 *
 */
class WorkingCopy
{
	/** @var Account $account */
	public $account;
	/** @var Dir $dir */
	public $dir;
	/** @var string $id */
	//public $id;
	/** @var string $repoBaseUri */
	public $repoBaseUri;
	/** @var string $repoBaseUrl */
	public $repoBaseUrl;
	/** @var string $repoPath */
	public $repoPath;
	/** @var string $repoUrl */
	//public $repoUrl;
	/** @var SvnInfo $info */
	public $info = NULL;
	/** @var SvnExternal[] $externals */
	public $externals = NULL;


	/**
	 * @param $workingCopyFolder
	 * @param $baseUrl
	 * @param $baseUri
	 * @param Account $account
	 */
	public function __construct($workingCopyFolder, $baseUrl, $baseUri, Account $account)
	{
		$this->repoBaseUri = rtrim($baseUri, '/');
		$this->repoBaseUrl = rtrim($baseUrl, '/');
		$this->dir = new Dir($workingCopyFolder . '/' . self::dirify($this->getRepoUrl()));
		$this->account = $account;
	}

	/**
	 * @param $text
	 * @return string
	 */
	public static function dirify($text)
	{
		if (empty($text)) {
			return (string) $text;
		}
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text); // Replace non letter or digits by -
		$text = trim($text, '-');
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = strtolower($text);
		$text = preg_replace('~[^-\w]+~', '', $text); // Remove unwanted characters
		return $text;
	}

	/**
	 * @return Dir
	 */
	public function getDir()
	{
		return $this->dir;
	}

	/**
	 * @return string
	 */
	public function getRepoBaseUri()
	{
		return $this->repoBaseUri;
	}

	/**
	 * @return string
	 */
	public function getRepoBaseUrl()
	{
		return $this->repoBaseUrl;
	}

	/**
	 * @return string
	 */
	public function getRepoUrl()
	{
		return $this->repoBaseUrl . $this->repoBaseUri;
	}
}
