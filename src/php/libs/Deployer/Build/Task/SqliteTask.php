<?php namespace Deployer\Build\Task;

use \Deployer\Build\Entity\WorkingCopy;
use \Deployer\Build\Entity\Account;
use \Deployer\Build\Entity\Node;
use \Deployer\Build\Entity\File;
use \Deployer\Build\Entity\Database;
use \Deployer\Build\Exception;
use \Deployer\Build\Config;
use \Deployer\Build\Task;

/**
 *
 */
class SqliteTask extends Task
{
	//protected $lastDumpFileName ='';

	/**
	 * @param \Deployer\Build\Entity\Database $db
	 * @param \Deployer\Build\Entity\File $dumpFile
	 * @param $dbName
	 * @param array $tables
	 * @throws \Deployer\Build\Exception
	 */
	public static function mysqlDump(Database $db, $dbName, array $tables = array(), File $dumpFile)
	{
		if (! is_array($tables) || count($tables) === 0) {
			$tablesParam = '';
		}
		else {
			$tablesParam = explode(' ', $tables) . ' ';
		}

		// Presentation
		self::log("- Getting dump of {$dbName}");
		if ($db->isRemote()) {
			Task::log(" from {$db->host->hostname}");
		}
		self::log("\n");

		// Build the command
		$cmd = Config::get('mysqldump.bin') . " -vf -P {$db->port}";
		if ($db->isRemote()) {
			$cmd .= " -h {$db->host->hostname}";
		}

		// Create the folder if it doesn't exist already
		FileSystemTask::mkdir($dumpFile->dir->getPath());

		$cmd .= " -u {$db->account->username} -p{$db->account->password} {$dbName} {$tablesParam}| sed '/^\\/\\*\\!50013 DEFINER/d' > {$dumpFile->getPath()}";

		self::runCmd($cmd);
		self::log("\n");
	}

}
