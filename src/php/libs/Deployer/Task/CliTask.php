<?php namespace Primat\Deployer\Task;

use Primat\Deployer\Entity;
use Primat\Deployer\Entity\Account;
use Primat\Deployer\Entity\Database;
use Primat\Deployer\Entity\Dir;
use Primat\Deployer\Service\Logger;
use Primat\Deployer\Task;

/**
 * Performs task related to the command line interface
 */
class CliTask
{
	/**  @var \Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;

    /**
     * Constructor
     * @param OutputTask $outputTask
     */
	public function __construct(OutputTask $outputTask)
	{
		$this->outputTask = $outputTask;
	}

	/**
	 * Prompt the user for an account password
	 * @param Account $account
	 * @param bool $forcePrompt
	 */
	public function promptAccountPassword(Account $account, $forcePrompt = FALSE)
	{
		// Prompt for all necessary passwords
		if (empty($account->password) || $forcePrompt) {
			$this->outputTask->log('Enter password for user ' . $account->username . ': ');
			$account->password = $this->readStdin();
			$this->outputTask->log("\n");
		}
	}

	/**
	 * Prompt the user to select a database
	 * @param Database[] $databases
	 * @param string $promptText
	 * @return \Primat\Deployer\Entity\Database
	 */
	public function promptDatabase($databases = NULL, $promptText = 'Choose a database:')
	{
		$choices = array();
		if (empty($databases)) {
			$databases = Entity::getList('Database', true);
		}
		$mapping = array();
		foreach($databases as $index => $db) {
			/** @var $db \Primat\Deployer\Entity\Database */
			$mapping[] = $db;
			$choices[] = $db->getDbName() . ' on ' . $db->getHost()->getHostname();
		}
		$selection = CliTask::promptMultipleChoice($choices, $promptText);
		return $mapping[$selection];
	}

	/**
	 * Ask he user if they want to quit the script immediately
	 * @param string $promptMessage
	 */
	public function promptQuit($promptMessage = '')
	{
		if (empty($promptMessage)) {
			$promptMessage = 'Press y to continue or n to quit [y/n]: ';
		}

		$input  = '';
		while ($input !== 'y' && $input !== 'n') {
			$this->outputTask->log( "\n" . $promptMessage );
			$input = $this->readStdin();
			$this->outputTask->log($input . "\n");
		}

		$this->outputTask->log("\n");

		if ($input === 'n') {
			$this->outputTask->log("Exiting immediately\n");
			exit();
		}
	}

	/**
	 * Prompt a user to select one amongst many options
	 * @param mixed[] $choices
	 * @param string $customPromptText
	 * @return int
	 */
	public function promptMultipleChoice(array $choices, $customPromptText = "")
	{
		if (empty($customPromptText)) {
			$customPromptText = "Please choose one of the following:\n";
		}
		else {
			$customPromptText .= "\n";
		}
		$choiceCnt = count($choices);
		$choiceText = '';
		if ($choiceCnt === 1) {
			$choiceText = '1 or ';
		}
		else if ($choiceCnt > 1) {
			$choiceText = "1-{$choiceCnt} or ";
		}
		$result = 0;

		while(TRUE) {
			$result = 0;
			$this->outputTask->log($customPromptText);
			$counter = 1;
			if ($choiceCnt < 1) {
				$this->outputTask->log("No choices available\n");
			}
			foreach($choices as $i => $choice) {
				$this->outputTask->log("\t[{$counter}] {$choice}\n");
				$counter++;
			}

			$this->outputTask->log("Choice [{$choiceText}e(x)it]: ");
			$result = trim($this->readStdin());

			$this->outputTask->log("\n");
			if ($result === 'x') {
				$this->outputTask->log("Exiting immediately\n");
				exit;
			}
			else if (ctype_digit($result) && $result > 0 && $result <= $choiceCnt) {
				break;
			}
		}
		return $result - 1;
	}

