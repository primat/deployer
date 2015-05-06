<?php namespace Deployer\Task;

use \Deployer\Entity\WorkingCopy;
use \Deployer\Entity\Account;
use \Deployer\Entity\Node;
use \Deployer\Entity\File;
use \Deployer\Entity\Database;
use \Deployer\Exception;
use \Deployer\Config;
use \Deployer\Task;

/**
 *
 */
class SqliteTask extends Task
{
	//protected $lastDumpFileName ='';

	/**
	 * @param \Deployer\Entity\Database $db
	 * @param \Deployer\Entity\File $dumpFile
	 * @param $dbName
	 * @param array $tables
	 * @throws \Deployer\Exception
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
