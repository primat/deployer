<?php namespace Deployer;

use Deployer\Config;
use Deployer\Service\Logger;
use Deployer\Task\CliTask;
use Pimple\Container;

/**
 * Class for handling application operations such as exception handlers, shutdown functions, etc
 */

class App
{
	/** @var int $startTime The time that the build script started running */
	//protected $startTime = 0;


    protected $container;

	/**
	 * Initialize the build app
	 */
	public function __construct()
	{
		//$projectFolder
		//$this->startTime = time();



//		$container['emailTask'];
//		$container['fileSyncTask'];
//		$container['fileSystemTask'];
//		$container['mysqlTask'];
//		$container['sftpTask'];
//		$container['sqliteTask'];
//		$container['sshTask'];
//		$container['svnTask'];
//		$container['timerTask'];
//		$container['viewTask'];





//
//
//		$container['config'] = function ($c) {
//			return new SessionStorage('SESSION_ID');
//		};











//		/*
//		 * Path to a directory where temporary files are created during script execution
//		 */
//		define('BUILD_TMP_DIR', BUILD_ROOT_DIR . DIRECTORY_SEPARATOR . 'tmp');
//
//		/*
//		 * Path to the directory where working copies are cached
//		 */
//		define('BUILD_WORKING_COPY_DIR', BUILD_ROOT_DIR . DIRECTORY_SEPARATOR . 'working_copies');
//		//define('BUILD_WORKING_COPY_DIR', BUILD_ROOT_DIR . DIRECTORY_SEPARATOR . self::$projectName . DIRECTORY_SEPARATOR . 'working_copies');
//
//		/*
//		 * Whether or not the script is running in the CLI as opposed to a request for a web page
//		 */
//		define('IS_CLI', substr(php_sapi_name(), 0, 3) === 'cli');
//
//		// Try and figure out the full path to the currently running script
//		$path = realpath($_SERVER['SCRIPT_FILENAME']);
//		if ($path === false) {
//			$path = realpath($_SERVER['SCRIPT_NAME']);
//		}
//		if ($path === false) {
//			throw new \Exception('Unable to determine script path');
//		}
//		$pathParts = pathinfo($path);
//
//		// Disable output buffering for "streaming" display through HTTP and get the path (parts) to the script
//		if (! IS_CLI) {
//			self::enableOutputFlush();
//		}
//
//		/*
//		 * Name of the script file that was called (file name without the file extension)
//		 */
//		define('SCRIPT_FILE_BASENAME', $pathParts['filename']);
//
//		/*
//		 * Path to the location that the script is running in
//		 */
//		define('SCRIPT_DIR', $pathParts['dirname']);
//
//		/*
//		 * Path to the location that the script is running in
//		 */
//		define('SCRIPT_DB_DIR', SCRIPT_DIR . '/db');
//
//		/*
//		 * Path to the location where email files are stored
//		 */
//		define('BUILD_EMAILS_DIR', SCRIPT_DIR . "/emails");
//
//		/*
//		 * Path to the location where log files are stored
//		 */
//		define('BUILD_LOGS_DIR', SCRIPT_DIR . "/logs");
//
//		/*
//		 * Register some important handlers
//		 */
//		set_exception_handler(array('\Deployer\Task', 'exceptionHandler'));
//		register_shutdown_function(array('\Deployer\Task', 'endOfScriptMaintenance'));
//
//		/*
//		 * Load the default build configuration
//		 */
//		Config::set('datetime.slug', date('Y-m-d_H-i-s'));
	}

	/**
	 * Gets the script start time (Either 0 or the first time when App::init() was called)
	 */
//	public static function getStartTime()
//	{
//		return self::$startTime;
//	}
//
//	/**
//	 * Gets the directory to the application root
//	 */
//	public static function getAppRootDir()
//	{
//		return realpath(__DIR__ . '/../../../../') . '/';
//	}
//
//	/**
//	 * Flushes output for the HTTP buffer
//	 */
//	private static function enableOutputFlush()
//	{
//		ini_set('output_buffering', 'off');
//		ini_set('zlib.output_compression', false);
//		while (@ob_end_flush());
//		ini_set('implicit_flush', true);
//		ob_implicit_flush(true);
//	}


    /**
     * @param DeployerProject $project
     */
	public function registerProject(DeployerProject $project)
	{
		//echo get_class($project);

        $this->container = new Container();

        $container['project'] = $project;

        $container['logger'] = function ($c) {
            return new Logger();
        };

        $container['cliTask'] = function ($c) {
            return new CliTask($c['logger']);
        };


	}

	public function runScript($className, $methodName)
	{

	}

}
