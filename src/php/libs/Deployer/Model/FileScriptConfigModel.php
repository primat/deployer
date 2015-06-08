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
	 * @param string $key
	 * @return ScriptConfig
	 * @throws ScriptConfigException
	 */
	public function getScriptConfig($key)
	{
		$data = $this->getConfigData($key);

		$title = '';
		if (isset($data['title'])) {
			$title = $data['title'];
		}

		$entities = [];

		if (isset($data['entities'])) {

			foreach($data['entities'] as $id => $list) {

				if (is_array($data['entities'][$id][0])) {

					if (!isset($entities[$id]) || !is_array($entities[$id])) {
						$entities[$id] = [];
					}

					foreach($data['entities'][$id] as $subIndex => $subEntity) {
						//$type = $data['entityCollection'][$id][$subIndex][0];
						//$typeId = $data['entityCollection'][$id][$subIndex][1];
						//$entities[$id][] = $this->entityModel->getEntity($type, $typeId);
						$entities[$id][] = call_user_func_array([$this->entityModel, 'getEntity'], $subEntity);
					}
				}
				else {
					$type = $data['entities'][$id][0];
					$typeId = $data['entities'][$id][1];
					$entities[$id] = $this->entityModel->getEntity($type, $typeId);
				}

			}
		}

		$scriptId = $key;
		if (isset($data['script'])) {
			$scriptId = $data['script'];
		}

		$settings = [];
		if (isset($data['settings'])) {
			$settings = $data['settings'];
		}

		return new ScriptConfig($scriptId, $title, $entities, $settings);
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
