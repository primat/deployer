<?php namespace Primat\Deployer\Task;

use \Primat\Deployer\Entity\WorkingCopy;
use \Primat\Deployer\Entity\RepositoryBranch;
use \Primat\Deployer\Entity\Svn\SvnBranch;
use \Primat\Deployer\Entity\Svn\SvnExternal;
use \Primat\Deployer\Entity\Svn\SvnInfo;
use \Primat\Deployer\Entity\Svn\SvnLogEntry;
use \Primat\Deployer\Entity\Svn\SvnRepository;
use \Primat\Deployer\Entity\Svn\SvnTag;
use \Primat\Deployer\Entity\Svn\SvnWorkingCopy;
use \Primat\Deployer\Exception;
use \Primat\Deployer\Exception\ExitStatusException;
use \Primat\Deployer\Exception\TaskException;
use \Primat\Deployer\Exception\WorkingCopyException;
use \Primat\Deployer\Task\FileSystemTask;
use \Primat\Deployer\Task;

/**
 * Class for doing subversion related tasks
 * Class SvnTask
 * @package Primat\Deployer\Task
 */
class SvnTask
{
	/**  @var string $cacheFolder */
	protected $cacheFolder;
	/**  @var string $cmdSvn */
	protected $cmdSvn;
	/**  @var string $cmdSvnVersion */
	protected $cmdSvnVersion;
	/**  @var \Primat\Deployer\Task\CommandTask $commandTask */
	protected $commandTask;
	/**  @var \Primat\Deployer\Task\FileSystemTask $fileSystemTask */
	protected $fileSystemTask;
	/**  @var \Primat\Deployer\Task\OutputTask $outputTask */
	protected $outputTask;

	/**
	 * Constructor
	 * @param OutputTask $outputTask
	 * @param CommandTask $commandTask
	 * @param FileSystemTask $fileSystemTask
	 * @param string $cacheFolder
	 * @param string $cmdSvn
	 * @param string $cmdSvnVersion
	 */
	public function __construct(OutputTask $outputTask, CommandTask $commandTask, FileSystemTask $fileSystemTask,
		$cacheFolder, $cmdSvn = 'svn', $cmdSvnVersion = 'svnversion')
	{
		$this->outputTask = $outputTask;
		$this->commandTask = $commandTask;
		$this->fileSystemTask = $fileSystemTask;
		$this->cacheFolder = $cacheFolder;
		$this->cmdSvn = $cmdSvn;
		$this->cmdSvnVersion = $cmdSvnVersion;
	}

	/**
	 * @param SvnBranch $branch
	 * @param int $revision
	 * @return SvnWorkingCopy
	 */
	public function checkout(SvnBranch $branch, $revision = 0)
	{
		$ignoreExternals = true;
		$revision = (int)$revision;
		$cmdParams = '';

		// Start preparing the command
		if ($revision > 0) {
			$cmdParams .= "-r $revision ";
		}
		if ($ignoreExternals) {
			$cmdParams .= "--ignore-externals ";
		}

		// Do the checkout
		$this->outputTask->log("- Checking out ");
		if ($revision > 0) {
			$this->outputTask->log("revision {$revision}\n");
		}
		else {
			$this->outputTask->log("head revision\n");
		}

		$cacheFolder = $this->getCacheFolder($branch);

		$cmd = $this->cmdSvn . " --force --depth infinity {$cmdParams}checkout " .
			$branch->getUrl() . " " . $cacheFolder . " " .
			"--username {$branch->repo->account->username} " .
			"--password {$branch->repo->account->password} " .
			"--config-option config:miscellany:use-commit-times=yes " .
			"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n");

