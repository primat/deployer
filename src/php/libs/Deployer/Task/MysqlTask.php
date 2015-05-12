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
class MysqlTask extends Task
{
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

		// For use when converting a dump to SQLite
		// --compatible=ansi --skip-extended-insert --compact

		// Build the command
		$cmd = Config::get('mysqldump.bin') . " -vf -P {$db->port}";
		if ($db->isRemote()) {
			$cmd .= " -h {$db->host->hostname}";
		}

		// Create the folder if it doesn't exist already
		FileSystemTask::mkdir($dumpFile->dir->getPath());

		$cmd .= " -u {$db->account->username} -p{$db->account->password} {$dbName} {$tablesParam}| sed '/^\\/\\*\\!50013 DEFINER/d' > {$dumpFile->getPath()}";

		//$cmd .= " --result-file={$dumpFile->getPath()} -u {$db->account->username} -p{$db->account->password} {$dbName} {$tablesParam} 2>&1";

		self::runCmd($cmd);
		self::log("\n");
	}

	/**
	 * @param \Primat\Deployer\Entity\Database $db
	 * @param $dumpFilePath
	 * @param $dbName
	 * @throws \Primat\Deployer\Exception
	 */
	public static function importDump(Database $db, $dumpFilePath, $dbName)
	{
		$command = 'mysql -P ' . $db->port . ' -h ' . $db->host->hostname . ' -u ' . $db->account->username .
			' -p"' . $db->account->password . '" ' . $dbName . ' < ' . $dumpFilePath . ' 2>&1';
		
		self::log("- Importing MySQL dump to DB {$dbName} at {$db->host->hostname}:{$db->port} \n");
		passthru($command, $err);
		if ($err !== 0) {
			throw new Exception('- MysqlTask ' . __METHOD__ . '() failed');
		}
		self::log("\n");
	}

	/**
	 * @param \Primat\Deployer\Entity\Database $db
	 * @param $dumpFilePath
	 * @param $dbName
	 * @param $options
	 */
	public static function mysqlImport(Database $db, $dumpFilePath, $dbName, $options)
	{
		// use --debug-info to output some debug info
		$cmd = "mysqlimport -P {$db->port} -h {$db->host->hostname} -u {$db->account->username} " .
			"-p{$db->account->password} {$options} {$dbName} \"{$dumpFilePath}\" 2>&1";
		self::log("- Importing {$dumpFilePath} into {$dbName} on {$db->host->hostname}\n");
		self::runCmd($cmd);
		self::log("\n");
	}
}
