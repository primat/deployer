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
    /** var $muteOutput Bool */
    protected $muteOutput;
    /** var $fileLog ILogger */
    protected $fileLog;
    /** var $outPutLog ILogger */
    protected $outPutLog;

    public function __construct(ILogger $fileLog, ILogger $outPutLog)
    {
        $this->fileLog = $fileLog;
        $this->outPutLog = $outPutLog;
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        if ($this->muteOutput || $message == '') { // $message == '' is intentional
            return;
        }

        if (! empty($this->fileLog)) {
            $this->fileLog->log($message);
        }

        if (! empty($this->outPutLog)) {
            $this->outPutLog->log($message);
        }
    }

    /**
     * @return mixed
     */
    public function getMuteOutput()
    {
        return $this->muteOutput;
    }

    /**
     * @param mixed $muteOutput
     */
    public function setMuteOutput($muteOutput)
    {
        $this->muteOutput = $muteOutput;
    }
}