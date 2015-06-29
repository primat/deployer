<?php namespace Primat\Deployer\Task;
/**
 *
 */

use Primat\Deployer\Entity\Git\GitRepository;
use Primat\Deployer\Exception;
use Primat\Deployer\Exception\ExitStatusException;
use Primat\Deployer\Exception\TaskException;
use Primat\Deployer\Task;

/**
 * Class implementing wrappers for Git commands
 * Class GitTask
 * @package Primat\Deployer\Task
 */
class GitTask
{
	/**  @var string $cacheFolder */
	protected $cacheFolder;
	/**  @var string $cmdSvn */
	protected $cmdGit;
	/**  @var \Primat\Deployer\Task\CommandTask $commandTask */
	protected $commandTask;
	/**  @var \Primat\Deployer\Task\FileSystemTask $fileSystemTask */
	protected $fileSystemTask;
	/**  @var bool $isCli */
	protected $isCli;
	/**  @var \Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;

	/**
	 * Constructor
	 * @param OutputTask $outputTask
	 * @param CommandTask $commandTask
	 * @param FileSystemTask $fileSystemTask
	 * @param $cacheFolder
	 * @param bool $isCli
	 * @param string $cmdGit
	 */
	public function __construct(OutputTask $outputTask, CommandTask $commandTask, FileSystemTask $fileSystemTask,
								$cacheFolder, $isCli = true, $cmdGit = 'git')
	{
		$this->cacheFolder = $cacheFolder;
		$this->cmdGit = $cmdGit;
		$this->commandTask = $commandTask;
		$this->fileSystemTask = $fileSystemTask;
		$this->isCli = $isCli;
		$this->outputTask = $outputTask;
	}

	/**
	 * @param $path
	 * @param string $branch
	 * @return bool
	 * @throws Exception\CommandException
	 * @throws ExitStatusException
	 * @throws TaskException
	 */
	public function checkout($path, $branch = 'master')
	{
		$this->outputTask->log("- Checking out branch '$branch'\n");
		$cwd = getcwd();
		if (!chdir($path)) {
			throw new TaskException(__CLASS__ . '::' . __METHOD__ . "() Unable to change directory to $path");
		}
		$cmd = $this->cmdGit . " checkout $branch 2>&1";
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n\n");
		chdir($cwd);
		return true;
	}

	/**
	 * @param $path
	 * @return bool
	 * @throws Exception\CommandException
	 * @throws ExitStatusException
	 * @throws TaskException
	 */
	public function cleanWorkingTree($path)
	{
		$this->outputTask->log("- Clean the working tree\n");
		$cwd = getcwd();
		if (!chdir($path)) {
			throw new TaskException(__CLASS__ . '::' . __METHOD__ . "() Unable to change directory to $path");
		}
		$cmd = $this->cmdGit . " clean -d -f -x " . addslashes($path) . " 2>&1";
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n\n");
		chdir($cwd);
		return true;
	}

	/**
	 * @param $path
	 * @return string
	 * @throws Exception\CommandException
	 * @throws ExitStatusException
	 * @throws TaskException
	 */
	public function getCurrentBranch($path)
	{
		$branchName = '';
		$cwd = getcwd();
		if (!chdir($path)) {
			throw new TaskException(__CLASS__ . '::' . __METHOD__ . "() Unable to change directory to $path");
		}
		$cmd = $this->cmdGit . " branch 2>&1";
		$result = $this->commandTask->runCmd($cmd, false);

		$branches = explode("\n", $result);

		foreach ($branches as $index => $branch) {
			if (strpos($branch, '*') === 0) {
				$branchName = mb_substr($branch, 2);
				break;
			}
		}
		chdir($cwd);
		return $branchName;
	}

	/**
	 * @param $path
	 * @param string $repositoryAlias
	 * @return string
	 * @throws Exception\CommandException
	 * @throws ExitStatusException
	 * @throws TaskException
	 */
	public function getRemoteUrl($path, $repositoryAlias = 'origin')
	{
		$cwd = getcwd();
		if (!chdir($path)) {
			throw new TaskException(__CLASS__ . '::' . __METHOD__ . "() Unable to change directory to $path");
		}
		$cmd = $this->cmdGit . " config --get remote.$repositoryAlias.url 2>&1";
		$remoteUrl = trim($this->commandTask->runCmd($cmd, false));
		chdir($cwd);
		return $remoteUrl;
	}

