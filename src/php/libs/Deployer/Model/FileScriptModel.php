<?php namespace Primat\Deployer\Model;
/**
 * Created by PhpStorm
 * Date: 6/1/2015
 * Time: 9:33 PM
 */

/**
 * Class ScriptModel
 * @package Primat\Deployer\Model
 */
class FileScriptModel implements IScriptModel
{
	/** @var string $baseFolder */
	protected $baseFolder = '';

	/**
	 * Constructor
	 * @param string $baseFolder
	 */
	public function __construct($baseFolder)
	{
		$this->baseFolder = rtrim($baseFolder, '/\\') . '/scripts/';
	}

	/**
	 * Get a script (as a callback)
	 * @param $id
	 * @return callable
	 * @throws \Exception
	 */
	public function getScript($id)
	{
		$filename = $this->baseFolder . "$id.php";
		if ($this->scriptExists($id)) {
			$script = include $filename;
			if (is_callable($script)) {
				return $script;
			}
			else {
				throw new \Exception("Script ID '$id' is not callable");
			}
		}
		throw new \Exception("Script ID '$id' does not exist");
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function scriptExists($id)
	{
		return file_exists($this->baseFolder . "$id.php");
	}
}
