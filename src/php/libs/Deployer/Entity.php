<?php namespace Primat\Deployer;

/**
 *
 */
class Entity
{
	/** @var \Primat\Deployer\Entity[][] $pool Stores all initialized entities */
	protected static $pool = array();

	/**
	 * Constructor - Stores all entities in the $pool, separated by type
	 */
	public function __construct()
	{
		$class = get_class($this);
		self::$pool[$class][] = $this;
	}

	/**
	 * Destructor - when an entity is destroyed, remove it from the $pool
	 */
	public function __destruct()
	{
		$class = get_class($this);
		if (isset(self::$pool[$class])) {
			$index = array_search($this, self::$pool[$class]);
			if ($index !== FALSE) {
				unset(self::$pool[$class][$index]);
			}
		}
	}

	/**
	 * @param string $className The name of the class of objects to return
	 * @param bool $getSubClasses
	 * @return Entity[] The list of objects corresponding to the provided class name
	 */
	public static function getList($className, $getSubClasses = false)
	{
		$result = array();
		$className = __CLASS__ . '\\' . $className;
		if (isset(self::$pool[$className])) {
			$result = self::$pool[$className];
		}
		else {
			foreach(self::$pool as $entityType => $entities) {
				if (stripos($entityType, $className) === 0) {
					$result = array_merge($result, $entities);
				}
			}
		}
		return $result;
	}
}
