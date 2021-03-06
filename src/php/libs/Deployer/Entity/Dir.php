<?php namespace Primat\Deployer\Entity;

use Primat\Deployer\Entity;
use Primat\Deployer\Entity\Host;
use Primat\Deployer\EntityInterface\IDirectoryFile;

/**
 * Class Dir
 * @package Primat\Deployer\Entity
 */
class Dir extends Entity implements IDirectoryFile
{
	/** @var Host $host The host object - Null if local */
	public $host;
	/** @var string $name The file name */
	public $name = '';
	/** @var string $path The full path to the resource */
	public $path = ''; // Should always contains a trailing slash, for directory paths
	/** @var bool $isWinDir flag to indicate if the path is a Windows path */
	public $isWinDir = '';
	/** @var string $separator The separator used when evaluating paths */
	protected $separator = '/';


	/**
	 * @param $dirName
	 * @param Host $host
	 */
	public function __construct($dirName, Host $host = null)
	{
		// Establish if the path is a windows path or not
		$this->isWinDir = (mb_substr($dirName, 1, 1) === ':');
		$this->name = $dirName;

		// Try to clean up the path a bit
		if ($this->isWinDir) {
			$this->path = str_replace('/', '\\', $this->name);
			$this->path = preg_replace("/[\\\]+/", '\\', $this->path);
			$this->path = trim($this->path, '\\') . '\\';
			$this->separator = '\\';
		}
		else {
			$this->path = str_replace('\\', '/', $this->name);
			$this->path = preg_replace('/[\/]+/', '/', $this->path);
			$this->path = rtrim($this->path, '/') . '/';
			$this->separator = '/';
		}

		$this->host = $host;
	}

	/**
	 * Get the host where this resource is located
	 * @return Host|null
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Get the full directory path, including the trailing slash
	 * @return string
	 */
	public function getLocation()
	{
		return $this->getPath() . ((empty($this->host)) ? '' : ":" . $this->host->getHostname());
	}

	/**
	 * Get the full directory path, including the trailing slash
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Get the directory separator for this directory name
	 * @return string
	 */
	public function getSeparator()
	{
		return $this->separator;
	}

	/**
	 * @return bool
	 */
	public function isRemote()
	{
		return ! empty($this->host) && $this->host instanceof Host && $this->host->hostname !== 'localhost';
	}
}
