<?php namespace Primat\Deployer;
/**
 * Project: Deployer
 * Date: 19/05/15
 */

/**
 * Class DeployerScript
 * @package Primat\Deployer
 */
abstract class DeployerScript
{
    /** @var string $name The name of the script */
    protected $name = '';
	/** @var string[] $settings Script specific settings */
	protected $settings = [];

	//
	// These next vars are only here for IDE autocomplete
	//

	/** @var string[] $context */
	protected $context = [];
	/** @var TaskCollection $tasks */
	protected $tasks;
	/** @var \Primat\Deployer\Task\CliTask $cli */
	protected $cliTask;
	/** @var \Primat\Deployer\Task\EmailTask $email */
	protected $emailTask;
	/** @var \Primat\Deployer\Task\FileSyncTask $fileSync */
	protected $fileSyncTask;
	/** @var \Primat\Deployer\Task\FileSystemTask $fileSystem */
	protected $fileSystemTask;
	/** @var \Primat\Deployer\Task\MysqlTask $mysql */
	protected $mysqlTask;
	/** @var \Primat\Deployer\Task\SftpTask $sftp */
	protected $sftpTask;
	/** @var \Primat\Deployer\Task\SqliteTask $sqlite */
	protected $sqliteTask;
	/** @var \Primat\Deployer\Task\SshTask $ssh */
	protected $sshTask;
	/** @var \Primat\Deployer\Task\SvnTask $svn */
	protected $svnTask;
	/** @var \Primat\Deployer\Task\TimerTask $timer */
	protected $timerTask;
	/** @var \Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;
	/** @var \Primat\Deployer\Task\ViewTask $view */
	protected $viewTask;

	/**
	 * Initial app and task settings
	 * @return array
	 */
	public function getName()
	{
		// Set the default human readable script name as the current class name if not set already in subclass
		if (empty($this->name)) {
			return get_class($this);
		}
		return $this->name;
	}

	/**
	 * @return callable
	 */
	abstract public function getScriptClosure();

	/**
	 * Initial app and task settings
	 * @return string[]
	 */
	public function getSettings()
	{
		return $this->settings;
	}
}
