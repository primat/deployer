<?php namespace Primat\Deployer;

use Primat\Deployer\Exception;
use Primat\Deployer\Model\FileEntityModel;
use Primat\Deployer\Model\FileScriptModel;
use Primat\Deployer\Model\FileScriptConfigModel;
use Primat\Deployer\Model\TaskModel;
use Primat\Deployer\Service\Logging\FileLogger;

/**
 * Class App
 */
class App
{
	/** @var int $appStartTime */
	protected $appStartTime;
	/** @var string $baseFolder */
	protected $baseFolder;
	/** @var string $cacheFolder */
	protected $cacheFolder;
	/** @var FileEntityModel $entityModel */
	protected $entityModel;
	/** @var TaskModel $taskModel */
	protected $taskModel;
	/** @var $isCli bool */
	protected $isCli = true;
	/** @var $outputScriptDuration bool */
	protected $outputScriptDuration = false;
	/** @var FileScriptConfigModel $scriptConfigModel */
	protected $scriptConfigModel;
	/** @var FileScriptModel $scriptModel */
	protected $scriptModel;
	/** @var string $tempFolder */
	protected $tempFolder;

	/**
	 * Constructor - Initialize the app
	 */
	public function __construct()
	{
		// Absolute start time
		$this->appStartTime = time();
		$this->baseFolder = $this->getBaseFolder();
		$this->cacheFolder = $this->baseFolder . '/cache';
		$this->tempFolder = $this->baseFolder . '/temp';
		$this->isCli = substr(php_sapi_name(), 0, 3) === 'cli';

		// Register some handlers
		set_exception_handler(array($this, 'exceptionHandlerCallback'));
		set_error_handler(array($this, 'errorHandlerCallback'));
		register_shutdown_function(array($this, 'shutDownCallback'));

        // Perform CLI or HTTP specific actions
        if ($this->isCli) {
            if ($_SERVER['argc'] < 2) {
				throw new Exception('Usage: php ' . $_SERVER['argv'][0] . ' <script> [param1 param2 ...]');
            }
			// Establish the controller and method to call
			$scriptConfigId = $_SERVER['argv'][1];
			$scriptArgs = array_slice($_SERVER['argv'], 2);
        }
        else {
			throw new Exception('Error: Web interface not yet implemented.');
            //$this->enableOutputFlush();
        }

		// Create the models
		$this->taskModel = new TaskModel($this->getSrcFolder(), $this->baseFolder, $this->cacheFolder,
			$this->tempFolder, $this->isCli);
		$this->entityModel = new FileEntityModel($this->baseFolder);
		$this->scriptConfigModel = new FileScriptConfigModel($this->baseFolder, $this->entityModel);
		$this->scriptModel = new FileScriptModel($this->baseFolder);

		$this->initScript($scriptConfigId, $scriptArgs);
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
//		if (!(error_reporting() & $errno)) {
//			// This error code is not included in error_reporting
//			return true;
//		}
//
//		switch ($errno) {
//			case E_USER_ERROR:
//				$str = "ERROR: [$errno] $errstr\n";
//				$str .= "  Fatal error on line $errline in file $errfile";
//				$str .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
//				$this->taskModel->output->logException($str . "\n");
//				exit(1);
//				break;
//
//			case E_USER_WARNING:
//				$this->taskModel->output->log("WARNING: [$errno] $errstr\nError on line $errline in file $errfile\n");
//				break;
//
//			case E_USER_NOTICE:
//				$this->taskModel->output->log("NOTICE: [$errno] $errstr\nError on line $errline in file $errfile\n");
//				break;
//
//			default:
//				$this->taskModel->output->log("UNKNOWN: [$errno] $errstr\nError on line $errline in file $errfile\n");
//				break;
//		}

		// Don't execute PHP internal error handler
		//return true;
		return false;
	}

