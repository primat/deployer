<?php namespace Primat\Deployer\Task;

use \Primat\Deployer\Entity\Host;
use \Primat\Deployer\Entity\Database;
use \Primat\Deployer\Entity\File;
use \Primat\Deployer\Exception;
use \Primat\Deployer\Exception\TaskException;
use \Primat\Deployer\Task;

//require_once BUILD_ROOT_DIR . '/vendor/autoload.php';
//require_once BUILD_ROOT_DIR . '/vendor/phpseclib/phpseclib/phpseclib/Net/SFTP.php';
//require_once BUILD_ROOT_DIR . '/vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php';
//
//define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX); // NET_SFTP_LOG_COMPLEX or NET_SFTP_LOG_SIMPLE
//define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX); // NET_SSH2_LOG_COMPLEX or NET_SSH2_LOG_SIMPLE

/**
 * Class SshTask
 * @package Primat\Deployer\Task
 */
class SshTask
{
	const SSH_KEY_NAME = 'deployer-generated-key';

	/** @var \Net_SFTP[] $handles An array of SSH connection handles */
	protected $handles = [];
	/**  @var \Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;
	/**  @var string $tempFolder */
	protected $tempFolder = '';

	/**
	 * Constructor
	 * @param OutputTask $outputTask
	 * @param string $tempFolder
	 */
	public function __construct(OutputTask $outputTask, $tempFolder)
	{
		$this->outputTask = $outputTask;
		$this->tempFolder = $tempFolder;
	}

	/**
	 * @param \Primat\Deployer\Entity\Host $host
	 * @return \Net_SFTP
	 * @throws \Primat\Deployer\Exception
	 */
	public function connect(Host $host)
	{
		// Throw an exception if no host is provided
		if (empty($host)) {
			throw new Exception(__METHOD__ . "() No host specified");
		}

		// If the connection doesn't already exist, create it
		if (empty($this->handles[$host->hostname])) {
			$this->outputTask->log("- Starting an SSH session on {$host->hostname} for user {$host->account->username}\n\n");
			$this->handles[$host->hostname] = $handle = new \Net_SFTP($host->hostname);
			if (! $handle->login($host->account->username, $host->account->password)) {
				throw new Exception(__METHOD__ . '() SSH connection failed');
			}
			// Set the home folder, if it isn't explicitly set already
			$homeDirPath = $handle->pwd();
			if (empty($host->homeDirPath) && ! empty($homeDirPath)) {
				$host->homeDirPath = $homeDirPath;
			}
		}
		return $this->handles[$host->hostname];
	}

	/**
	 * Copies a file on the remote server from $remoteFile->path to $newPath
	 * @param File $remoteFile
	 * @param $newPath
	 * @throws \Primat\Deployer\Exception
	 */
	public function copyFile(File $remoteFile, $newPath)
	{
		$this->exec($remoteFile->getHost(), "cp {$remoteFile->getPath()} {$newPath}");
	}

	/**
	 * Generate an SSH public / private key pair
	 * @return array
	 */
	public function generateKeyPair()
	{
		$publicKey = '';
		$privateKey = '';
		$rsa = new \Crypt_RSA();
		$rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_OPENSSH);
		extract($rsa->createKey());
		$publicKey = str_replace('phpseclib-generated-key', self::SSH_KEY_NAME, $publicKey);
		return array($publicKey, $privateKey);
	}

	/**
	 * @param Host $remoteHost
	 * @return array
	 * @throws \Primat\Deployer\Exception
	 */