	/**
	 * Prompt the user to select a local directory to sync file from
	 * @param string $promptText
	 * @return \Primat\Deployer\Entity\Dir
	 */
	public function promptLocalSyncDir($promptText = 'Choose a local directory to sync from:')
	{
		$choices = array();
		$dirs = Entity::getList('Dir');
		$mapping = array();
		foreach($dirs as $index => $dir) {
			/** @var $dir \Primat\Deployer\Entity\Dir */
			if ($dir->host === NULL && is_dir($dir->path) && stripos($dir->path, BUILD_ROOT_DIR) === FALSE) {
				$mapping[] = $dir;
				$choices[] = $dir->path;
			}
		}
		$selection = CliTask::promptMultipleChoice($choices, $promptText);
		return $mapping[$selection];
	}

	/**
	 * Prompt the user to select a repository to sync
	 * @param string $promptText
	 * @return \Primat\Deployer\Entity\WorkingCopy
	 */
	public function promptRepo($promptText = 'Choose a repository to sync:')
	{
		$choices = array();
		$workingCopies = Entity::getList('WorkingCopy');
		$mapping = array();
		foreach($workingCopies as $index => $workingCopy) {
			/** @var $workingCopy \Primat\Deployer\Entity\WorkingCopy */
				$mapping[] = $workingCopy;
				$choices[] = $workingCopy->repoUrl;
		}
		$selection = CliTask::promptMultipleChoice($choices, $promptText);
		return $mapping[$selection];
	}

	/**
	 * Prompt the user to select a directory for a specific remote host
	 * @param Dir[] $dirs
	 * @param string $promptText
	 * @return mixed
	 */
	public function promptDir(array $dirs, $promptText = 'Choose a remote directory:')
	{
		$choices = array();
		if (empty($dirs)) {
			$dirs = Entity::getList('Dir');
		}
		foreach($dirs as $index => $dir) {
			/** @var $dir \Primat\Deployer\Entity\Dir */
			$displayString = $dir->getPath();
			if ($dir->getHost() !== NULL) {
				$displayString .= ' on ' . $dir->getHost()->hostname;
			}
			$choices[] = $displayString;
		}
		$selection = CliTask::promptMultipleChoice($choices, $promptText);
		return $dirs[$selection];
	}

	/**
	 * Prompt the user to select a directory for a specific remote host
	 * @param null $hostFilter
	 * @param string $promptText
	 * @return mixed
	 */
	public function promptRemoteDir($hostFilter = NULL, $promptText = 'Choose a remote directory:')
	{
		$choices = array();
		$dirs = Entity::getList('Dir');
		$mapping = array();
		foreach($dirs as $index => $dir) {
			/** @var $dir \Primat\Deployer\Entity\Dir */
			if ($hostFilter === NULL || $dir->getHost() === $hostFilter) {
				$mapping[] = $dir;
				$choices[] = $dir->getPath();
			}
		}
		$selection = CliTask::promptMultipleChoice($choices, $promptText);
		return $mapping[$selection];
	}

	/**
	 * Prompt the user to choose a host
	 * @param Entity\Host[] $hosts
	 * @param string $promptText
	 * @return Entity\Host
	 */
	public function promptHost(array $hosts = NULL, $promptText = 'Choose a host:')
	{
		$choices = array();
		if (empty($hosts)) {
			$hosts = Entity::getList('Host');
		}
		foreach($hosts as $index => $host) {
			/** @var $host \Primat\Deployer\Entity\Host */
			$name = $host->hostname;
			if (! empty($host->name)) {
				$name .= ' - ' . $host->name;
			}
			$choices[] = $name;
		}
		$selection = CliTask::promptMultipleChoice($choices, $promptText);
		return $hosts[$selection];
	}

	/**
	 * Prompt the user to select a working copy
	 * @param string $promptText
	 * @return mixed
	 */
	public function promptWorkingCopy($promptText = 'Choose a working copy:')
	{
		$choices = array();
		$workingCopies = Entity::getList('WorkingCopy');
		foreach($workingCopies as $index => $wc) {
			/** @var $wc \Primat\Deployer\Entity\WorkingCopy */
			$choices[] = $wc->id;
		}
		$selection = CliTask::promptMultipleChoice($choices, $promptText);
		return $workingCopies[$selection];
	}

	/**
	 * Read characters from STDIN until enter is pressed
	 * @return string
	 */
	public function readStdin()
	{
		$fr = fopen("php://stdin","r");
		do {
			$input = fgets($fr, 128);
			$input = rtrim($input);
		} while (empty($input));
		fclose ($fr);
		return $input;
	}
}
