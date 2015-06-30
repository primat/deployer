<?php namespace Primat\Deployer\Task;
/**
 *
 */

use \Primat\Deployer\Entity\Database;
use \Primat\Deployer\Entity\File;
use \Primat\Deployer\Exception;
use \Primat\Deployer\Exception\TaskException;

/**
 * Class MysqlTask
 * @package Primat\Deployer\Task
 */
class MysqlTask
{
	/**  @var string $cmdMysql */
	protected $cmdMysql;
	/**  @var string $cmdMysqlDump */
	protected $cmdMysqlDump;
	/**  @var string $cmdMysqlImport */
	protected $cmdMysqlImport;
	/**  @var \Primat\Deployer\Task\CommandTask $commandTask */
	protected $commandTask;
	/**  @var \Primat\Deployer\Task\FileSystemTask $fileSystemTask */
	protected $fileSystemTask;
	/**  @var \Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;

	/**
	 * Construct
	 * @param OutputTask $outputTask
	 * @param CommandTask $commandTask
	 * @param FileSystemTask $fileSystemTask
	 * @param string $cmdMysql
	 * @param string $cmdMysqlDump
	 * @param string $cmdMysqlImport
	 */
	public function __construct(OutputTask $outputTask, CommandTask $commandTask, FileSystemTask $fileSystemTask,
		$cmdMysql = 'mysql', $cmdMysqlDump = 'mysqldump', $cmdMysqlImport = 'mysqlimport')
	{
		$this->commandTask = $commandTask;
		$this->fileSystemTask = $fileSystemTask;
		$this->outputTask = $outputTask;
		$this->cmdMysql = $cmdMysql;
		$this->cmdMysqlDump = $cmdMysqlDump;
		$this->cmdMysqlImport = $cmdMysqlImport;
	}

	/**
	 * @param \Primat\Deployer\Entity\Database $db
	 * @param \Primat\Deployer\Entity\File $dumpFile
	 * @param $dbName
	 * @param array $tables
	 * @return bool
	 * @throws \Primat\Deployer\Exception
	 */
	public function createDumpFile(Database $db, $dbName, array $tables = array(), File $dumpFile)
	{
		if (! is_array($tables) || count($tables) === 0) {
			$tablesParam = '';
		}
		else {
			$tablesParam = explode(' ', $tables) . ' ';
		}

		// Presentation
		$this->outputTask->log("- Getting dump of {$dbName}");
		if ($db->isRemote()) {
			$this->outputTask->log(" from {$db->host->hostname}");
		}
		$this->outputTask->log("\n");

		// For use when converting a dump to SQLite
		// --compatible=ansi --skip-extended-insert --compact

		// Build the command
		$cmd = $this->cmdMysqlDump . " -vf -P {$db->port}";
		if ($db->isRemote()) {
			$cmd .= " -h {$db->host->hostname}";
		}

		// Create the folder if it doesn't exist already
		$this->fileSystemTask->createFolder($dumpFile->dir->getPath());

		$cmd .= " -u {$db->account->username} -p{$db->account->password} {$dbName} {$tablesParam}| sed '/^\\/\\*\\!50013 DEFINER/d' > {$dumpFile->getPath()}";
		//$cmd .= " --result-file={$dumpFile->getPath()} -u {$db->account->username} -p{$db->account->password} {$dbName} {$tablesParam} 2>&1";

		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n");

		return true;
	}

	/**
	 * @param Database $db
	 * @param string $dumpFilePath
	 * @param string $dbName
	 * @throws TaskException
	 */
	public function importDump(Database $db, $dumpFilePath, $dbName)
	{
		$command = $this->cmdMysql . ' -P ' . $db->port . ' -h ' . $db->host->hostname . ' -u ' . $db->account->username .
			' -p"' . $db->account->password . '" ' . $dbName . ' < ' . $dumpFilePath . ' 2>&1';

		$this->outputTask->log("- Importing MySQL dump to DB {$dbName} at {$db->host->hostname}:{$db->port} \n");
		passthru($command, $err);
		if ($err !== 0) {
			throw new TaskException(__CLASS__ . '::' .  __METHOD__ . '() failed');
		}
		$this->outputTask->log("\n");
	}

	/**
	 * @param \Primat\Deployer\Entity\Database $db
	 * @param $dumpFilePath
	 * @param $dbName
	 * @param $options
	 */
	public function mysqlImport(Database $db, $dumpFilePath, $dbName, $options)
	{
		// use --debug-info to output some debug info
		$cmd = $this->cmdMysqlImport . " -P {$db->port} -h {$db->host->hostname} -u {$db->account->username} " .
			"-p{$db->account->password} {$options} {$dbName} \"{$dumpFilePath}\" 2>&1";
		$this->outputTask->log("- Importing {$dumpFilePath} into {$dbName} on {$db->host->hostname}\n");
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n");
	}
}
