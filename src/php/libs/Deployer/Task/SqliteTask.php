<?php namespace Primat\Deployer\Task;

use \Primat\Deployer\Entity\WorkingCopy;
use \Primat\Deployer\Entity\Account;
use \Primat\Deployer\Entity\Node;
use \Primat\Deployer\Entity\File;
use \Primat\Deployer\Entity\Database;
use \Primat\Deployer\Exception;
use \Primat\Deployer\Config;
use \Primat\Deployer\Task;

/**
 *
 */
class SqliteTask
{
	//protected $lastDumpFileName ='';

	/**
	 * @param \Primat\Deployer\Entity\Database $db
	 * @param \Primat\Deployer\Entity\File $dumpFile
	 * @param $dbName
	 * @param array $tables
	 * @throws \Primat\Deployer\Exception
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
