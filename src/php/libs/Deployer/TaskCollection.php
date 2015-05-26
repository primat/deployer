<?php namespace Primat\Deployer;
/**
 * Project: Deployer
 * Date: 19/05/15
 */

/**
 * Class DeployerTasks
 * @package Primat\Deployer
 */
class TaskCollection
{
	/** @var \Primat\Deployer\Task\CliTask $cliTask */
	public $cliTask;
	/** @var \Primat\Deployer\Task\CommandTask $commandTask */
	public $commandTask;
	/** @var \Primat\Deployer\Task\EmailTask $emailTask */
	public $emailTask;
	/** @var \Primat\Deployer\Task\FileSyncTask $fileSyncTask */
	public $fileSyncTask;
	/** @var \Primat\Deployer\Task\FileSystemTask $fileSystemTask */
	public $fileSystemTask;
	/** @var \Primat\Deployer\Task\MysqlTask $mysqlTask */
	public $mysqlTask;
	/** @var \Primat\Deployer\Task\SftpTask $sftpTask */
	public $sftpTask;
	/** @var \Primat\Deployer\Task\SqliteTask $sqliteTask */
	public $sqliteTask;
	/** @var \Primat\Deployer\Task\SshTask $sshTask */
	public $sshTask;
	/** @var \Primat\Deployer\Task\SvnTask $svnTask */
	public $svnTask;
	/** @var \Primat\Deployer\Task\TimerTask $timerTask */
	public $timerTask;
	/** @var \Primat\Deployer\Task\OutputTask $outputTask */
	public $outputTask;
	/** @var \Primat\Deployer\Task\ViewTask $viewTask */
	public $viewTask;
}
