<?php namespace Deployer\Utils;

use \Deployer\Utils\Cygwin;
use \Deployer\Config;

/**
 * Class Expect
 * @package Deployer\Utils
 */
class Expect
{
	/**
	 * Gets the Expect command template used in commands which would normally require a person to interactively enter
	 * their (e.g. SSH) password
	 * @return string
	 */
	public static function getCommandTemplate()
	{
		return Config::get('expect.bin') . ' ' . Cygwin::cygPath(BUILD_ROOT_DIR) . '/source/expect/pass.exp "%s" "%s"';
	}
}
