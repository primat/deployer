<?php  namespace Primat\Deployer\Entity; 
/**
 * Project: Deployer
 * Date: 26/05/15
 */

use Primat\Deployer\Entity;

/**
 * Class RepositoryBranch
 * @package Primat\Deployer\Entity
 */
class RepositoryBranch extends Entity
{
	/**
	 * @param $text
	 * @return string
	 */
	protected function getSlug($text)
	{
		$text = (string)$text;
		if (strlen($text) === 0) {
			return '';
		}
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text); // Replace non letter or digits by -
		$text = trim($text, '-');
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = strtolower($text);
		$text = preg_replace('~[^-\w]+~', '', $text); // Remove unwanted characters
		return $text;
	}
}
