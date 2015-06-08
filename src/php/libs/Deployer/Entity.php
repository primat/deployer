<?php  namespace Primat\Deployer; 
/**
 * Project: Deployer
 * Date: 01/06/15
 */

/**
 * Class Entity
 * @package Primat\Deployer
 */
abstract class Entity
{
	/**
	 * Gets the class name (e.g. only the last namespace segments)
	 * @return string
	 */
	public function getType()
	{
		$className = get_class($this);
		$classParts = explode('\\', $className);
		return end($classParts);
	}
}
