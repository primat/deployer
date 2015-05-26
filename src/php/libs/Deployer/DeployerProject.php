<?php namespace Primat\Deployer;
/**
 * Project: Deployer
 * Date: 11/05/15
 */

use ReflectionObject;

/**
 * Class DeployerProject
 * @package Primat\Deployer\Utils
 */
class DeployerProject
{
	/**  @var \Primat\Deployer\EntityCollection $entities */
	protected $entities = null;
	/**  @var string $folder */
	protected $folder = '';
	/**  @var string $name */
	protected $name = '';
	/**  @var string[] $scriptEntities */
	protected $scriptEntities = [];
	/**  @var string[] $scriptSettings */
	protected $scriptSettings = [];
	/**  @var string[] $settings */
	protected $settings = [];

	/**
	 * Get the subclass's folder. It a folder has not been explicitly set, then generate one by default
	 * @return string
	 */
	public function getFolder()
	{
		if (empty($this->folder)) {
			$reflectionClass = new ReflectionObject($this);
			$fileName = $reflectionClass->getFileName();
			$this->folder = dirname($fileName);
		}
		return $this->folder;
	}

	/**
	 * Get a human readable name for the project
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param $scriptName
	 * @return string[]
	 */
	public function getScriptEntities($scriptName)
	{
		if (isset($this->scriptEntities[$scriptName])) {
			$cb = $this->scriptEntities[$scriptName];
			return $cb();
		}
		return [];
	}

	/**
	 * @param $scriptName
	 * @return string[]
	 */
	public function getScriptSettings($scriptName)
	{
		if (isset($this->scriptSettings[$scriptName])) {
			return ($this->scriptSettings[$scriptName]);
		}
		return [];
	}

	/**
	 * Initial app and task settings. These override the project settings on a "per script" basis
	 * @return string[]
	 */
	public function getSettings()
	{
		return $this->settings;
	}
}
