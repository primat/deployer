<?php namespace Primat\Deployer;
/**
 * Project: Deployer
 * Date: 11/05/15
 */

use ReflectionObject;

/**
 * Class Project
 * @package Primat\Deployer\Utils
 */
class Project
{
	/**  @var array $context Script configurations */
	protected $contexts = [];
	/**  @var \Primat\Deployer\EntityCollection $entities */
	protected $entities = null;
	/**  @var string $folder */
	protected $folder = '';
	/**  @var string $name */
	protected $name = '';
	/**  @var callable[] $scripts */
	protected $scripts = [];
	/**  @var string[] $settings Project specific settings */
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
	 * @param $key
	 * @param bool $getCallable
	 * @return callable|Script
	 */
	public function getScript($key, $getCallable = false)
	{
		if (!isset($this->scripts[$key])) {
			return null;
		}

		if ($getCallable) {
			return $this->scripts[$key];
		}

		$callable = $this->scripts[$key];
		return $callable();
	}

	/**
	 * @param $key
	 * @return callable|null
	 */
	public function getScriptCallback($key)
	{
		$script = $this->getScript($key);
		if (!empty($script)) {
			return null;
		}
		return $script->getScriptClosure();
	}

	/**
	 * @param $key
	 * @return null|ScriptContext
	 */
	public function getScriptContext($key)
	{
		if (!isset($this->contexts[$key])) {
			trigger_error("Script (context) {$key} does not exist in this project");
			return null;
		}

		$script = $this->getScript($key);

		$entities = [];
		if (isset($this->contexts[$key]['entities']) && is_array($this->contexts[$key]['entities'])) {
			foreach($this->contexts[$key]['entities'] as $entityId => $args) {
				$entities[$entityId] = $this->entities->getEntity($args[0], $args[1]);
			}
		}

		$embeddedScripts = [];
		if (isset($this->contexts[$key]['embeddedScripts']) && is_array($this->contexts[$key]['embeddedScripts'])) {
			foreach($this->contexts[$key]['embeddedScripts'] as $scriptId) {
				$embeddedScripts[] = $this->getScriptCallback($scriptId);
			}
		}

		$settings = [];
		if (isset($this->contexts[$key]['settings']) && is_array($this->contexts[$key]['settings'])) {
			$settings = $this->contexts[$key]['settings'];
		}
		$settings += $script->getSettings() + $this->settings;

		return new ScriptContext($script, $entities, $embeddedScripts, $settings);
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
