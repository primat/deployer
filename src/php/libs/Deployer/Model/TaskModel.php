<?php namespace Primat\Deployer\Model;
/**
 * Created by PhpStorm
 * Date: 6/4/2015
 */

use Primat\Deployer\Task\CliTask;
use Primat\Deployer\Task\CommandTask;
use Primat\Deployer\Task\EmailTask;
use Primat\Deployer\Task\FileSyncTask;
use Primat\Deployer\Task\FileSystemTask;
use Primat\Deployer\Task\GitTask;
use Primat\Deployer\Task\OutputTask;
use Primat\Deployer\Task\SftpTask;
use Primat\Deployer\Task\SshTask;
use Primat\Deployer\Task\SvnTask;
use Primat\Deployer\Task\ViewTask;
use Primat\Deployer\Utils\Cygwin;
use Primat\Deployer\Utils\Expect;

/**
 * Class TaskModel
 * @package Primat\Deployer\Model
 */
class TaskModel
{
	// Services
	/** @var \Primat\Deployer\Utils\Cygwin $cygwin */
	protected $cygwin;
	/** @var \Primat\Deployer\Utils\Expect $expect */
	protected $expect;

	// Tasks
	/** @var \Primat\Deployer\Task\CliTask $cli */
	public $cli;
	/** @var \Primat\Deployer\Task\CommandTask $command */
	public $command;
	/** @var \Primat\Deployer\Task\EmailTask $email */
	public $email;
	/** @var \Primat\Deployer\Task\FileSyncTask $fileSync */
	public $fileSync;
	/** @var \Primat\Deployer\Task\FileSystemTask $fileSystem */
	public $fileSystem;
	/** @var \Primat\Deployer\Task\GitTask $git */
	public $git;
	/** @var \Primat\Deployer\Task\MysqlTask $mysql */
	public $mysql;
	/** @var \Primat\Deployer\Task\OutputTask $output */
	public $output;
	/** @var \Primat\Deployer\Task\SftpTask $sftp */
	public $sftp;
	/** @var \Primat\Deployer\Task\SqliteTask $sqlite */
	public $sqlite;
	/** @var \Primat\Deployer\Task\SshTask $ssh */
	public $ssh;
	/** @var \Primat\Deployer\Task\SvnTask $svn */
	public $svn;
	/** @var \Primat\Deployer\Task\TimerTask $timer */
	public $timer;
	/** @var \Primat\Deployer\Task\ViewTask $view */
	public $view;

	/**
	 * @param OutputTask $outputTask
	 * @param IProjectModel $projectModel
	 * @param bool $isCli
	 */
	public function __construct(OutputTask $outputTask, $projectModel, $isCli)
	{
		$this->cygwin = new Cygwin();
		$this->expect = new Expect($this->cygwin);

		$this->output = $outputTask;
		$this->cli = new CliTask($this->output);
		$this->command = new CommandTask($this->output);
		$this->fileSystem = new FileSystemTask($this->output);
		$this->sftp = new SftpTask($this->output, $this->fileSystem);
		$this->ssh = new SshTask($this->output, $projectModel->getTempFolder());
		$this->fileSync = new FileSyncTask($this->output, $this->expect, $this->cygwin, $this->ssh, $this->command, $isCli);
		$this->git = new GitTask($this->output, $this->command, $this->fileSystem, $projectModel->getCacheFolder());
		$this->svn = new SvnTask($this->output, $this->command, $this->fileSystem, $projectModel->getCacheFolder());
		$this->view = new ViewTask($projectModel->getViewsFolder());
		$this->email = new EmailTask($this->output);
	}
}
