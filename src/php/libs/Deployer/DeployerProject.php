<?php namespace Deployer;
/**
 * Project: Deployer
 * User: mprice
 * Date: 11/05/15
 */

use Pimple\Container;

/**
 * Class DeployerProject
 * @package Deployer\Utils
 */
class DeployerProject
{
	/**  @var $cliTask \Deployer\Task\CliTask */
	protected $cliTask;
	/**  @var $emailTask \Deployer\Task\EmailTask */
	protected $emailTask;
	/**  @var $fileSyncTask \Deployer\Task\FileSyncTask */
	protected $fileSyncTask;
	/**  @var $fileSystemTask \Deployer\Task\FileSystemTask */
	protected $fileSystemTask;
	/**  @var $mysqlTask \Deployer\Task\MysqlTask */
	protected $mysqlTask;
	/**  @var $sftpTask \Deployer\Task\SftpTask */
	protected $sftpTask;
	/**  @var $sqliteTask \Deployer\Task\SqliteTask */
	protected $sqliteTask;
	/**  @var $sshTask \Deployer\Task\SshTask */
	protected $sshTask;
	/**  @var $svnTask \Deployer\Task\SvnTask */
	protected $svnTask;
	/**  @var $timerTask \Deployer\Task\TimerTask */
	protected $timerTask;
	/**  @var $viewTask \Deployer\Task\ViewTask */
	protected $viewTask;

	/**  @var $viewTask \Deployer\Service\Logger */
	protected $logger;

	/**  @var $viewTask \Deployer\Service\Logger */
	//protected $logger;


	protected $projectFolder = '';
	protected $tempFolder = '';
	protected $logfile = '';
	protected $workingCopiesFolder = '';

	/**
	 * @param string $logFolder
	 */
	public function setLogFolder($logFolder)
	{
		$this->logFolder = $logFolder;
	}

	/**
	 * @return string
	 */
	public function getLogFolder()
	{
		return $this->logFolder;
	}

	/**
	 * @param \Deployer\Service\Logger $projectFolder
	 */
	public function setProjectFolder($projectFolder)
	{
		$this->projectFolder = $projectFolder;
	}

	/**
	 * @return \Deployer\Service\Logger
	 */
	public function getProjectFolder()
	{
		return $this->projectFolder;
	}

	/**
	 * @param string $wcCacheFolder
	 */
	public function setWcCacheFolder($wcCacheFolder)
	{
		$this->wcCacheFolder = $wcCacheFolder;
	}

	/**
	 * @return string
	 */
	public function getWcCacheFolder()
	{
		return $this->wcCacheFolder;
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
		return $this->workingCopiesFolder;
	}




	/**
	 * Constructor
	 * @param string $projectFolder
	 * @param string $tempFolder
	 */
	public function __construct($projectFolder, $tempFolder)
	{
		$this->tempFolder = $tempFolder . '/temp';
		$this->logFolder = $this->tempFolder . '/logs';
		$this->wcCacheFolder = $this->tempFolder . '/workingCopies';
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

}
