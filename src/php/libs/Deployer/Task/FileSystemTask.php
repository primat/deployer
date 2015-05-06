<?php namespace Deployer\Task;

use Deployer\Exception;

/**
 *
 */
class FileSystemTask extends \Deployer\Task
{
	/**
	 * Recursively iterate through a directory and delete all files and folders
	 * @param $dir
	 * @param bool $deleteRootDir
	 */
	public static function rrmdir($dir, $deleteRootDir = TRUE)
	{
		$dir = rtrim($dir, '/\\');
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") {
						self::rrmdir($dir."/".$object);
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

	/**
	 * @param $path
	 * @return bool
	 * @throws \Deployer\Exception
	 */
	public static function mkdir($path)
    {
        if (! is_dir($path)) {
            mkdir($path);
        }
        if (! is_dir($path)) {
			throw new Exception('Unable to create directory ' . $path);
        }
        return TRUE;
    }
}
