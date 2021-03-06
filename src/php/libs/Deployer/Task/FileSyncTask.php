<?php namespace Primat\Deployer\Task;

use \Primat\Deployer\Task;
use \Primat\Deployer\Exception;
use \Primat\Deployer\Utils\Cygwin;
use \Primat\Deployer\Utils\Expect;
use \Primat\Deployer\Entity\RsyncOptions;

/**
 * Task for synchronizing files from local to remote, local to local but not remote to remote (use SshTask for that)
 * Class FileSyncTask
 * @package Primat\Deployer\Task
 */
class FileSyncTask
{
	/**  @var \Primat\Deployer\Task\CommandTask $commandTask */
	protected $commandTask;
	/**  @var \Primat\Deployer\Utils\Cygwin $cygwin */
	protected $cygwin;
	/**  @var \Primat\Deployer\Utils\Expect $expect */
	protected $expect;
	/** @var bool $isCli */
	protected $isCli;
	/**  @var \Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;
	/** @var string $rsyncCmd */
	protected $rsyncCmd = 'rsync';
	/**  @var \Primat\Deployer\Task\SshTask $sshTask */
	protected $sshTask;

	/**
	 * Constructor
	 * @param Expect $expect
	 * @param Cygwin $cygwin
	 * @param CommandTask $commandTask
	 * @param OutputTask $outputTask
	 * @param SshTask $sshTask
	 * @param bool $isCli
	 * @param string $rsyncCmd
	 */
	public function __construct(OutputTask $outputTask, Expect $expect, Cygwin $cygwin, SshTask $sshTask,
		CommandTask $commandTask, $isCli, $rsyncCmd = 'rsync')
	{
		$this->isCli = $isCli;
		$this->expect = $expect;
		$this->cygwin = $cygwin;
		$this->commandTask = $commandTask;
		$this->outputTask = $outputTask;
		$this->sshTask = $sshTask;
		$this->rsyncCmd = $rsyncCmd;
	}

	/**
	 * Take a set of rsync options, build the command then run it
	 * @param RsyncOptions $rsync
	 */
	public function sync(RsyncOptions $rsync)
	{
		$this->outputTask->log("- Rsyncing " . $rsync->source->getLocation() . " with " . $rsync->destination->getLocation() . "\n\n");

		$remoteHost = $rsync->getRemoteHost();

		if (empty($rsync->identityFilePath) && !$this->isCli) {
			// No CLI and no identity means one must be generated temporarily
			$this->sshTask->GenerateTemporaryKeyPair($remoteHost);
		}

		$command = $this->getRsyncCommand($rsync);

		// Adjust the command if we are using expect
		if ($this->isCli && !empty($remoteHost)) { // IS_CLI &&
			$cmdTemplate = $this->expect->getPasswordCommandTemplate();
			$command = sprintf($cmdTemplate, addslashes($command), addslashes($remoteHost->account->password));
		}
		else {
			$this->outputTask->log("$command\n\n");
		}

		$this->commandTask->runCmd($command);
		$this->outputTask->log("\n\n");
	}

	/**
	 * @param RsyncOptions $rsync
	 * @return string
	 */
	public function getRsyncCommand(RsyncOptions $rsync)
	{
		$cmd = $this->rsyncCmd;

		if (! empty($rsync->flags)) {
			$cmd .= ' -' . $rsync->flags;
		}

		if ($rsync->deleteAfter) {
			$cmd .= ' --delete-after';
		}

		if ($rsync->deleteExcluded) {
			$cmd .= ' --delete-excluded';
		}

		if ($rsync->delete) {
			$cmd .= ' --delete';
		}

		if ($rsync->delayUpdates) {
			$cmd .= ' --delay-updates';
		}

		if ($rsync->dryRun) {
			$cmd .= ' --dry-run';
		}

		if ($rsync->noMotd) {
			$cmd .= ' --no-motd';
		}

		if ($rsync->stats) {
			$cmd .= ' --stats';
		}

		if ($rsync->progress) {
			$cmd .= ' --progress';
		}

		if ($rsync->safeLinks) {
			$cmd .= ' --safe-links';
		}

		if (! empty($rsync->chmod)) {
			$cmd .= ' --chmod=' . $rsync->chmod;
		}

		foreach($rsync->includes as $include) {
			$cmd .= ' --include "' . $include . '"';
		}

		foreach($rsync->excludes as $exclude) {
			$cmd .= ' --exclude "' . $exclude . '"';
		}

		$remoteHost = $rsync->getRemoteHost();

		if ($rsync->useSsh && ! empty($remoteHost)) {

			$cmd .= ' -e "ssh -o ConnectTimeout=5';
			$identity = '';
			if ($rsync->useHostIdentity && ! empty($remoteHost->privateKeyPath)) {
				// No CLI and no identity means a temporary SSL cert must be generated
				$cmd .= ' -i ' . $this->cygwin->getCygPath($remoteHost->privateKeyPath);
			}
			if (! $rsync->sshStrictHostKeyChecking) {
				$cmd .= ' -o StrictHostKeyChecking=no';
			}
			$cmd .= '"';
		}

		// Continue building the command with the source arg
		$cmd .= ' ';
		if ($rsync->source->isRemote() && $rsync->useSsh) {
			$cmd .= "{$rsync->source->getHost()->account->username}@{$rsync->source->getHost()->hostname}:";
		}
		$cmd .= $this->cygwin->getCygPath($rsync->source->getPath());

		// Continue building the command with the destination arg
		$cmd .= ' ';
		if ($rsync->destination->isRemote() && $rsync->useSsh) {
			$cmd .= "{$rsync->destination->getHost()->account->username}@{$rsync->destination->getHost()->hostname}:";
		}
		$cmd .= $this->cygwin->getCygPath($rsync->destination->getPath());

		return $cmd;
	}
}
