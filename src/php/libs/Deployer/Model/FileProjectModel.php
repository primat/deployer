<?php  namespace Primat\Deployer\Model; 
/**
 * Created by PhpStorm
 * Date: 08/06/15
 */

use Primat\Deployer\Exception\ModelException;

/**
 * Class FileProjectModel
 * @package Primat\Deployer\Model
 */
class FileProjectModel implements IProjectModel
{
	const FOLDER_PERMISSIONS = 0775;

	/** @var string $projectFolder */
	protected $projectFolder;
	/** @var string $projectId */
	protected $projectId;
	/** @var string $dbDumpFolder */
	protected $dbDumpFolder;
	/** @var string $cacheFolder */
	protected $cacheFolder;
	/** @var string $logsFolder */
	protected $logsFolder;
	/** @var string $tempFolder */
	protected $tempFolder;

	/**
	 * Constructor
	 * @param string $appPath
	 * @param string $projectId
	 * @throws ModelException
	 */
	public function __construct($appPath, $projectId)
	{
		$this->projectId = $projectId;

		$this->projectFolder = realpath($appPath . '/' . $projectId);
		if (!$this->projectFolder) {
			throw new ModelException("Path " . $appPath . '/' . $projectId . " is not a valid project folder");
		}

		$this->cacheFolder = $this->projectFolder . '/cache';
		$this->dbDumpFolder = $this->projectFolder . '/db';
		$this->logsFolder = $this->projectFolder . '/logs';
		$this->scriptsFolder = $this->projectFolder . '/scripts';
		$this->tempFolder = sys_get_temp_dir ();
		$this->viewsFolder = $this->projectFolder . '/views';
	}

	/**
	 * @param string $folder
	 * @throws ModelException
	 */
	protected function createFolder($folder)
	{
		if (!is_dir($folder) && !mkdir($folder, self::FOLDER_PERMISSIONS, true)) {
			throw new ModelException("Unable to create project folder " . $folder);
		}
	}

	/**
	 * @return string
	 */
	public function getFolder()
	{
		$this->createFolder($this->projectFolder);
		return $this->projectFolder;
	}

	/**
	 * @return string
	 */
	public function getDbDumpFolder()
	{
		$this->createFolder($this->dbDumpFolder);
		return $this->dbDumpFolder;
	}

	/**
	 * @return string
	 */
	public function getCacheFolder()
	{
		$this->createFolder($this->cacheFolder);
		return $this->cacheFolder;
	}

	/**
	 * @return string
	 */
	public function getLogsFolder()
	{
		$this->createFolder($this->logsFolder);
		return $this->logsFolder;
	}

	/**
	 * @return string
	 */
	public function getTempFolder()
	{
		return $this->tempFolder;
	}

	/**
	 * @return string
	 */
	public function getViewsFolder()
	{
		return $this->viewsFolder;
	}
}
