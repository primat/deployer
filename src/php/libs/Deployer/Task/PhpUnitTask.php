<?php namespace Primat\Deployer\Task;

use \Primat\Deployer\Task;

/**
 * A class for running phpunit tests
 * Class PhpUnitTask
 * @package Primat\Deployer\Task
 */
class PhpUnitTask
{
	/**
	 * @param string $command
	 */
	public static function run($command = 'phpunit')
	{
		self::runCmd($command);
		self::log("\n");
	}
}
