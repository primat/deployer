<?php namespace Primat\Deployer\Task;
/**
 * Created by PhpStorm
 * Date: 6/26/2015
 */

use \Primat\Deployer\Entity\Dir;
use \Primat\Deployer\Entity\Host;
use \Primat\Deployer\Exception;
use \Primat\Deployer\Exception\TaskException;
use \Primat\Deployer\Task;

/**
 * Class SftpTask
 * @package Primat\Deployer\Task
 */
class SftpTask
{
	/**  @var \Primat\Deployer\Task\FileSystemTask $fileSystemTask */
	protected $fileSystemTask;
	/** @var \Net_SFTP[] $handles An array of SSH connection handles */
	protected $handles = [];
	/**  @var \Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;

	/**
	 * Constructor
	 * @param OutputTask $outputTask
	 * @param FileSystemTask $fileSystemTask
	 */
	public function __construct(OutputTask $outputTask, FileSystemTask $fileSystemTask)
	{
		$this->outputTask = $outputTask;
		$this->fileSystemTask = $fileSystemTask;
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
			$this->outputTask->log("- Starting an sftp session on {$host->hostname} for user {$host->account->username}\n\n");
			$this->handles[$host->hostname] = $handle = new \Net_SFTP($host->hostname);
			if (! $handle->login($host->account->username, $host->account->password)) {
				throw new Exception(__METHOD__ . '() sftp connection failed');
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
	 * @param Dir $sourceDir
	 * @param Dir $destinationDir
	 * @param $excludedFiles
	 * @param bool $isDryRun
	 * @throws TaskException
	 */
	public function syncDir(Dir $sourceDir, Dir $destinationDir, $excludedFiles = [], $isDryRun = false)
	{
		$sourceHost = $sourceDir->getHost();
		$destinationHost = $destinationDir->getHost();

		// Disable syncing two remote or two local dirs
		if (!(empty($sourceHost) XOR empty($destinationHost))) {
			throw new TaskException(
				__CLASS__ . '::' . __METHOD__ . '() source and destination cannot both be local or remote');
		}

		$sourceIsRemote = !empty($sourceHost);

		if (empty($sourceHost)) {
			$remoteDir =& $destinationDir;
			$localDir =& $sourceDir;
		}
		else {
			$remoteDir =& $sourceDir;
			$localDir =& $destinationDir;
		}

		// Connect to the remote host
		$remoteHost = $remoteDir->getHost();
		$sftp = $this->connect($remoteHost);

		$this->outputTask->log("Synchronizing " . $sourceDir->getLocation() . " to " . $destinationDir->getLocation() . "\n\n");

		// Get the base paths to sync
		$remotePath = $remoteDir->getPath();
		$localPath = $localDir->getPath();

		if ($sourceIsRemote) {
			$this->syncRemote($sftp, $remotePath, $localPath, $excludedFiles, $isDryRun);
		}
		else {
			$this->syncLocal($sftp, $remotePath, $localPath, $excludedFiles, $isDryRun);
		}
		$this->outputTask->log("All files synced\n");
	}

	/**
	 * Merge two file name lists into a single array. This is a helper method for the sftp sync methods
	 * @param $localFiles
	 * @param $remoteFiles
	 * @param $excludedFiles
	 * @return array
	 */
	protected function prepFilesList($localFiles, $remoteFiles, $excludedFiles)
	{
		$excludedFiles += ['.', '..'];
		$files = $localFiles + $remoteFiles;
		foreach ($excludedFiles as $exclusion => $dummy) {
			if (isset($files[$exclusion])) {
				unset($files[$exclusion]);
			}
		}

		//  Reset array values
		foreach($files as $key => $value) {
			$files[$key] = '';
		}

		// Sort and return
		ksort($files);
		return $files;
	}

	/**
	 * @param \NET_SFTP $sftp
	 * @param $remoteDirPath
	 * @param $localDirPath
	 * @param array $excludedFiles
	 * @param bool $isDryRun
	 * @throws TaskException
	 */
	protected function syncLocal(\NET_SFTP $sftp, $remoteDirPath, $localDirPath, array $excludedFiles, $isDryRun)
	{
		// Get the list of files in the local directory
		$localFiles = scandir($localDirPath);
		$localFiles = array_flip($localFiles);

		// Get the list of files in the corresponding remote directory
		$remoteFiles = $sftp->rawlist($remoteDirPath);
		if (empty($remoteFiles)) {
			$remoteFiles = ['.' => ''];
			if (!$isDryRun) {
				throw new TaskException(__CLASS__ . '::' . __METHOD__ .
					"() Folder $remoteDirPath should be created before attempting to read its contents");
			}
		}

		$files = $this->prepFilesList($localFiles, $remoteFiles, $excludedFiles);

		foreach ($files as $name => $dummy) {

			$localPath = $localDirPath . $name;
			$remotePath = $remoteDirPath . $name;

			if (!isset($remoteFiles[$name])) {

				$info = [
					'type' => is_dir($localPath) ? 2 : 1,
				];

				// Copy local file/folder
				if ($info['type'] == 2) { // It's a directory, create it remotely and recurse to the next level
					$localPath .= '/';
					$remotePath .= '/';
					$this->outputTask->log("C $remotePath\n");
					if (!$isDryRun) {
						$sftp->mkdir($remotePath);
					}
					$this->syncLocal($sftp, $remotePath, $localPath, $excludedFiles, $isDryRun);
				}
				else { // It's a file, upload it
					$this->outputTask->log("PUT $remotePath\n");
					if (!$isDryRun) {
						$sftp->put($remotePath, $localPath, NET_SFTP_LOCAL_FILE);
					}
				}
			}
			else if (!isset($localFiles[$name])) {

				// Delete remote file/folder
				$this->outputTask->log("DEL $remotePath\n");
				if (!$isDryRun) {
					$sftp->delete($remotePath, true);
				}
			}
			else {
				// Get info on the local file and compare it with with remote file

				$info = [
					//'mtime' => filemtime($localPath),
					'size' => filesize($localPath),
					'type' => is_dir($localPath) ? 2 : 1,
				];


				if ($info['type'] != $remoteFiles[$name]['type']) {

					$this->outputTask->log("DEL $remotePath\n");
					if (!$isDryRun) {
						$sftp->delete($remotePath, true);
					}

					if ($info['type'] == 2) { // It's a directory, create it remotely and recurse to the next level
						$localPath .= '/';
						$remotePath .= '/';
						$this->outputTask->log("NEW $remotePath\n");
						if (!$isDryRun) {
							$sftp->mkdir($remotePath);
						}
						$this->syncLocal($sftp, $remotePath, $localPath, $excludedFiles, $isDryRun);
					}
					else { // It's a file, download it
						$this->outputTask->log("PUT $localPath\n");
						if (!$isDryRun) {
							$sftp->put($remotePath, $localPath, NET_SFTP_LOCAL_FILE);
						}
					}
				}
				else {
					if ($info['type'] == 1 && $remoteFiles[$name]['size'] != $info['size']) {
						$this->outputTask->log("PUT $remotePath\n");
						if (!$isDryRun) {
							$sftp->delete($remotePath, true);
							$sftp->put($remotePath, $localPath, NET_SFTP_LOCAL_FILE);
						}
					}
				}
			}
		}
	}

	/**
	 * @param \NET_SFTP $sftp
	 * @param $remoteDirPath
	 * @param $localDirPath
	 * @param array $excludedFiles
	 * @param bool $isDryRun
	 * @throws TaskException
	 */
	protected function syncRemote(\NET_SFTP $sftp, $remoteDirPath, $localDirPath, array $excludedFiles, $isDryRun)
	{
		// Get the list of files in the remote directory
		$remoteFiles = $sftp->rawlist($remoteDirPath);

		// Get the list of files in the corresponding local directory
		if (is_dir($localDirPath)) {
			$localFiles = scandir($localDirPath);
			$localFiles = array_flip($localFiles);
		}
		else {
			$localFiles = ['.' => ''];
			if (!$isDryRun) {
				throw new TaskException(__CLASS__ . '::' . __METHOD__ .
					"() Folder $localDirPath should be created before attempting to read its contents");
			}
		}

		$files = $this->prepFilesList($localFiles, $remoteFiles, $excludedFiles);

		foreach ($files as $name => $dummy) {

			$localPath = $localDirPath . $name;
			$remotePath = $remoteDirPath . $name;

			if (!isset($remoteFiles[$name])) {
				// Delete local file/folder
				$this->outputTask->log("DEL $localPath\n");
				if (!$isDryRun) {
					$this->fileSystemTask->deleteFile($localPath);
				}
			}
			else if (!isset($localFiles[$name])) {
				// Copy remote file/folder
				if ($remoteFiles[$name]['type'] == 2) { // It's a directory, create it locally and recurse to the next level
					$localPath .= '/';
					$remotePath .= '/';
					$this->outputTask->log("NEW $localPath\n");
					if (!$isDryRun) {
						mkdir($localPath);
					}
					$this->syncRemote($sftp, $remotePath, $localPath, $excludedFiles, $isDryRun);
				}
				else { // It's a file, download it
					$this->outputTask->log("GET $localPath\n");
					if (!$isDryRun) {
						$sftp->get($remotePath, $localPath);
					}
				}
			}
			else {
				// Get info on the local file and compare it with with remote file
				$info = [
					//'mtime' => filemtime($localPath),
					'size' => filesize($localPath),
					'type' => is_dir($localPath) ? 2 : 1,
				];

				if ($remoteFiles[$name]['type'] != $info['type']) {
					$this->outputTask->log("DEL $localPath\n");
					if (!$isDryRun) {
						$this->fileSystemTask->deleteFile($localPath);
					}

					if ($remoteFiles[$name]['type'] == 2) { // It's a directory, create it locally and recurse to the next level
						$localPath .= '/';
						$remotePath .= '/';
						$this->outputTask->log("NEW $localPath\n");
						if (!$isDryRun) {
							mkdir($localPath);
						}
						$this->syncRemote($sftp, $remotePath, $localPath, $excludedFiles, $isDryRun);
					}
					else { // It's a file, download it
						$this->outputTask->log("GET $localPath\n");
						if (!$isDryRun) {
							$sftp->get($remotePath, $localPath);
						}
					}
				}
				else {
					if ($remoteFiles[$name]['type'] == 1 && $remoteFiles[$name]['size'] != $info['size']) {
						$this->outputTask->log("GET $localPath\n");
						if (!$isDryRun) {
							$this->fileSystemTask->deleteFile($localPath);
							$sftp->get($remotePath, $localPath);
						}
					}
				}
			}
		}
	}
}
