<?php  namespace Primat\Deployer\Entity; 
/**
 * Project: Deployer
 * Date: 26/05/15
 */

/**
 * Class Repository
 * @package Primat\Deployer\Entity
 */
class Repository
{
	const TYPE_SVN = 'svn';
	const TYPE_GIT = 'git';

	/** @var Account $account */
	public $account;
	/** @var string $baseUrl */
	public $baseUrl;

	/**
	 * @param $baseUrl
	 * @param Account $account
	 */
	public function __construct($baseUrl, Account $account)
	{
		$this->baseUrl = rtrim($baseUrl, '/');
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
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}
}
