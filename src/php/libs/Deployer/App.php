<?php namespace Primat\Deployer;
/**
 * Created by PhpStorm.
 * Date: 5/13/2015
 */

use Primat\Deployer\Exception;
use Primat\Deployer\Model\FileEntityModel;
use Primat\Deployer\Model\FileProjectModel;
use Primat\Deployer\Model\FileScriptConfigModel;
use Primat\Deployer\Model\FileScriptModel;
use Primat\Deployer\Model\TaskModel;
use Primat\Deployer\Service\Logging\FileLogger;
use Primat\Deployer\Task\OutputTask;

/**
 * Class App
 * @package Primat\Deployer
 */
class App
{
	/** @var int $appStartTime */
	protected $appStartTime;
	/** @var bool $isCli */
	protected $isCli;
	/** @var OutputTask $output */
	protected $output;
	
	/**
	 * Constructor - Initialize the app
	 */
	public function __construct()
	{
		$this->appStartTime = time();
		$this->isCli = substr(php_sapi_name(), 0, 3) === 'cli';
		$this->output = new OutputTask($this->isCli);

		// Register the error and exception handlers
		set_exception_handler(array($this, 'exceptionHandlerCallback'));
		//set_error_handler(array($this, 'errorHandlerCallback'));

		// Determine the controller and method
		if ($this->isCli) {
			if ($_SERVER['argc'] < 3) {
				throw new Exception('Usage: php ' . $_SERVER['argv'][0] . ' <script> [param1 param2 ...]');
			}
			// Establish the controller and method to call
			$projectId = $_SERVER['argv'][1];
			$scriptId = $_SERVER['argv'][2];
			$scriptArgs = array_slice($_SERVER['argv'], 3);
		}
		else {
			throw new Exception('Error: Web interface not yet implemented.');
		}

		$this->loadScript($projectId, $scriptId, $scriptArgs);
	}

	/**
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @return bool
	 */
	public function errorHandlerCallback($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting
			return true;
		}

		switch ($errno) {
			case E_USER_ERROR:
				$str = "ERROR: [$errno] $errstr\n";
				$str .= "  Fatal error on line $errline in file $errfile";
				$str .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
				$this->output->logException($str . "\n");
				exit(1);
				break;

			case E_USER_WARNING:
				$this->output->log("WARNING: [$errno] $errstr\nError on line $errline in file $errfile\n");
				break;

			case E_USER_NOTICE:
				$this->output->log("NOTICE: [$errno] $errstr\nError on line $errline in file $errfile\n");
				break;

			default:
				$this->output->log("UNKNOWN: [$errno] $errstr\nError on line $errline in file $errfile\n");
				break;
		}

		// Don't execute PHP internal error handler
		return true;
	}

	/**
	 * The exception handler
	 * @param \Exception $e
	 */
	public function exceptionHandlerCallback(\Exception $e)
	{
		if ($e instanceof Exception) {
			$this->output->logException($e->getMessage());
		}
		else {
			$this->output->logException($e->getMessage());
		}

		exit(1);
	}

	/**
	 * @param bool $outputScriptDuration
	 */
	public function shutDownCallback($outputScriptDuration)
	{
		if ($outputScriptDuration == true) {
			$this->output->logElapsedTime($this->appStartTime);
		}
	}

	/**
	 * @param string $projectId
	 * @param string $scriptId
	 * @param [] $args
	 * @throws Exception
	 */
	protected function loadScript($projectId, $scriptId, $args)
	{
		// Create the models
		$projectModel = new FileProjectModel($this->getFolder(), $projectId);
		$taskModel = new TaskModel($this->output, $projectModel, $this->isCli);
		$entityModel = new FileEntityModel($projectModel->getFolder());
		$scriptConfigModel = new FileScriptConfigModel($projectModel->getFolder(), $entityModel);
		$scriptModel = new FileScriptModel($projectModel->getFolder());

		// Get the script configuration
		$scriptConfig = $scriptConfigModel->getScriptConfig($scriptId);

		// These are the application default settings
		$defaultSettings = [
			'output.scriptTitle'=> true,
			'output.scriptDuration'=> true,
			'log.enable'  => true,
			'log.distinct'  => false,
			'log.filePath'  => '',
		];

		// These are the run-time settings - the combined settings of the script config and the app defaults
		$settings = $scriptConfig->getSettings() + $defaultSettings;
		$settings['appStartTime'] = $this->appStartTime;
		$settings['dateTimeSlug'] = date('Y-m-d_H-i-s', $this->appStartTime);
		$settings['isCli'] = $this->isCli;

		// Take actions based upon the settings

		// Add a file logger to the output
		if ($settings['log.enable']) {

			// Build the log file path
			$settings['log.filePath'] = $projectModel->getLogsFolder() . '/' . $scriptId;
			if ($settings['log.distinct']) {
				$settings['log.filePath'] .= '_' . $settings['dateTimeSlug'];
			}
			$settings['log.filePath'] .= '.txt';
			$this->output->addLogger(new FileLogger($settings['log.filePath']));
		}

		// Register the shutdown function
		if ($settings['output.scriptDuration'] === true) {
			register_shutdown_function(array($this, 'shutDownCallback'), $settings['output.scriptDuration']);
		}

		// Output the script title, if desired
		if ($settings['output.scriptTitle'] === true) {
			$this->output->logScriptHeading($scriptConfig->getTitle());
		}

		$execEnv = new ExecutionEnvironment($taskModel, $scriptModel, $scriptConfig->getEntities(), $settings);

		// Run the script!
		$execEnv->callScript($scriptId, $args);
	}

	/**
	 * Get the folder path of the initial executing script
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function getFolder()
	{
		$realPath = realpath($_SERVER['SCRIPT_NAME']);
		if ($realPath) {
			return dirname($realPath);
		}
		throw new \RuntimeException("Unable to establish the application folder");
	}

	/**
	 * @param string $scriptId
	 * @param bool $useDistinctFilename
	 * @param string $distinctText
	 * @return string
	 */
	protected function getLogFileName($scriptId, $useDistinctFilename = false, $distinctText = '')
	{
		// Build the project's log file (name)
		$logFile = $scriptId;
		if ($useDistinctFilename) {
			$logFile .= '_' . $distinctText;
		}
		$logFile .= '.txt';
		return $logFile;
	}
}