		return new SvnWorkingCopy($branch, $cacheFolder);
	}

	/**
	 * Check out a clean working copy from SVN
	 * @param \Primat\Deployer\Entity\Svn\SvnBranch $branch
	 * @param int $revision
	 * @return SvnWorkingCopy
	 */
	public function checkoutClean(SvnBranch $branch, $revision = 0)
	{
		$cacheFolder = $this->getCacheFolder($branch);
		$this->fileSystemTask->createFolder($cacheFolder, 'working copy');

		$this->outputTask->log("-- Getting a clean checkout at ");
		if ($revision > 0) {
			$this->outputTask->log("revision {$revision}\n\n");
		}
		else {
			$this->outputTask->log("HEAD revision\n\n");
		}

		// Get the repo information to validate the requested revision numbers to checkout
		$svnRemoteInfo = $this->getBranchInfo($branch, $revision);
		$realRevision = $svnRemoteInfo->commitRevision;

		// Correct the requested revision number to the last commit revision
		if ($revision !== $realRevision) {
			$this->outputTask->log("Checkout last commit revision {$realRevision}\n\n");
		}

		$svnInfo = $this->getInfoByPath($cacheFolder);

		// The Working copy and remote repo do not share the same URL.
		// We must delete the working copy and do a fresh checkout
		if (empty($svnInfo) || $svnInfo->getUrl() !== $svnRemoteInfo->getUrl()) {
			$this->fileSystemTask->deleteFolder($cacheFolder, false);
			$svnInfo = null;
		}

		if ($svnInfo === null) {
			$this->outputTask->log("- Checkout a fresh working copy\n\n");
			$workingCopy = $this->checkout($branch, $realRevision);
		}
		else { // Clean up the already existing working copy
			$this->outputTask->log("- Cleaning up the cached working copy\n\n");
			$workingCopy = $this->checkout($branch, $realRevision);
			$this->cleanUp($workingCopy);
		}

		// Do a bit of cleanup
		$this->revert($workingCopy);
		$this->purgeIgnoredAndUnversioned($workingCopy);

		return $workingCopy;
	}

	/**
	 * @param WorkingCopy $workingCopy
	 * @param string $commitMessage
	 */
	public function commit(WorkingCopy $workingCopy, $commitMessage = '')
	{
		$this->outputTask->log("- Committing {$workingCopy->dir->getPath()} to {$workingCopy->repoUrl}\n");
		$cmd = $this->cmdSvn . " commit " . $workingCopy->dir->getPath() . " " .
			'-m "' . $commitMessage . '" ' .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n");
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 */
	public function checkoutTag(WorkingCopy $workingCopy)
	{
		// Checkout the tag to a temporary location
		if (is_dir($workingCopy->dir->getPath())) {
			$this->fileSystemTask->deleteFolder($workingCopy->dir->getPath(), FALSE);
		}
		else {
			mkdir($workingCopy->dir->getPath(), 0775);
		}

		// Do the check out
		$this->outputTask->log("- Checking out {$workingCopy->repoUrl}\n");
		$cmd = $this->cmdSvn . " --force --depth infinity checkout " .
			$workingCopy->repoUrl . " " . $workingCopy->dir->getPath() . " " .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			"--config-option config:miscellany:use-commit-times=yes " .
			"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n");
	}

	/**
	 * Cleans up a working copy (directory)
	 * @param SvnWorkingCopy $workingCopy
	 */
	public function cleanUp($workingCopy)
	{
		$this->outputTask->log("- Running svn cleanup\n");
		$cmd = $this->cmdSvn . " cleanup " . $workingCopy->dir->getPath() . " 2>&1";
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n");
	}

	/**
	 * @param SvnWorkingCopy $workingCopy
	 * @param string $filePath
	 * @param bool $fullTimestamp
	 * @throws TaskException
	 */
	public function createManifestFile(SvnWorkingCopy $workingCopy, $filePath = '', $fullTimestamp = false)
	{
		if (empty($filePath)) {
			$filePath = $workingCopy->dir->getPath() . 'manifest';
		}
		// Create the file
		$this->outputTask->log("- Creating a manifest file\n");

		$revision = $this->getWorkingCopyRevision($workingCopy->dir->getPath());

		$extraSegment = '-' . date('Hi');
		if ($fullTimestamp) {
			$extraSegment .= '-' . $revision;
		}

		if (file_put_contents($filePath, date("Ymd") . $extraSegment) === false) {
			throw new TaskException("Unable to create manifest file");
		}

		$this->outputTask->log("Created manifest file {$filePath} for revision {$revision}\n\n");
	}

	/**
	 * @param WorkingCopy $workingCopy
	 * @return int
	 * @throws \Primat\Deployer\Exception
	 */
	public function getBranchHeadRevision(WorkingCopy $workingCopy)
	{
		$cmd = $this->cmdSvn . " log {$workingCopy->repoUrl}" .
			" --username {$workingCopy->account->username}" .
			" --password {$workingCopy->account->password}" .
			" --xml --stop-on-copy --limit 1 --non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);

		// Parse the XML
		$xmlObj = @simplexml_load_string($xml);

		if (! $xmlObj) {
			throw new Exception("Unable to unmarshall XML:\n" . $xml . "\n");
		}

		if (count($xmlObj->logentry) > 0) {
			$attributes = $xmlObj->logentry->attributes();
			if (! empty($attributes['revision'])) {
				return (int)$attributes['revision'];
			}
		}

		throw new Exception("Unable to get the last revision number for this branch:\n" . $xml . "\n");
	}

	/**
	 * @param SvnRepository $repo
	 * @return array
	 * @throws \Primat\Deployer\Exception\TaskException
	 */
	public function getBranchList(SvnRepository $repo)
	{
		$cmd = $this->cmdSvn . " list " . $repo->getBranchesUrl() . " --xml " .
			"--username {$repo->account->username} " .
			"--password {$repo->account->password} " .
			"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);

		// Parse the XML
		$xmlObj = @simplexml_load_string($xml);
		if (! $xmlObj) {
			throw new TaskException("Unable to unmarshall XML:\n" . $xml . "\n");
		}

		/* @var $xmlObj \SimpleXmlElement */
		$branches = [];
		foreach($xmlObj->list->entry as $index => $entry) { /* @var $entry \SimpleXmlElement */
			$branches[] = new SvnBranch($repo, $repo->getBranchesUri() . '/' . (string)$entry->name);
		}
		return $branches;
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 * @return mixed
	 * @throws \Primat\Deployer\Exception
	 */
	public function getExternals(WorkingCopy $workingCopy)
	{
		$cmd = $this->cmdSvn . " propget -R svn:externals {$workingCopy->dir->getPath()} --xml 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);

		$xmlObj = @simplexml_load_string($xml);
		if (! $xmlObj) {
			throw new Exception("Unable to unmarshall XML:\n" . $xml . "\n");
		}

		$workingCopy->externals = array();
		foreach($xmlObj->target as $target) {
			$workingCopy->externals = array_merge($workingCopy->externals, self::parseExternalsNode($target));
		}

		return $workingCopy->externals;
	}

	/**
	 * @deprecated
	 * @param WorkingCopy $workingCopy
	 * @return array
	 * @throws \Primat\Deployer\Exception
	 */
	public function getExternalsArray(WorkingCopy $workingCopy)
	{
		$this->outputTask->log("- Getting list of externals\n");
		$result = array();
		$cmd = $this->cmdSvn . " propget -R svn:externals " .
			"{$workingCopy->dir->getPath()} " .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
		exec($cmd, $output, $err);
		if ($err !== 0) {
			throw new Exception("SVN get externals failed.\n\t{$cmd}");
		}
		foreach($output as $index => $line) {
			if (! empty($line)) {
				if ($index === 0) {
					$tmpParts = explode('- ', $line, 2);
					if (count($tmpParts) !== 2) {
						throw new Exception("SVN parse externals failed:\n\t".implode('\n\t', $output)."\n");
					}
					$line = $tmpParts[1];
				}
				$tmpParts = explode(' ', $line);
				$result[$index]['url'] = explode('@', $tmpParts[0], 2);
				$partCnt = count($result[$index]['url']);
				if ($partCnt === 2) {
					$result[$index]['revision'] = $result[$index]['url'][1];
					$result[$index]['url'] = $result[$index]['url'][0];
				}
				else {
					$result[$index]['url'] = $result[$index]['url'][0];
				}
				$result[$index]['path'] = $tmpParts[1];
			}
		}
		$this->outputTask->log("\n");
		return $result;
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 * @return int
	 */
	public function getHeadRevision(WorkingCopy $workingCopy)
	{
		$cmd = $this->cmdSvn . " info {$workingCopy->repoUrl} --xml " .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);
		$svnInfo = new SvnInfo($xml);
		return $svnInfo->currentRevision;
	}

	/**
	 * @param string $path
	 * @return \Primat\Deployer\Entity\Svn\SvnInfo
	 */
	public function getInfoByPath($path)
	{
		if (!is_dir($path)) {
			return null;
		}
		$cmd = $this->cmdSvn . " info {$path} --xml 2>&1";

		try {
			$xml = $this->commandTask->runCmd($cmd, false);
		}
		catch (ExitStatusException $e) {
			return null;
		}
		return new SvnInfo($xml);
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 * @param int $limit
	 * @return array
	 */
	public function getLastTagUrls(WorkingCopy $workingCopy, $limit = 5)
	{
		$repoUrl = rtrim($workingCopy->repoUrl, '/'); // The base repo url
		$urls = array(); // The urls to be returned

		// Start by getting the first level of folder names - each name corresponds to the year of the release so they
		// must be sorted to get the latest ones
		$this->outputTask->log("- Getting the latest tags\n");

		$cmd = $this->cmdSvn . " list {$workingCopy->repoUrl} " .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$output = $this->commandTask->runCmd($cmd, false);

		// Parse the svn text into an array of folder names and sort them descending
		$segments = self::getFolderSegmentsFromString($output);
		rsort($segments);

		$counter = 0;
		foreach($segments as $i => $segment) {
			$cmd = $this->cmdSvn . " list {$workingCopy->repoUrl}/{$segment} " .
				"--username {$workingCopy->account->username} " .
				"--password {$workingCopy->account->password} " .
				"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
			$output = $this->commandTask->runCmd($cmd, false);

			// Parse the second level of relative folder names and sort
			$segments2 = self::getFolderSegmentsFromString($output);
			rsort($segments2);

			foreach($segments2 as $j => $segment2) {
				$urls[] = $repoUrl . '/' . $segment . '/' . $segment2 . '/';
				$counter++;
				if ($counter === $limit) {
					break;
				}
			}
			if ($counter === $limit) {
				break;
			}
		}

		$this->outputTask->log("\n");
		return $urls;
	}

	/**
	 * @param $repoUrl
	 * @param int $qty
	 */
//	public static function getLastCommits($repoUrl, $qty = 5)
//	{
//		$result = array();
//		$cmd = $this->cmdSvn . " log {$repoUrl} --limit {$qty} 2>&1";
//		$output = self::runCmd($cmd, FALSE);
//		$output = trim($output, '-');
//		$output = trim($output, "\n");
//		$output = explode("------------------------------------------------------------------------", $output);
//
//		file_put_contents('temp.txt', implode("\n", $output));
//
//return;
//		print_r($output);
//		foreach($output as $index => $commitData) {
//
//			$commitData = trim($commitData, "\n");
//			//$tmpData = $commitData;
//			$tmpData = explode("\n\n", $commitData, 2);
//			foreach($tmpData as $j => $tmpData2) {
//
//				$tmpData2 = trim($tmpData2, "\n");
//
//				//$tmpData[$j] = trim($tmpData2, "\n");
//
//				//$text = preg_replace("/[\r\n]+/", "\n", $text);
//
//
//				$tmpData[$j] = explode("\n", $tmpData2, 2);
//				print_r($tmpData[$j]);
//			}
////			$tmpData[0] = explode(" | ", $tmpData[0], 4);
////			$result[] = array(
////				'revision' => ltrim($tmpData[0][0], 'r'),
////				'user' => $tmpData[0][1],
////				'timestamp' => $tmpData[0][2],
////				'message' => $tmpData[1]
////			);
//			//echo $commitData;
//		}
//
//		//print_r($result);
//	}

	/**
	 * @param WorkingCopy $workingCopy
	 * @return int
	 * @throws \Primat\Deployer\Exception
	 */
	public function getLastTagRevision(WorkingCopy $workingCopy)
	{
		$revision = 0;
		$tag = NULL;
		$cmd = $this->cmdSvn . " log {$workingCopy->repoUrl} " .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			" --xml -v --stop-on-copy --limit 40 --non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);

		// Parse the XML
		$xmlObj = @simplexml_load_string($xml);
		if (! $xmlObj) {
			throw new Exception("Unable to unmarshall XML:\n" . $xml . "\n");
		}

		/* @var $tagEntry \SimpleXmlElement */
		$deletedPaths = array();
		$tagEntry = NULL;
		foreach($xmlObj->logentry as $index => $entry) { /* @var $entry \SimpleXmlElement */

			if (! $entry->paths || ! $entry->paths->path) {
				continue;
			}
			foreach($entry->paths->path as $j => $path) { /* @var $path \SimpleXmlElement */

				if (empty($path['action'])
					|| !preg_match("/_\d\d\-\d\d\-\d\d$/", (string)$path)
					|| isset($deletedPaths[(string)$path])) {
					continue;
				}

				if ($path['action'] == 'D') {
					$deletedPaths[(string)$path] = true;
					continue;
				}

				if ($path['action'] == 'A' && (int)$path['copyfrom-rev'] > 0) {
					$revision = (int)$path['copyfrom-rev'];
					break;
				}
			}
			if ($revision > 0) {
				break;
			}
		}

//		if ($revision === 0) {
//			throw new Exception('No tags found in subversion');
//		}

		return $revision;
	}

	/**
	 * @param WorkingCopy $workingCopy
	 * @param string $commitText
	 * @return int
	 * @throws \Primat\Deployer\Exception
	 */
	public function getLastDeployRevision(WorkingCopy $workingCopy, $commitText="Release to Production")
	{
		$logLimit = 10;
		$revision = -1;
		$tag = NULL;
		$cmd = $this->cmdSvn . " log {$workingCopy->repoUrl}" .
			" --username {$workingCopy->account->username}" .
			" --password {$workingCopy->account->password}" .
			" --xml --stop-on-copy --limit " . $logLimit .
			" --non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);

		// Parse the XML
		$xmlObj = @simplexml_load_string($xml);

		if (! $xmlObj) {
			throw new Exception("Unable to unmarshall XML:\n" . $xml . "\n");
		}

		/* @var $tagEntry \SimpleXmlElement */
		foreach($xmlObj->logentry as $index => $entry) { /* @var $entry \SimpleXmlElement */
			if (stripos($entry->msg, $commitText) !== FALSE) {
				$attributes = $entry->attributes();
				if (! empty($attributes['revision'])) {
					$revision = (int)$attributes['revision'];
				}
				break;
			}
		}
		return $revision;
	}

	/**
	 * @param SvnBranch $branch
	 * @param int $maxEntries
	 * @return array
	 * @throws TaskException
	 */
	public function getLatestLogEntries(SvnBranch $branch, $maxEntries = 40)
	{
		$account = $branch->repo->account;
		$entries = array();

		$authCmdString = '';
		if (!empty($account)) {
			$authCmdString = " --username {$account->username} --password {$account->password}  --non-interactive";
		}

		$cmd = $this->cmdSvn . " log {$branch->getUrl()}" . $authCmdString .
			 " --limit $maxEntries --xml --trust-server-cert --no-auth-cache 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);

		// Parse the XML
		$xmlObj = @simplexml_load_string($xml);
		if (! $xmlObj) {
			throw new TaskException("Unable to unmarshall XML:\n" . $xml . "\n");
		}

		foreach($xmlObj->logentry as $index => $entry) {
			/* @var $entry \SimpleXmlElement */
			if (isset($entry['revision'])) {
				$entries[(int)$entry['revision']] = new SvnLogEntry($entry);
			}
		}
		return $entries;
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 * @param $fromRevision
	 * @param $toRevision
	 * @return array
	 * @throws \Primat\Deployer\Exception
	 */
	public function getLogEntries(WorkingCopy $workingCopy, $fromRevision, $toRevision)
	{
		$entries = array();
		$cmd = $this->cmdSvn . " log -r{$fromRevision}:{$toRevision} {$workingCopy->repoUrl} " .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			" --xml --non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);

		// Parse the XML
		$xmlObj = @simplexml_load_string($xml);
		if (! $xmlObj) {
			throw new Exception("Unable to unmarshall XML:\n" . $xml . "\n");
		}

		foreach($xmlObj->logentry as $index => $entry) {
			/* @var $entry \SimpleXmlElement */
			if (isset($entry['revision'])) {
				$entries[(int)$entry['revision']] = new SvnLogEntry($entry);
			}
		}
		krsort($entries); // Sort descending
		return $entries;
	}

	/**
	 * Get an object representing information for a repository URL
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 * @param int $revision
	 * @return \Primat\Deployer\Entity\Svn\SvnInfo
	 */
	public function getRepoInfo(WorkingCopy $workingCopy, $revision = 0)
	{
		$revisionParam = ($revision > 0) ? "@{$revision}" : '';
		$cmd = $this->cmdSvn . " info {$workingCopy->repoUrl}{$revisionParam} " .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			"--non-interactive --trust-server-cert --xml --no-auth-cache 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);
		return new SvnInfo($xml);
	}

	/**
	 * Get an object representing information for a repository URL
	 * @param SvnBranch $branch
	 * @param int $revision
	 * @return \Primat\Deployer\Entity\Svn\SvnInfo
	 */
	public function getBranchInfo(SvnBranch $branch, $revision = 0)
	{
		$revisionParam = ($revision > 0) ? "@{$revision}" : '';
		$cmd = $this->cmdSvn . " info " . $branch->getUrl() . $revisionParam . " " .
			"--username {$branch->repo->account->username} " .
			"--password {$branch->repo->account->password} " .
			"--non-interactive --trust-server-cert --xml --no-auth-cache 2>&1";

		try {

		}
		catch (Exception\CommandException $e) {

		}

		$xml = $this->commandTask->runCmd($cmd, false);


		return new SvnInfo($xml);
	}

	/**
	 *
	 */
	public function getRevision(SvnWorkingCopy $workingCopy)
	{
		if (!isset($workingCopy->getInfo()->commitRevision)) {
			//$workingCopy->setInfo($this->getInfo($workingCopy));
		}
		return $workingCopy->getInfo()->commitRevision;
	}

	/**
	 *
	 */
	public function getWorkingCopyRevision($path)
	{
		$cmd = $this->cmdSvnVersion . " $path 2>&1";
		$result = $this->commandTask->runCmd($cmd, false);
		if (stripos($result, 'unversioned') !== false) {
			return '';
		}
		return $result;
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 * @return array
	 * @throws \Primat\Deployer\Exception
	 */
	public function getTags(WorkingCopy $workingCopy)
	{
		$tags = array();
		$cmd = $this->cmdSvn . " log {$workingCopy->repoUrl} " .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			" --xml -v --stop-on-copy --non-interactive --trust-server-cert --no-auth-cache 2>&1";
		$xml = $this->commandTask->runCmd($cmd, false);

		// Parse the XML
		$xmlObj = @simplexml_load_string($xml);
		if (! $xmlObj) {
			throw new Exception("Unable to unmarshall XML:\n" . $xml . "\n");
		}

		foreach($xmlObj->logentry as $index => $entry) {
			/* @var $entry \SimpleXmlElement */
			$tmpObj = new SvnTag($entry->asXML(), $workingCopy->repoBaseUrl);
			if ($tmpObj->copyFromRevision > 0 && ! empty($tmpObj->copyFromPath)) {
				$tags[$tmpObj->copyFromRevision] = $tmpObj;
			}
		}

		// Filter and sort the tags
		krsort($tags);

		return $tags;
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 * @return bool
	 */
	public function isCheckedOut(WorkingCopy $workingCopy)
	{
		if ($workingCopy->info === NULL) {
			try {
				self::loadInfo($workingCopy);
			}
			catch (WorkingCopyException $e) { }
		}
		return $workingCopy->info !== NULL;
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 * @throws \Primat\Deployer\Exception\WorkingCopyException
	 */
	public function loadInfo(WorkingCopy $workingCopy)
	{
		if ($workingCopy->info === NULL) {
			$cmd = $this->cmdSvn . " info {$workingCopy->dir->getPath()} " .
				"--username {$workingCopy->account->username} " .
				"--password {$workingCopy->account->password} " .
				"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
			exec($cmd, $output, $err);
			if ($err) {
				$outputString = implode("\n\t", $output);
				preg_match('/svn: [A-Z](\d\d\d\d\d\d)/', $outputString, $matches);
				$code = 0;
				if (! empty($matches[1])) {
					$code = (int)$matches[1];
				}
				throw new WorkingCopyException("Unable to get working copy information. Command failed with exit status $err.\n\t" . $outputString, $code);
			}
			foreach($output as $i => $line) {
				$line = trim($line);
				if (! empty($line)) {
					if (! is_array($workingCopy->info)) {
						$workingCopy->info = array();
					}
					$parts = explode(': ', $line, 2);
					if (count($parts) === 2) {
						$key = str_replace(' ', '_', strtolower($parts[0]));
						$workingCopy->info[$key] = $parts[1];
					}
				}
			}
		}
	}

	/**
	 * @param SvnWorkingCopy $workingCopy
	 * @throws Exception
	 */
	public function purgeIgnoredAndUnversioned(SvnWorkingCopy $workingCopy)
	{
		$account = $workingCopy->branch->repo->account;
		$authString = '';
		if (!empty($account)) {
			$authString =
				"--username {$account->username} " .
				"--password {$account->password} " .
				"--non-interactive --trust-server-cert --no-auth-cache ";
		}

		// Remove unversioned and ignored files/folders
		$this->outputTask->log("- Removing ignored and unversioned files and folders\n");
		$cmd = $this->cmdSvn . " status --no-ignore " . $authString . $workingCopy->dir->getPath() . " 2>&1";
		exec($cmd, $output, $err);
		if ($err !== 0) {
			throw new Exception("SVN deletion of ignored and unversioned files failed:\n\t".implode("\n\t", $output)."\n");
		}
		foreach($output as $lineNumber => $line) {
			if (preg_match('/^(I|\?)\s{7}/', $line) === 1) {
				$file = mb_substr($line, 8);
				$this->outputTask->log("Deleting {$file}\n");
				if (file_exists($file)){
					if (is_dir($file)) {
						$this->fileSystemTask->deleteFolder($file);
					}
					else {
						unlink($file);
					}
				}
			}
		}
		$this->outputTask->log("\n");
	}

	/**
	 * @param SvnWorkingCopy $workingCopy
	 * @throws Exception
	 */
	public function revert($workingCopy)
	{
		$cmd = $this->cmdSvn . " revert -R {$workingCopy->dir->getPath()} 2>&1";
		$this->outputTask->log("- Reverting working copy\n");
		$this->commandTask->runCmd($cmd);
		$this->outputTask->log("\n");
	}

	/**
	 * @param $path
	 * @param $externalsText
	 * @throws \Primat\Deployer\Exception
	 */
	public function setExternals($path, $externalsText)
	{
		$tmpFilePath = BUILD_TMP_DIR . '/tmp-svn-externals.txt';
		if (file_put_contents($tmpFilePath, $externalsText) === FALSE) {
			throw new Exception('Could not create temporary file for svn externals');
		}
		$cmd = $this->cmdSvn . " propset svn:externals -F {$tmpFilePath} {$path} 2>&1";
		self::runCmd($cmd);
		unlink($tmpFilePath);
	}

	/**
	 * @deprecated
	 * @param $workingCopy
	 * @param int $revisionNbr
	 * @return bool
	 */
	public function setExternalsRevision($workingCopy, $revisionNbr = 0)
	{
		$revisionNbr = (int)$revisionNbr;
		$revisionNbrStr = ($revisionNbr === 0) ? '' : '@'.(string)$revisionNbr;

		$this->outputTask->log("- Setting externals to ");
		if ($revisionNbr === 0) {
			$this->outputTask->log("head revision\n");
		}
		else {
			$this->outputTask->log("revision {$revisionNbr}\n");
		}

		self::$muteOutput = TRUE;
		$externals = self::getExternalsArray($workingCopy);
		self::$muteOutput = FALSE;
		if (count($externals) === 0) {
			$this->outputTask->log("No externals to set!\n\n");
			return FALSE;
		}

		$fileContents = '';
		foreach($externals as $index => $external) {
			$fileContents .= $external['url'] . $revisionNbrStr . ' ' . $external['path'] . "\n";
		}

		$tmpFilePath = $workingCopy->dir->getPath() . '/tmp-svn-externals.txt';
		file_put_contents($tmpFilePath, $fileContents);
		$cmd = $this->cmdSvn . " propset svn:externals -F {$tmpFilePath} {$workingCopy->dir->getPath()} 2>&1";

		self::runCmd($cmd);
		unlink($tmpFilePath);
		$this->outputTask->log("\n");
		return TRUE;
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $workingCopy
	 * @param string $revision
	 * @return bool
	 */
	public function setExternalsToRevision(WorkingCopy $workingCopy, $revision = '')
	{
		$exts = array();

		$this->outputTask->log("- Setting externals to ");
		if ($revision === '') {
			$this->outputTask->log("head revision\n");
		}
		else {
			$this->outputTask->log("revision {$revision}\n");
		}

		// Get the externals when they've not been loaded yet
		if ($workingCopy->externals == NULL) {
			self::getExternals($workingCopy);
		}

		if ($revision !== '') {
			$revision = '@' . $revision;
		}

		// Make a new data structure for the externals which will be easier to use when setting them
		// Specifically, place them in tan array wher the index is the path to the properties file and the keys are
		// the extrnals that are set for that path
		/** @var $external SvnExternal */
		foreach($workingCopy->externals as $index => $external) {
			if (! isset($exts[$external->basePath])) {
				$exts[$external->basePath] = '';
			}
			// Append th external info to a string which cabn easily be added to a SVN property file
			$exts[$external->basePath] .= "{$external->url}{$revision} {$external->relPath}\n";
		}

		foreach($exts as $path => $externals) {
			self::setExternals($path, $externals);
		}
		$this->outputTask->log("\n");
		return TRUE;
	}

	/**
	 * @param \Primat\Deployer\Entity\WorkingCopy $tag
	 * @param string $workingCopyPath
	 * @param $message
	 */
	public function tagRelease(WorkingCopy $tag, $workingCopyPath, $message)
	{
		$message = addslashes($message);
		$this->outputTask->log("-- Tagging working copy\n");
		$command = $this->cmdSvn . " copy {$workingCopyPath} " .
			"{$tag->repoUrl}/" . date('Y-m-d_H-i-s') . "/ " .
			"-m \"{$message}\" " .
			"--username {$tag->account->username} " .
			"--password {$tag->account->password} " .
			"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
		self::runCmd($command);
		$this->outputTask->log("\n");
	}

	/**
	 * @param $workingCopy
	 * @param int $revision
	 * @throws \Primat\Deployer\Exception
	 */
	public function update($workingCopy, $revision = 0)
	{
		$revision = (int)$revision;
		$revisionToUpdate = '';
		if ($revision > 0) {
			$revisionToUpdate = "-r$revision ";
		}
		$this->outputTask->log("- Updating to ");
		if ($revision > 0) {
			$this->outputTask->log("revision {$revision}\n");
		}
		else {
			$this->outputTask->log("head revision\n");
		}
		$cmd = $this->cmdSvn . " update {$workingCopy->dir->getPath()} {$revisionToUpdate}" .
			"--username {$workingCopy->account->username} " .
			"--password {$workingCopy->account->password} " .
			"--config-option config:miscellany:use-commit-times=yes " .
			"--non-interactive --trust-server-cert --no-auth-cache 2>&1";
		self::runCmd($cmd);
		$this->outputTask->log("\n");
	}

	/**
	 * Parse the result of a SVN command to extract a list of folder segments based on a call to svn list
	 * @param $string
	 * @return array
	 */
	protected static function getFolderSegmentsFromString($string)
	{
		$arr = explode("\n", $string);
		$result = array();
		foreach($arr as $i => $str) {
			$str = trim($str);
			if (mb_substr($str, -1, 1) === '/') {
				$result[] = trim($str, '/');
			}
		}
		return $result;
	}

	/**
	 * Parses an xml node coming from svn propget svn:externals
	 * @param $node
	 * @return array
	 */
	protected static function parseExternalsNode($node)
	{
		$externals = array();
		if (empty($node['path']) || empty($node->property)) {
			return $externals;
		}
		$text = trim((string)$node->property);
		$lines = explode("\n", $text);
		foreach($lines as $line) {
			$path = '';
			$revision = '';
			$parts = explode(" ", $line);
			if (! empty($parts[1])) {
				$path = $parts[1];
			}
			$parts = explode("@", $parts[0], 2);
			if (! empty($parts[1])) {
				$revision = (int)$parts[1];
			}
			$url = $parts[0];
			$externals[] = new SvnExternal((string)$node['path'], $path, $url, $revision);
		}
		return $externals;
	}

	/**
	 * @param SvnBranch $branch
	 * @return string
	 */
	protected function getCacheFolder(SvnBranch $branch)
	{
		return $this->cacheFolder . '/' . $branch->getId();
	}
}
