<?php namespace Deployer\Build\Project\MyAccount;

use \Deployer\Build\Task;
use \Deployer\Build\Entity\Dir;
use \Deployer\Build\Task\SshTask;
use \Deployer\Build\Task\SftpTask;

/**
 * A class for My Account specific tasks (aka methods)
 */
class MyAccountTask extends Task
{
	/**
	 */
	public static function getRemoteRevision(Dir $dir)
	{
		$sftpHandle = SftpTask::connect($dir->host);
		$manifest = $sftpHandle->get($dir->path . '/manifest');
		$manifest = explode('-', $manifest, 2);
		if(isset($manifest[1]) && ctype_digit($manifest[1])) {
			return $manifest[1];
		}
		return 0; // Error case - Unable to determine revision number
	}
}