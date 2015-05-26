<?php namespace Primat\Deployer\Task;

use Primat\Deployer\Task;

/**
 * The TimerTask class is a chronometer for measuring script execution time
 */
class TimerTask
{
	/** @var int $lastElapsedTime */
	protected static $lastElapsedTime = 0;

	/** @var int $startTime */
	protected static $startTime = 0;


	/**
	 * Start the timer
	 */
	public static function start()
	{
		self::$startTime = time();
	}

	/**
	 * Stop the timer
	 */
	public static function stop()
	{
		if (self::$startTime === 0) {
			return;
		}
		self::$lastElapsedTime = time() - self::$startTime;
		self::$startTime = 0;
	}

	/**
	 * Get the last elapsed time
	 * @return string
	 */
	public static function getLastElapsedTime()
	{
		return gmdate("H:i:s", self::$lastElapsedTime);
	}

	/**
	 * Get the start time
	 * @return int
	 */
	public static function getStartTime()
	{
		return self::$startTime;
	}
}
