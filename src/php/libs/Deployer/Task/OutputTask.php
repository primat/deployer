<?php namespace Primat\Deployer\Task;
/**
 * Created by PhpStorm.
 * Date: 5/13/2015
 * Time: 11:01 PM
 */

use Primat\Deployer\Service\Logging\ILogger;

/**
 * Class OutputTask
 * @package Primat\Deployer\Task
 */
class OutputTask
{
	/** @var $loggers ILogger[] */
	protected $loggers;
    /** @var $muteOutput Bool */
    protected $mute;

	/**
	 * Constructor
	 * @param ILogger $logger
	 */
	public function __construct(Ilogger $logger)
    {
		$this->loggers[] = $logger;
    }

	/**
	 * ADd a logger to log output to
	 * @param ILogger $logger
	 */
	public function addLogger(ILogger $logger)
	{
		$this->loggers[] = $logger;
	}

    /**
     * @param $message String
     */
    public function log($message)
    {
        if ($this->mute || $message == '') { // $message == '' is intentional
            return;
        }

		foreach ($this->loggers as $logger) {
			$logger->log($message);
        }
    }

	/**
	 *
	 */
	public function logElapsedTime($startTime)
	{
		$endTime = time();
		$elapsedTime = $endTime - $startTime;

		if ($elapsedTime === $endTime) {
			$elapsedTime = 0;
		}

		$this->log("\n---------------------------------------\n");
		$this->log("Script execution time: " . gmdate("H:i:s", $elapsedTime));
		$this->log("\n---------------------------------------\n\n");
	}

	/**
	 * @param $message String
	 */
	public function logException($message)
	{
		$this->log($message . "\n\nAbandon ship!\n---------------------------------------\n");
	}

	/**
	 * @param $message String
	 */
	public function logScriptHeading($message)
	{
		$this->log("\n---------------------------------------\n-------- {$message}\n\n");
	}

	//
	// Getters and setters
	//

    /**
     * @return mixed
     */
    public function getMute()
    {
        return $this->mute;
    }

	/**
	 * @param mixed $mute
	 */
	public function setMute($mute)
	{
		$this->mute = $mute;
	}
}