<?php namespace Primat\Deployer\Model;
/**
 * Created by PhpStorm
 * Date: 6/2/2015
 */

use Primat\Deployer\Exception\ScriptConfigException;
use Primat\Deployer\ScriptConfig;

/**
 * Class ContextModel
 * @package Primat\Deployer\Model
 */
class FileScriptConfigModel implements IScriptConfigModel
{
	const MAX_RECURSION = 1;

	/** @var string $baseFolder */
	protected $baseFolder = '';
	/** @var IEntityModel $baseFolder */
	protected $entityModel = '';

	/**
	 * Constructor
	 * @param $baseFolder
	 * @param IEntityModel $entityModel
	 */
	public function __construct($baseFolder, IEntityModel $entityModel)
	{
		$this->baseFolder = rtrim($baseFolder, '/\\');
		$this->entityModel = $entityModel;
	}

	/**
	 * Gets the script configuration data, as an object
	 * @param string $configId
	 * @return ScriptConfig
	 * @throws ScriptConfigException
	 */
	public function getScriptConfig($configId)
	{
		$data = $this->getConfigData($configId);
		$title = '';
		$entities = [];
		$scriptId = $configId;
		$settings = [];

		if (isset($data['title'])) {
			$title = $data['title'];
		}

		if (isset($data['entities'])) {
			$entities = $this->createObjectEntities($data['entities']);
		}

		if (isset($data['script'])) {
			$scriptId = $data['script'];
		}

		if (isset($data['settings'])) {
			$settings = $data['settings'];
		}

		return new ScriptConfig($scriptId, $title, $entities, $settings);
	}

	/**
	 * @param array $arrayEntities
	 * @param int $recursionLevel
	 * @return array
	 */
	protected function createObjectEntities(array $arrayEntities, $recursionLevel = 0)
	{
		$entities = [];

		// Cycle through each entity
		foreach ($arrayEntities as $id => $list) {

			// If the array value of the first element of list is also an array, then we are dealing with a collection
			// of entities rather than a single one, so we have to cycle through the
			if (is_array($list[0]) && $recursionLevel < self::MAX_RECURSION) {
				$entities[$id] = $this->createObjectEntities($list, $recursionLevel + 1);
			}
			else {
				$entities[$id] = $this->entityModel->getEntity($arrayEntities[$id][0], $arrayEntities[$id][1]);
			}

		}
		return $entities;
	}

	/**
	 * Gets the script configuration data as an array of text elements
	 * @param string $key
	 * @return array
	 * @throws ScriptConfigException
	 */
	protected function getConfigData($key)
	{
		$configFile = $this->baseFolder . '/config.php';
		$customConfigFile = $this->baseFolder . '/config-local.php';
		if (is_file($configFile)) {
			$data = include $configFile;
		}
		else {
			throw new ScriptConfigException("Unable to locate script configuration file");
		}

		if (isset($data[$key])) {
			$data = $data[$key];
		}
		else {
			throw new ScriptConfigException("Invalid script config ID: $key");
		}

		if (!is_array($data)) {
			throw new ScriptConfigException("The script configuration file does not return an array");
		}

		if (is_file($customConfigFile)) {
			/* @var callable $callback */
			$callback = include $customConfigFile;
			if (!is_callable($callback)) {
				throw new ScriptConfigException("The custom script configuration file must return a callable");
			}
			$data = (array) $callback($data);
		}

		if (!is_array($data)) {
			throw new ScriptConfigException("The custom script configuration file does not return an array");
		}

		return $data;
	}
}
