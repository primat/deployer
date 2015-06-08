<?php namespace Primat\Deployer\Service\Logging;
/**
 * Project: Deployer
 * Date: 11/05/15
 */

use \Primat\Deployer\Exception\LoggerException;

/**
 * Class Logger
 * @package Primat\Deployer\Service\Logging
 */
class FileLogger implements ILogger
{
    /** @var resource $logFileHandle */
    protected $logFileHandle;

    /**
     * @param $filePath
     * @throws LoggerException
     */
	public function __construct($filePath)
	{
        $this->logFileHandle = fopen($filePath, 'w');
        if (empty($this->logFileHandle)) {
            throw new LoggerException('Unable to create log file ' . $filePath . ' for writing');
        }
	}

    /**
     * Destructor - Clean up
     */
    public function __destruct()
    {
        fclose($this->logFileHandle);
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        fwrite($this->logFileHandle, $message);
    }
}
