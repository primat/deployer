<?php namespace Primat\Deployer\Utils;

/**
 * A class for Cygwin related functionality
 * Class Cygwin
 * @package Primat\Deployer\Utils
 */
class Cygwin
{
	/**
	 * Convert a Windows absolute path to a Cygwin path
	 * @param string $path
	 * @return string
	 */
	public function getCygPath($path)
	{
		// Only affect Windows absolute paths
		if (preg_match('/^([A-Za-z]):/', $path, $matches) === 1) {
			$driveLetter = strtolower($matches[1]);
			$path = mb_substr(str_replace('\\', '/', $path), 2);
			$path = '/cygdrive/' . $driveLetter . $path;
		}
		return $path;
	}
}