	/**
	 * This method is designed to handle un caught exceptions so it should be registered with set_exception_handler()
	 * @param \Exception $e
	 */
	public function exceptionHandlerCallback(\Exception $e)
	{
		if ($e instanceof Exception) {
			$this->taskModel->output->logException($e->getMessage());
			//$this->taskModel->output->logException($e->getMessage() . "\n" . $e->getTraceAsString());
		}
		else {
			$this->taskModel->output->logException($e->getMessage());
			//$this->taskModel->output->logException($e->getMessage() . "\n" . $e->getTraceAsString());
		}
		exit;
	}

	/**
	 *
	 */
	public function shutDownCallback()
	{
		$this->taskModel->output->logElapsedTime($this->appStartTime);
	}

	/**
	 * Flushes output for the HTTP buffer
	 */
	protected function enableOutputFlush()
	{
		ini_set('output_buffering', 'off');
		ini_set('zlib.output_compression', false);
		while (@ob_end_flush());
		ini_set('implicit_flush', true);
		ob_implicit_flush(true);
	}

	/**
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function getBaseFolder()
	{
		$realPath = realpath($_SERVER['SCRIPT_NAME']);
		if ($realPath) {
			return dirname($realPath);
		}
		throw new \RuntimeException("Cannot establish the project's base folder");
	}

	/**
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function getSrcFolder()
	{
		$realPath = realpath(__DIR__ . '/../../../');
		if ($realPath) {
			return $realPath;
		}
		throw new \RuntimeException("Cannot establish the application's source folder");
	}

	/**
	 * @param $configId
	 * @param $args
	 * @throws Exception
	 */
	protected function initScript($configId, $args)
	{
		$defaultSettings = [
			'output.scriptTitle'=> true,
			'output.scriptDuration'=> true,
			'log.enable'  => true,
			'log.distinct'  => false,
		];

		// Get the script configuration
		$scriptConfig = $this->scriptConfigModel->getScriptConfig($configId);

		// The context is the run-time settings - the combined settings of the script config and the app defaults
		$settings = $scriptConfig->getSettings() + $defaultSettings;
		$this->outputScriptDuration = $settings['output.scriptDuration'];
		$settings['appStartTime'] = $this->appStartTime;
		$settings['dateTimeSlug'] = date('Y-m-d_H-i-s', $this->appStartTime);
		$settings['isCli'] = $this->isCli;
		$settings['projectFolder'] = $this->baseFolder;
		$settings['projectLogsFolder'] = $this->baseFolder . '/logs';
		$settings['projectCacheFolder'] = $this->cacheFolder;
		$settings['projectTempFolder'] = $this->baseFolder . '/temp';
		$settings['scriptName'] = $scriptConfig->getTitle();

		// Create project folders
		$this->taskModel->fileSystem->createFolder($this->baseFolder, 'project');
		$this->taskModel->fileSystem->createFolder($settings['projectLogsFolder'], 'logs');
		$this->taskModel->fileSystem->createFolder($this->cacheFolder, 'cache');
		$this->taskModel->fileSystem->createFolder($settings['projectTempFolder'], 'temporary');

		// Add a file logger
		$settings['logFile'] = '';
		if (isset($settings['log.enable']) && $settings['log.enable'] == true) {
			$settings['logFile'] = $settings['projectLogsFolder'] . '/' . $configId;
			if (isset($settings['log.distinct']) && $settings['log.distinct'] == true) {
				$settings['logFile'] .= '_' . $settings['dateTimeSlug'];
			}
			$settings['logFile'] .= '.txt';
		}

		// Add a file logger to the output, if required
		if (strlen($settings['logFile'])) {
			$this->taskModel->output->addLogger(new FileLogger($settings['logFile']));
		}

		$entities = $scriptConfig->getEntities();

		$settings['scriptStartTime'] = time();
		$execEnv = new ExecutionEnvironment($this->taskModel, $this->scriptModel, $entities, $settings);

		// Output the script title, if desired
		if ($settings['output.scriptTitle'] === true) {
			$this->taskModel->output->logScriptHeading($scriptConfig->getTitle());
		}

		// Run the script!
		$execEnv->callScript($configId, $args);
	}
}
