<?php namespace Primat\Deployer\Utils;
/**
 * Created by JetBrains PhpStorm.
 * Date: 5/29/14
 */

/**
 * Class StringUtils
 * @package Primat\Deployer\Utils
 */
class StringUtils
{
	/**
	 * @param $start
	 * @param $end
	 * @param $new
	 * @param $source
	 * @return mixed
	 */
	public function replaceWithinDelimiters($start, $end, $new, $source) {
		return preg_replace('#('.preg_quote($start).')(.*)('.preg_quote($end).')#si', '$1'.$new.'$3', $source);
	}
}