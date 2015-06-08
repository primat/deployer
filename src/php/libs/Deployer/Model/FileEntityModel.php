<?php namespace Primat\Deployer\Model;
/**
 * Created by PhpStorm
 * Date: 6/1/2015
 */

use Primat\Deployer\Entity;
use Primat\Deployer\Exception\EntityException;

/**
 * Class EntityModel
 * @package Primat\Deployer\Model
 */
class FileEntityModel implements IEntityModel
{
	const NAMESPACE_PREFIX = '\\Primat\\Deployer\\Entity\\';

	/** @var string $baseFolder */
	protected $baseFolder = '';
	/** @var array $cache */
	protected $cache = [];
	/** @var string[] $data */
	protected $data = [];

	/**
	 * Constructor
	 * @param $baseFolder
	 */
	public function __construct($baseFolder)
	{
		$this->baseFolder = rtrim($baseFolder, '/\\');
		$this->data = $this->getEntityData();
	}

	/**
	 * @param $type
	 * @param $key
	 * @return null|Entity
	 * @throws EntityException
	 */
	public function getEntity($type, $key)
	{
		list($className, $type) = $this->parseClassName($type);

		// Check the cache first
		if (isset($this->cache[$type][$key])) {
			return $this->cache[$type][$key];
		}

		// Get the entity parameters
		if (isset($this->data[$type][$key])) {
			$args = $this->data[$type][$key];
		}
		else {
			throw new EntityException("Entity {$type}.{$key} does not exist");
		}

		$reflection = new \ReflectionClass($className);
		$constructor = $reflection->getMethod('__construct');
		$params = $constructor->getParameters();

		foreach ($params as $key => $param) {

			// We don't need to do anything once all args have been cycled through
			if (! isset($args[$key])) {
				break;
			}

			// Get the type hinted class. If one isn't set, we can skip this parameter/argument
			$typeHintClass = $param->getClass();
			//$typeHintClassName = '';
			if (empty($typeHintClass)) {
				continue;
			}

			// With a class type hint, we can now determine if we need to get another entity or set a default value
			$typeHintClassName = $typeHintClass->name;

			if (strlen($args[$key])) {
				// Get the entity corresponding to the type hint class with the provided key
				$args[$key] = $this->getEntity($typeHintClassName, $args[$key]);
			}
			else if ($param->isDefaultValueAvailable()) {
				$args[$key] = $param->getDefaultValue();
			}
		}

		// Prep the cache, if necessary, and add the item to it
		if (!isset($this->cache[$type])) {
			$this->cache[$type]	= [];
		}
		$this->cache[$type][$key] = $reflection->newInstanceArgs($args);

		return $this->cache[$type][$key];
	}

	/**
	 *
	 * @return array
	 * @throws EntityException
	 */
	protected function getEntityData()
	{
		$entitiesFile = $this->baseFolder . '/entities.php';
		$customEntitiesFile = $this->baseFolder . '/entities-local.php';
		if (is_file($entitiesFile)) {
			$data = include $entitiesFile;
		}
		else {
			throw new EntityException("Unable to locate entities file");
		}

		if (!is_array($data)) {
			throw new EntityException("The entities file does not return an array");
		}

		if (is_file($customEntitiesFile)) {
			/* @var callable $callback */
			$callback = include $customEntitiesFile;
			if (!is_callable($callback)) {
				throw new EntityException("The custom local entities file must return a callable");
			}
			$data = (array) $callback($data);
		}

		if (!is_array($data)) {
			throw new EntityException("The custom local entities file does not return an array");
		}

		return $data;
	}

	/**
	 * Parse a class name into two parts: a first part which consists of the application specific segments, and 2) the
	 * identifier part which is a shorter and unique ID for the entity
	 * @param $className
	 * @return array
	 */
	protected function parseClassName($className)
	{
		if ($className[0] === '\\') {
			$type = str_replace(self::NAMESPACE_PREFIX, '', $className);
		}
		else if (strpos($className, ltrim(self::NAMESPACE_PREFIX, '\\')) === 0) {
			$type = str_replace(ltrim(self::NAMESPACE_PREFIX, '\\'), '', $className);
		}
		else {
			$type = $className;
			$className = self::NAMESPACE_PREFIX . $className;
		}

		return [$className, $type];
	}
}