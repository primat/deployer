<?php namespace Deployer\Build\Task;

use \Deployer\Build\Task;

/**
 * A class for running phpunit tests
 */
class PhpUnitTask extends Task
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
