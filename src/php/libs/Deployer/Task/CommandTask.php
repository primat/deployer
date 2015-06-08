<?php namespace Primat\Deployer\Task;
/**
 * Created by PhpStorm.
 * Date: 5/13/2015
 * Time: 10:16 PM
 */

use Primat\Deployer\Exception\CommandException;
use Primat\Deployer\Exception\ExitStatusException;

/**
 * Class CommandTask
 * @package Primat\Deployer\Task
 */
class CommandTask
{
	/**  @var $viewTask \Primat\Deployer\Task\OutputTask */
	protected $outputTask;

    protected $cmdIoBuffer = 4096;

	/**
	 * Constructor
	 * @param OutputTask $outputTask
	 */
	public function __construct(OutputTask $outputTask)
	{
		$this->outputTask = $outputTask;
	}

    /**
     * @param $cmd
     * @param bool $echoOutput
     * @return string
     * @throws CommandException
     * @throws ExitStatusException
     */
    public function runCmd($cmd, $echoOutput = TRUE)
    {
        $descriptorSpec = array(
            0 => array("pipe", "r"), // stdin is a pipe that the child will read from
            1 => array("pipe", "w"), // stdout is a pipe that the child will write to
            // 2 => array("pipe", "w") do nothing with stderr because stream_set_blocking() doesn't work on Windows :(
        );
        $pipes = array();
        $result = '';

        $process = proc_open($cmd, $descriptorSpec, $pipes);

        if (is_resource($process)) {

            stream_set_blocking($pipes[1] , 0);
            //stream_set_blocking($pipes[2] , 0);

            while (! feof($pipes [1])) {
                $read = fread($pipes[1], $this->cmdIoBuffer);
                $result .= $read;
                if ($echoOutput) {
					$this->outputTask->log($read);
                }
            }

            fclose($pipes[1]);
            //fclose($pipes[2]);

            $exitStatus = proc_close($process);
            if ($exitStatus > 0) {
                throw new ExitStatusException("Command failed.\n\tCommand: $cmd\n\tExit status: $exitStatus\n\tMessage: $result");
            }
        }
        else {
            throw new CommandException('Command failed: invalid process resource');
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getCmdIoBuffer()
    {
        return $this->cmdIoBuffer;
    }

    /**
     * @param int $cmdIoBuffer
     */
    public function setCmdIoBuffer($cmdIoBuffer)
    {
        $this->cmdIoBuffer = $cmdIoBuffer;
    }
}