<?php namespace Primat\Deployer\Model;
/**
 * Created by PhpStorm
 * Date: 6/4/2015
 * Time: 10:20 PM
 */

use Primat\Deployer\Service\Logging\ConsoleLogger;
use Primat\Deployer\Service\Logging\HtmlLogger;
use Primat\Deployer\Task\CliTask;
use Primat\Deployer\Task\CommandTask;
use Primat\Deployer\Task\FileSystemTask;
use Primat\Deployer\Task\FileSyncTask;
use Primat\Deployer\Task\OutputTask;
use Primat\Deployer\Task\SshTask;
use Primat\Deployer\Task\SvnTask;
use Primat\Deployer\Task\ViewTask;
use Primat\Deployer\Task\EmailTask;
use Primat\Deployer\Utils\cygwin;
use Primat\Deployer\Utils\Expect;

/**
 * Class TaskModel
 * @package Primat\Deployer\Model
 */
class TaskModel
{
	//Tasks
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
	/** @var \Primat\Deployer\Task\MysqlTask $mysql */
	public $mysql;
	/** @var \Primat\Deployer\Task\SqliteTask $sqlite */
	public $sqlite;
	/** @var \Primat\Deployer\Task\SshTask $ssh */
	public $ssh;
	/** @var \Primat\Deployer\Task\SvnTask $svn */
	public $svn;
	/** @var \Primat\Deployer\Task\TimerTask $timer */
	public $timer;
	/** @var \Primat\Deployer\Task\OutputTask $output */
	public $output;
	/** @var \Primat\Deployer\Task\ViewTask $view */
	public $view;

	// Services
	/** @var \Primat\Deployer\Utils\Cygwin $cygwin */
	protected $cygwin;
	/** @var \Primat\Deployer\Utils\Expect $expect */
	protected $expect;

	/**
	 * @param string $appFolder
	 * @param string $projectFolder
	 * @param string $cacheFolder
	 * @param string $tmpFolder
	 * @param bool $isCli
	 */
	public function __construct($appFolder, $projectFolder, $cacheFolder, $tmpFolder, $isCli)
	{
		$this->cygwin = new Cygwin();
		$this->expect = new Expect($appFolder, $this->cygwin);

		if ($isCli) {
			$this->output = new OutputTask(new ConsoleLogger());
		}
		else {
			$this->output = new OutputTask(new HtmlLogger());
		}

		$this->cli = new CliTask($this->output);
		$this->command = new CommandTask($this->output);
		$this->fileSystem = new FileSystemTask($this->output);
		$this->ssh = new SshTask($this->output, $tmpFolder);
		$this->fileSync = new FileSyncTask($this->expect, $this->cygwin, $this->output, $this->ssh, $this->command, $isCli);
		$this->svn = new SvnTask($this->output, $this->command, $this->fileSystem, $cacheFolder);
		$this->view = new ViewTask($projectFolder);
		$this->email = new EmailTask($this->output, $isCli);
	}
}
