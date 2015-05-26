<?php namespace Primat\Deployer;

use Primat\Deployer\Exception;
use Primat\Deployer\Service\Logging\FileLogger;
use Primat\Deployer\Service\Logging\ConsoleLogger;
use Primat\Deployer\Service\Logging\HtmlLogger;
use Primat\Deployer\Task\CliTask;
use Primat\Deployer\Task\CommandTask;
use Primat\Deployer\Task\OutputTask;
use Primat\Deployer\Task\SvnTask;
use Primat\Deployer\Task\ViewTask;
use Primat\Deployer\Task\EmailTask;

/**
 * Class App
 */
class App
{
	/** @var int $appStartTime */
	protected $appStartTime;
	/** @var $currentProject \Primat\Deployer\DeployerProject */
	protected $currentProject;
	/** @var $currentScript \Primat\Deployer\DeployerScript */
	protected $currentScript;
	/** @var $isCli bool */
	protected $isCli = true;
	/** @var $settings string[] */
	protected $settings = [
		'output.scriptTitle'=> true,
		'output.scriptDuration'=> true,
		'log.enable'  => true,
		'log.distinct'  => false,
		'cmd.bash' => 'bash',
		'cmd.mintty' => 'mintty',
		'cmd.expect' => 'expect',
		'cmd.rsync' => 'rsync',
		'cmd.svn' => 'svn',
		'cmd.mysql' => 'mysql',
		'cmd.mysqldump' => 'mysqldump'
	];
	/** @var\Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;

	/**
	 * Constructor - Initialize the app
	 */
	public function __construct()
	{
		$this->appStartTime = time();
		$this->isCli = substr(php_sapi_name(), 0, 3) === 'cli';

		// Initialize output control
		if ($this->isCli) {
			$this->outputTask = new OutputTask(new ConsoleLogger());
		}
		else {
			$this->outputTask = new OutputTask(new HtmlLogger());
		}

		// Register some handlers
		set_exception_handler(array($this, 'exceptionHandlerCallback'));
		set_error_handler(array($this, 'errorHandlerCallback'));
		register_shutdown_function(array($this, 'shutDownCallback'));

		$projectName = '';
		$projectNameCustom = '';
		$scriptName = '';
		$scriptClassName = '';
		$args = [];

        // Perform CLI or HTTP specific actions
        if ($this->isCli) {
            if ($_SERVER['argc'] < 3) {
				$this->outputTask->log('Usage: php ' . $_SERVER['argv'][0] . ' <Project> <Script> [param1 param2 ...]');
                exit;
            }
			// Establish the controller and method to call
			$projectName = $_SERVER['argv'][1];
			$projectNameCustom = $projectName . 'Custom';
			$scriptName = (isset($_SERVER['argv'][2])) ? $_SERVER['argv'][2] : 'index';
			$scriptClassName = '\\' . $projectName . '\\' . $scriptName;
			$args = array_slice($_SERVER['argv'], 2);
        }
        else {
			// TODO: Implement Web interface
			throw new Exception('Error: Web interface not yet implemented.');
            //$this->enableOutputFlush();
        }

		/// Test to make sure the requested project and script exist
		if (!class_exists($projectNameCustom)) {
			$projectNameCustom = $projectName;
		}
		if (!class_exists($projectName)) {
			throw new Exception('Error: Class ' . $projectName . " does not exist.");
		}
		if (!class_exists($scriptClassName)) {
			throw new Exception('Error: Script ' . $scriptClassName . " does not exist.");
		}

		$this->initScript($projectName, $projectNameCustom, $scriptName, $scriptClassName, $args);
	}

