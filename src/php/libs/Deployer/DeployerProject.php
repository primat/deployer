<?php namespace Primat\Deployer;
/**
 * Project: Deployer
 * User: mprice
 * Date: 11/05/15
 */

use Pimple\Container;

/**
 * Class DeployerProject
 * @package Primat\Deployer\Utils
 */
class DeployerProject
{
	const DIR_LOGS = '/logs';
	const DIR_TEMP = '/temp';
	const DIR_WC = '/workingCopies';

	/**  @var $cliTask \Primat\Deployer\Task\CliTask */
	protected $cliTask;
	/**  @var $emailTask \Primat\Deployer\Task\EmailTask */
	protected $emailTask;
	/**  @var $fileSyncTask \Primat\Deployer\Task\FileSyncTask */
	protected $fileSyncTask;
	/**  @var $fileSystemTask \Primat\Deployer\Task\FileSystemTask */
	protected $fileSystemTask;
	/**  @var $mysqlTask \Primat\Deployer\Task\MysqlTask */
	protected $mysqlTask;
	/**  @var $sftpTask \Primat\Deployer\Task\SftpTask */
	protected $sftpTask;
	/**  @var $sqliteTask \Primat\Deployer\Task\SqliteTask */
	protected $sqliteTask;
	/**  @var $sshTask \Primat\Deployer\Task\SshTask */
	protected $sshTask;
	/**  @var $svnTask \Primat\Deployer\Task\SvnTask */
	protected $svnTask;
	/**  @var $timerTask \Primat\Deployer\Task\TimerTask */
	protected $timerTask;
	/**  @var $viewTask \Primat\Deployer\Task\ViewTask */
	protected $viewTask;

	/**  @var $viewTask \Primat\Deployer\Service\Logger */
	protected $logger;

	/**  @var $viewTask \Primat\Deployer\Service\Logger */
	//protected $logger;

	/**  @var $projectFolder string */
	protected $projectFolder = __DIR__;
	/**  @var $tempFolder string */
	protected $tempFolder = '';
	/**  @var $logsFolder string */
	protected $logsFolder = '';
	/**  @var $workingCopiesFolder string */
	protected $workingCopiesFolder = '';




	/**
	 * Constructor
	 * @param string $projectFolder
	 * @param string $tempFolder
	 */
	public function __construct($projectFolder, $tempFolder)
	{
//		$this->tempFolder = $tempFolder . '/temp';
//		$this->logFolder = $this->tempFolder . '/logs';
//		$this->wcCacheFolder = $this->tempFolder . '/workingCopies';
	}

	public function registerTasks(Container $diContainer)
	{
		$this->cliTask = $diContainer['cliTask'];
		$this->emailTask = $diContainer['emailTask'];
		$this->fileSyncTask = $diContainer['fileSyncTask'];
		$this->fileSystemTask = $diContainer['fileSystemTask'];
		$this->mysqlTask = $diContainer['mysqlTask'];
		$this->sftpTask = $diContainer['sftpTask'];
		$this->sqliteTask = $diContainer['sqliteTask'];
		$this->sshTask = $diContainer['sshTask'];
		$this->svnTask = $diContainer['svnTask'];
		$this->timerTask = $diContainer['timerTask'];
		$this->viewTask = $diContainer['viewTask'];
	}

	//
	// Getters and Setters
	//

	/**
	 * @param string $logsFolder
	 */
	public function setLogsFolder($logsFolder)
	{
		$this->logsFolder = $logsFolder;
	}

	/**
	 * @return string
	 */
	public function getLogsFolder()
	{
		if (empty($this->logsFolder)) {
			return $this->projectFolder . self::DIR_LOGS;
		}
		return $this->logsFolder;
	}

	/**
	 * @param string $projectFolder
	 */
	public function setProjectFolder($projectFolder)
	{
		$this->projectFolder = $projectFolder;
	}

	/**
	 * @return string
	 */
	public function getProjectFolder()
	{
		return $this->projectFolder;
	}

	/**
	 * @param string $tempFolder
	 */
	public function setTempFolder($tempFolder)
	{
		$this->tempFolder = $tempFolder;
	}

	/**
	 * @return string
	 */
	public function getTempFolder()
	{
		if (empty($this->tempFolder)) {
			return $this->projectFolder . self::DIR_TEMP;
		}
		return $this->tempFolder;
	}

	/**
	 * @param string $workingCopiesFolder
	 */
	public function setWorkingCopiesFolder($workingCopiesFolder)
	{
		$this->workingCopiesFolder = $workingCopiesFolder;
	}

	/**
	 * @return string
	 */
	public function getWorkingCopiesFolder()
	{
		if (empty($this->workingCopiesFolder)) {
			return $this->projectFolder . self::DIR_WC;
		}
		return $this->workingCopiesFolder;
	}
}
