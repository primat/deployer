<?php namespace Primat\Deployer\Service;
/**
 * Project: Deployer
 * User: mprice
 * Date: 11/05/15
 */
/**
 * Class Logger
 * @package Primat\Deployer\Service
 */

use \Primat\Deployer\Exception\CommandException;
use \Primat\Deployer\Exception\LoggerException;
use \Primat\Deployer\Exception\ExitStatusException;

class Logger
{
	const CMD_IO_BUFFER = 4096;

	/** @var bool $muteOutput Used to mute all output */
	protected $muteOutput = FALSE;

	/** @var resource $logFileHandle */
	protected $logFileHandle;


	public function __construct()
	{

	}

	/**
	 * @param boolean $muteOutput
	 */
	public function setMuteOutput($muteOutput)
	{
		$this->muteOutput = $muteOutput;
	}

	/**
	 * @return boolean
	 */
	public function getMuteOutput()
	{
		return $this->muteOutput;
	}

	/**
	 * Creates a log file name based on config settings
	 * @return string
	 * @throws LoggerException
	 */
	public function buildLogFilePath()
	{
		$dir = SCRIPT_DIR . "/logs/";
		if (! is_dir($dir)) {
			mkdir($dir);
		}
		if (! is_dir($dir)) {
			throw new LoggerException('Could not create log file directory ' . $dir);
		}
		// Build the complete log file path
		$logFile = $dir . SCRIPT_FILE_BASENAME;
		if (Config::get('logging.distinct') == TRUE) {
			$logFile .= "_" . Config::get('datetime.slug');
		}
		$logFile .= '.txt';

		return $logFile;
	}

	public static function getLogFileHandle()
	{

	}

	/**
	 * @param $message
	 */
	public static function log($message)
	{
		if ($this->muteOutput || empty($message)) {
			return;
		}

		// Output to console/screen/web page/etc
		if (IS_CLI) {
			echo $message;
		}
		else {
			echo nl2br($message);
		}

		// Output to log file
		if (Config::get('logging.enabled')) {

			// Init the file handle if not done already
			if (self::$logFileHandle === NULL) {
				self::initLogFile();
			}
			self::writeLog($message);
		}
		flush();
	}

	/**
	 * Writes a chunk of text to the log file, assuming the log file is open and writable
	 * @param $message
	 */
	public static function writeLog($message)
	{
		if (empty(self::$logFileHandle)) {
			// TODO Consider triggering an error
			return;
		}
		fwrite(self::$logFileHandle, $message);
	}

	/**
	 * Initializes a log file handle for further use
	 * @throws Exception
	 */
	public static function initLogFile()
	{
		if (! empty(self::$logFileHandle)) {
			fclose(self::$logFileHandle);
		}

		$filePath = self::buildLogFilePath();
		self::$logFileHandle = fopen($filePath, 'w');
		if (empty(self::$logFileHandle)) {
			throw new Exception('Unable to create log file ' . $filePath . ' for writing');
		}
	}

	/**
	 * This method is designed to handle un caught exceptions so it should be registered with set_exception_handler()
	 * @param \Exception $e
	 */
	public static function exceptionHandler($e)
	{
		self::log($e->getMessage() . "\n\nAbandon ship!\n---------------------------------------\n");
		exit;
	}

	/**
	 *
	 */
	public static function outputElapsedTime()
	{
		$endTime = time();
		$elapsedTime = $endTime - App::getStartTime();

		if ($elapsedTime === $endTime) {
			$elapsedTime = 0;
		}

		self::log("\n---------------------------------------\n");
		self::log("Script execution time: " . gmdate("H:i:s", $elapsedTime));
		self::log("\n---------------------------------------\n\n");
	}
}