//	public static function getAuthorizedKeys(Host $remoteHost)
//	{
//		$sftp = $this->connect($remoteHost);
//		$fileContents = $sftp->get("/home/{$remoteHost->account->username}/.ssh/authorized_keys");
//		$fileContents = trim($fileContents);
//		$lastError = trim($sftp->getLastSFTPError());
//		if (strlen($lastError)) {
//			if (strpos($lastError, 'NET_SFTP_STATUS_EOF') === FALSE &&
//				strpos($lastError, 'NET_SFTP_STATUS_NO_SUCH_FILE') === FALSE) {
//				throw new Exception(__METHOD__ . "()\n\t" . $lastError);
//			}
//		}
//
//		$result = array();
//		if (empty($fileContents)) {
//			echo var_export($result);
//			return $result;
//		}
//
//		// Parse the authorized keys file and convert keys to objects/arrays
//		$parts = explode("\n", $fileContents);
//		foreach ($parts as $part) {
//			$part = trim($part);
//			if (empty($part)) {
//				continue;
//			}
//			$subParts = explode(' ', $part);
//			$tmpParts = array();
//			foreach($subParts as $subPart) {
//				$subPart = trim($subPart);
//				if (empty($subPart)) {
//					continue;
//				}
//				$tmpParts[] = $subPart;
//			}
//			$result[] = $tmpParts;
//		}
//		return $result;
//	}

	/**
	 * Provides automation for command line ssh commands which require a tty/pty
	 * @param Host $remoteHost
	 * @throws \Primat\Deployer\Exception
	 */
	public function GenerateTemporaryKeyPair(Host $remoteHost)
	{
		$sftp = $this->connect($remoteHost);
		$sshDir = "{$remoteHost->homeDirPath}/.ssh";

		// Create the .ssh folder if it doesn't exist
		if (! $this->dirExists($sftp, $sshDir)) {
			$sftp->mkdir($sshDir, 0700);
			$lastError = trim($sftp->getLastSFTPError());
			if (strlen($lastError)) {
				throw new Exception(__METHOD__ . "() \$sftp->mkdir() failed\n\t" . $lastError);
			}
		}

		// Get the contents of the authorized_keys file
		$fileContents = trim($sftp->get($sshDir . "/authorized_keys")) . "\n";
		$lastError = trim($sftp->getLastSFTPError());
		if (strlen($lastError)) {
			if (strpos($lastError, 'NET_SFTP_STATUS_EOF') === FALSE &&
				strpos($lastError, 'NET_SFTP_STATUS_NO_SUCH_FILE') === FALSE) {
				throw new Exception(__METHOD__ . "() \$sftp->get()\n\t" . $lastError);
			}
		}

		// Produce the key pair
		list($publicKey, $privateKey) = $this->generateKeyPair();

		// Clean up old keys and add the new one
		$fileContents = $this->removeTemporaryAuthorizedKeys($fileContents);
		$fileContents .= $publicKey . "\n";
		$cmdResult = $sftp->put($sshDir . "/authorized_keys", $fileContents);
		if(! $cmdResult) {
			$lastError = trim($sftp->getLastSFTPError());
			if (strlen($lastError)) {
				if (strpos($lastError, 'NET_SFTP_STATUS_EOF') === FALSE) {
					throw new Exception(__METHOD__ . "() \$sftp->put()\n\t" . $lastError);
				}
			}
		}

		$sftp->chmod(0644, $sshDir . "/authorized_keys");

		$remoteHost->privateKeyPath = $this->tempFolder . '/.id_rsa_' .$remoteHost->hostname;

		// Copy the private key to a local temporary file
		if(file_put_contents($remoteHost->privateKeyPath , $privateKey) === FALSE) {
			throw new Exception(__METHOD__ . "()\n\t Could not create temporary private key file");
		}
	}

	/**
	 * @param $authorizedKeys
	 * @return string
	 */
	protected function removeTemporaryAuthorizedKeys($authorizedKeys)
	{
		$result = '';
		$lines = explode("\n", $authorizedKeys);
		foreach($lines as $line) {
			$line = trim($line);
			if (empty($line)) {
				continue;
			}
			if (strpos($line, self::SSH_KEY_NAME) === FALSE) {
				$result .= $line . "\n";
			}
		}
		return $result;
	}

	/**
	 * @param \Primat\Deployer\Entity\File $localFile
	 * @param \Primat\Deployer\Entity\File $remoteFile
	 * @param int $chmod
	 * @throws \Primat\Deployer\Exception
	 */
	public function uploadFile(File $localFile, File $remoteFile, $chmod = 0664)
	{
		$sftp = $this->connect($remoteFile->getHost());
		$this->outputTask->log("- Uploading $localFile->name to {$remoteFile->getHost()->hostname}:{$remoteFile->getPath()}\n");
		$res = $sftp->put($remoteFile->getPath(), $localFile->getPath(), NET_SFTP_LOCAL_FILE);
		if (! $res || count($sftp->getSFTPErrors())) {
			throw new Exception(__METHOD__ . "()\n\t" . implode("\n\t", $sftp->getSFTPErrors()));
		}
		$res = $sftp->chmod($chmod, $remoteFile->getPath());
		if (! $res || count($sftp->getSFTPErrors())) {
			throw new Exception(__METHOD__ . "()\n\t" . implode("\n\t", $sftp->getSFTPErrors()));
		}
		$this->outputTask->log("\n");
	}

	/**
	 * Moves a file on the remote server from $remoteFile->path to $newPath
	 * @param File $remoteFile
	 * @param $newPath
	 * @throws \Primat\Deployer\Exception
	 */
	public function moveFile(File $remoteFile, $newPath)
	{
		$sftp = $this->connect($remoteFile->getHost());
		$this->outputTask->log("- Moving {$remoteFile->getPath()} to {$newPath}\n");
		$res = $sftp->rename($remoteFile->getPath(), $newPath);
		if (! $res || count($sftp->getSFTPErrors())) {
			throw new Exception(__METHOD__ . "()\n\t" . implode("\n\t", $sftp->getSFTPErrors()));
		}
		$this->outputTask->log("\n");
	}

	/**
	 * @param \Primat\Deployer\Entity\File $localDumpFile
	 * @param \Primat\Deployer\Entity\Database $db
	 * @param string $dbName
	 */
	public function importDb(File $localDumpFile, Database $db, $dbName)
	{
		$remoteFile = new File('/home/'.$db->host->account->username, $localDumpFile->name, $db->host);
		$this->uploadFile($localDumpFile, $remoteFile);
		$this->mysqlImport($remoteFile, $db, $dbName);
		$this->deleteFile($remoteFile);
	}

	/**
	 * Import a sql file into mysql by copying a temp file through SSH then importing it with mysql and deleting the
	 * dump afterward
	 * @param \Primat\Deployer\Entity\File $dumpFile
	 * @param \Primat\Deployer\Entity\Database $db
	 * @param $dbName
	 * @throws \Primat\Deployer\Exception
	 */
	public function mysqlImport(File $dumpFile, Database $db, $dbName)
	{
		$this->outputTask->log("- Importing {$dumpFile->name} into {$dbName}\n");
		$command = "mysql -u {$db->account->username} -p{$db->account->password} {$dbName} < {$dumpFile->getPath()}";
		$this->exec($db->getHost(), $command);
		$this->outputTask->log("\n");
	}

	/**
	 * Delete a file on the remote Host
	 * @param File $remoteFile
	 * @throws TaskException
	 */
	public function deleteFile(File $remoteFile)
	{
		// Make sure there is a connection open
		$ssh = $this->connect($remoteFile->getHost());

		$this->outputTask->log("- Deleting file " . $remoteFile->getPath() . "\n");
		$res = $ssh->delete($remoteFile->getPath());
		if (! $res || count($ssh->getSFTPErrors())) {
			throw new TaskException(__METHOD__ . "() - File deletion failed: \n\t" .
				implode("\n\t", $ssh->getSFTPErrors()));
		}
		$this->outputTask->log("\n");
	}

	/**
	 * Execute a one-off command on a remote host
	 * @param \Primat\Deployer\Entity\Host $host The host to run the command on
	 * @param string $command The actual command to execute
	 * @param bool $printOutput Whether or not to display the output from the command
	 * @throws \Primat\Deployer\Exception
	 */
	public function exec(Host $host, $command, $printOutput = TRUE)
	{
		// Make sure there is a connection open
		$ssh = $this->connect($host);

		// log the command
		//$this->outputTask->log("{$host->account->username}@{$host->hostname}: $command\n");

		// Execute the command
		if ($printOutput) {
			$ssh->exec($command, function($str) {
					$this->outputTask->log($str);
			});
			$this->outputTask->log("\n");
		}
		else {
			$ssh->exec($command);
		}
		$this->checkCmdExceptions($ssh);
	}

	/**
	 * Test if the given directory exists on the remote machine
	 * @param \Net_SSH2 $ssh The ssh connection handle
	 * @param string $path The directory to test for existence
	 * @return bool TRUE if the directory exists, FALSE otherwise
	 */
	public function dirExists(\Net_SSH2 $ssh, $path)
	{
		$command = '[ -d ' . $path . ' ] && echo "1" || echo "0"';
		$output = trim($ssh->exec($command));
		$this->checkCmdExceptions($ssh);
		return $output === "1";
	}

	/**
	 * Tests if a given file exists on the remote server
	 * @param \Primat\Deployer\Entity\File $file
	 * @return bool
	 */
	public function fileExists(File $file)
	{
		// Make sure there is a connection
		if (!$file->isRemote()) {
			echo "Warning: Testing for file existence on local machine rather than a remote one";
			return file_exists($file->getPath());
		}

		$command = '[ -f ' . $file->getPath() . ' ] && echo "1" || echo "0"';
		$ssh = $this->connect($file->getHost());
		$output = trim($ssh->exec($command));
		$this->checkCmdExceptions($ssh);
		return $output === "1";
	}

	/**
	 * Run this method after executing a command to detect errors
	 * @param \Net_SSH2 $handle
	 * @throws TaskException
	 */
	protected function checkCmdExceptions(\Net_SSH2 $handle)
	{
		if ($handle->getExitStatus() > 0) {
			throw new TaskException("Command failed with exit status ".$handle->getExitStatus()."\n\t" .
				implode("\n\t", $handle->getErrors()));
		}
		else if (count($handle->getErrors())) {
			throw new TaskException(__CLASS__ . ": Command failed.\n\t" . implode("\n\t", $handle->getErrors()));
		}
	}
}