	/**
	 * @param GitRepository $repo
	 * @return string
	 */
	public function getWorkingTreePath(GitRepository $repo)
	{
		return $this->cacheFolder . '/' . $repo->getId() . '.git';
	}

	/**
	 * @param GitRepository $repo
	 * @throws Exception\CommandException
	 * @throws ExitStatusException
	 * @throws TaskException
	 */
	public function gitClone(GitRepository $repo)
	{
		$workingTreePath = $this->getWorkingTreePath($repo);
		$this->outputTask->log("- Cloning into $workingTreePath\n");
		$cwd = getcwd();
		if (!chdir($workingTreePath)) {
			throw new TaskException(__CLASS__ . '::' . __METHOD__ . "() Unable to change directory to $workingTreePath");
		}
		$cmd = $this->cmdGit . " clone -q " . $repo->getUrl() . " " . addslashes($workingTreePath) . " 2>&1";
		$this->commandTask->runCmd($cmd);
		chdir($cwd);
		$this->outputTask->log("\n");
	}

	/**
	 * @param $path
	 * @return bool
	 * @throws ExitStatusException
	 * @throws TaskException
	 */
	public function isWorkingTree($path)
	{
		$cwd = getcwd();
		if (!chdir($path)) {
			throw new TaskException(__CLASS__ . '::' . __METHOD__ . "() Unable to change directory to $path");
		}

		$cmd = $this->cmdGit . " rev-parse 2>&1";
		try {
			$this->commandTask->runCmd($cmd, false);
			chdir($cwd);
		}
		catch (ExitStatusException $e) {
			return false;
		}
		return true;
	}

	/**
	 * @param $path
	 * @return bool
	 * @throws Exception\CommandException
	 * @throws ExitStatusException
	 * @throws TaskException
	 */
	public function pull($path)
	{
		$this->outputTask->log("- Pulling from ...\n");
		$cwd = getcwd();
		if (!chdir($path)) {
			throw new TaskException(__CLASS__ . '::' . __METHOD__ . "() Unable to change directory to $path");
		}
		$cmd = $this->cmdGit . " pull 2>&1";
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n\n");
		chdir($cwd);
		return true;
	}

	/**
	 * @param GitRepository $repo
	 * @param string $branch
	 * @return string
	 * @throws TaskException
	 */
	public function refreshWorkingTree(GitRepository $repo, $branch = 'master')
	{
		$this->outputTask->log("-- Refreshing the working tree of repository '" . $repo->getId() . "'\n\n");
		$workingTreePath = $this->getWorkingTreePath($repo);
		$this->fileSystemTask->createFolder($workingTreePath);
		$cwd = getcwd();
		if (!chdir($workingTreePath)) {
			throw new TaskException(__CLASS__ . '::' . __METHOD__ . "() Unable to change directory to $workingTreePath");
		}

		if ($this->isWorkingTree($workingTreePath) && $this->getRemoteUrl($workingTreePath) === $repo->getUrl()) {
			// This folder is already a working tree
			// Check if it's the correct Repo Branch
			$branchName = $this->getCurrentBranch($workingTreePath);
			if ($branchName !== $branch) {
				// Do a git checkout to load the correct branch into the working tree
				$this->checkout($workingTreePath, $branch);
			}
			$this->resetWorkingTree($workingTreePath);
			$this->cleanWorkingTree($workingTreePath);
			$this->pull($workingTreePath);
		}
		else {
			// Remove contents of the folder and clone the repo
			$this->fileSystemTask->deleteFolder($workingTreePath, false);
			$this->gitClone($repo);
		}
		chdir($cwd);
		return $workingTreePath;
	}

	/**
	 * @param string $path
	 * @return bool
	 * @throws Exception\CommandException
	 * @throws ExitStatusException
	 * @throws TaskException
	 */
	public function resetWorkingTree($path)
	{
		$this->outputTask->log("- Doing a hard reset of the working tree\n");
		$cwd = getcwd();
		if (!chdir($path)) {
			throw new TaskException(__CLASS__ . '::' . __METHOD__ . "() Unable to change directory to $path");
		}
		$cmd = $this->cmdGit . " reset --hard HEAD 2>&1";
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n\n");
		chdir($cwd);
		return true;
	}
}
