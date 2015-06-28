<?php namespace Primat\Deployer\Task;

use Primat\Deployer\Exception;
use Primat\Deployer\Exception\TaskException;

/**
 * Class FileSystemTask
 * @package Primat\Deployer\Task
 */
class FileSystemTask
{
	/**
	 * @param string $name
	 * @param string $folder
	 * @throws TaskException
	 */
	public function createFolder($folder, $name = '')
	{
		if (is_dir($folder)) {
			return;
		}
		if (! mkdir($folder, 0777, true)) {
			throw new TaskException("Error: Unable to create $name folder " . $folder);
		}
	}

	/**
	 *
	 * @param string $path
	 * @param bool $deleteRootDir
	 */
	public function deleteFile($path, $deleteRootDir = true)
	{
		if (is_dir($path)) {
			$this->deleteFolder($path, $deleteRootDir);
			return;
		}
		unlink($path);
	}

	/**
	 * Recursively iterate through a directory and delete all files and folders
	 * @param $dir
	 * @param bool $deleteRootDir
	 */
	public function deleteFolder($dir, $deleteRootDir = true)
	{
		$dir = rtrim($dir, '/\\');
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") {
						$this->deleteFolder($dir."/".$object);
					}
					else {
						unlink($dir."/".$object);
					}
				}
			}
			reset($objects);
			if ($deleteRootDir) {
				rmdir($dir);
			}
		}
	}
}