	/**
	 * @param $projectName
	 * @param $projectNameCustom
	 * @param $scriptName
	 * @param $scriptClassName
	 * @param $args
	 * @throws Exception
	 */
	public function initScript($projectName, $projectNameCustom, $scriptName, $scriptClassName, $args)
	{
		/** @var $project DeployerProject */
		$project = new $projectNameCustom();
		/** @var $script DeployerScript */
		$script = new $scriptClassName();

		// Combine settings from the project script and default app settings
		$scriptSettings = $project->getScriptSettings($scriptName);
		$scriptSettings += $script->getSettings();
		$scriptSettings += $project->getSettings();
		$scriptSettings += $this->settings;

		$entities = $project->getScriptEntities($scriptName);

		// The context is the run-time settings
		$context = $scriptSettings;
		$context['appStartTime'] = $this->appStartTime;
		$context['scriptStartTime'] = time();
		$context['dateTimeSlug'] = date('Y-m-d_H-i-s', $context['scriptStartTime']);
		$context['scriptName'] = $script->getName();
		$context['isCli'] = $this->isCli;
		$context['projectFolder'] = $project->getFolder();
		$context['projectName'] = $project->getName();

		if (! is_dir($context['projectFolder'])) {
			if (! mkdir($context['projectFolder'], 0777, true)) {
				throw new Exception('Error: Unable to create project folder ' . $context['projectFolder']);
			}
		}

		$context['projectLogsFolder'] = $context['projectFolder'] . '/logs';
		if (! is_dir($context['projectLogsFolder'])) {
			if (! mkdir($context['projectLogsFolder'], 0777, true)) {
				throw new Exception('Error: Unable to create logs folder ' . $context['projectLogsFolder']);
			}
		}

		$context['projectCacheFolder'] = $context['projectFolder'] . '/cache';
		// Test if folder exists
		if (! is_dir($context['projectCacheFolder'])) {
			if (! mkdir($context['projectCacheFolder'], 0777, true)) {
				throw new Exception('Error: Unable to create working copies cache folder ' . $context['projectCacheFolder']);
			}
		}

		$context['projectTempFolder'] = $context['projectFolder'] . '/temp';
		// Test if folder exists
		if (! is_dir($context['projectTempFolder'])) {
			if (! mkdir($context['projectTempFolder'], 0777, true)) {
				throw new Exception('Error: Unable to create temporary folder ' . $context['projectTempFolder']);
			}
		}

		$context['logFile'] = '';
		if (isset($context['log.enable']) && $context['log.enable'] == true) {
			$context['logFile'] = $context['projectLogsFolder'] . '/' . $projectName . '-' . $scriptName;
			if (isset($context['log.distinct']) && $context['log.distinct'] == true) {
				$context['logFile'] .= '_' . $context['dateTimeSlug'];
			}
			$context['logFile'] .= '.txt';
		}

		// Add a file logger to the output, if desired
		if (strlen($context['logFile'])) {
			$this->outputTask->addLogger(new FileLogger($context['logFile']));
		}

		$tasks = new TaskCollection();
		$tasks->outputTask = $this->outputTask;
		$tasks->cliTask = new CliTask($tasks->outputTask);
		$tasks->commandTask = new CommandTask($tasks->outputTask);
		$tasks->svnTask = new SvnTask($tasks->outputTask, $tasks->commandTask, $context['cmd.svn']);
		$tasks->viewTask = new ViewTask($context['projectFolder']);
		$tasks->emailTask = new EmailTask($tasks->outputTask);

		$execEnv = new ExecutionEnvironment($tasks, $entities, $script->getScriptClosure(), $context, $args);

		// Output the script title, if desired
		if ($context['output.scriptTitle'] === true) {
			$tasks->outputTask->logScriptHeading($script->getName());
		}

		$execEnv->runScript();
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
				$this->outputTask->logException($str . "\n");
				exit(1);
				break;

			case E_USER_WARNING:
				$this->outputTask->log("WARNING: [$errno] $errstr\nError on line $errline in file $errfile\n");
				break;

			case E_USER_NOTICE:
				$this->outputTask->log("NOTICE: [$errno] $errstr\nError on line $errline in file $errfile\n");
				break;

			default:
				$this->outputTask->log("UNKNOWN: [$errno] $errstr\nError on line $errline in file $errfile\n");
				break;
		}

		// Don't execute PHP internal error handler
		//return true;
		return false;
	}

	/**
	 * This method is designed to handle un caught exceptions so it should be registered with set_exception_handler()
	 * @param \Exception $e
	 */
	public function exceptionHandlerCallback($e)
	{
		$this->outputTask->logException($e->getMessage());
		exit;
	}

	/**
	 *
	 */
	public function shutDownCallback()
	{
		$this->outputTask->logElapsedTime($this->appStartTime);
	}
}
