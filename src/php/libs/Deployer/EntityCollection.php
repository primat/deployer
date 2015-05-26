<?php namespace Primat\Deployer;
/**
 * Project: Deployer
 * Date: 19/05/15
 */

use Primat\Deployer\Entity\Account;

/**
 * Class Entities
 * @package Primat\Deployer
 */
class EntityCollection
{
	protected $entityCache;
	protected $entityArray;

	/**
	 * Constructor
	 * @param $entities
	 */
	public function __construct($entities)
	{
		$this->entityArray = $entities;
		$this->entityCache = [
			'account' => [],
			'host' => [],
			'database' => [],
			'dir' => [],
			'workingCopy' => [],
			'email' => [],
			'smtpConnector' => []
		];
	}

	/**
	 * @param $key string The key of the entity to retrieve
	 * @return \Primat\Deployer\Entity\Account
	 * @throws exception
	 */
	public function getAccount($key)
	{
		// Check the cache
		if (isset($this->entityCache['account'][$key])) {
			return $this->entityCache['account'][$key];
		}

		// Get the entity parameters
		$parameters = [];
		if (isset($this->entityArray['account'][$key])) {
			$parameters = $this->entityArray['account'][$key];
		}
		else {
			throw new exception("Entity account.$key does not exist");
		}

		// Indicate which class to create
		$class = '\Primat\Deployer\Entity\Account';

		// PHP version >= 5.6
		//$entity =  new $class(...$parameters);

		// PHP version < 5.6
		$reflection = new \ReflectionClass($class);
		$entity = $reflection->newInstanceArgs($parameters);

		$this->entityCache['account'][$key] = $entity;
		return $entity;
	}

	/**
	 * @param $key string The key of the entity to retrieve
	 * @return \Primat\Deployer\Entity\Dir
	 * @throws exception
	 */
	public function getDir($key)
	{
		// Check the cache
		if (isset($this->entityCache['dir'][$key])) {
			return $this->entityCache['dir'][$key];
		}

		// Get the entity parameters
		$parameters = [];
		if (isset($this->entityArray['dir'][$key])) {
			$parameters = $this->entityArray['dir'][$key];
		}
		else {
			throw new exception("Entity dir.$key does not exist");
		}

		// Convert the second argument to an entity object
		if (!empty($parameters[1])) {
			$arr = explode(".", $parameters[1]);
			$parameters[1] = $this->getHost(end($arr));
		}

		// Indicate which class to create
		$class = '\Primat\Deployer\Entity\Dir';

		// PHP version >= 5.6
		// $entity =  new $class(...$parameters);

		// PHP version < 5.6
		$reflection = new \ReflectionClass($class);
		$entity = $reflection->newInstanceArgs($parameters);

		$this->entityCache['dir'][$key] = $entity;
		return $entity;
	}

	/**
	 * @param $key string The key of the entity to retrieve
	 * @return \Primat\Deployer\Entity\Host
	 * @throws exception
	 */
	public function getHost($key)
	{
		// Check the cache
		if (isset($this->entityCache['host'][$key])) {
			return $this->entityCache['host'][$key];
		}

		// Get the entity parameters
		$parameters = [];
		if (isset($this->entityArray['host'][$key])) {
			$parameters = $this->entityArray['host'][$key];
		}
		else {
			throw new exception("Entity host.$key does not exist");
		}

		// Convert the second argument to an entity object
		if (!empty($parameters[1])) {
			$arr = explode(".", $parameters[1]);
			$parameters[1] = $this->getAccount(end($arr));
		}

		// Indicate which class to create
		$class = '\Primat\Deployer\Entity\Host';

		// PHP version >= 5.6
		// $entity =  new $class(...$parameters);

		// PHP version < 5.6
		$reflection = new \ReflectionClass($class);
		$entity = $reflection->newInstanceArgs($parameters);

		$this->entityCache['host'][$key] = $entity;
		return $entity;
	}

	/**
	 * @param $key string The key of the entity to retrieve
	 * @return \Primat\Deployer\Entity\Host
	 * @throws exception
	 */
	public function getWorkingCopy($key)
	{
		// Check the cache
		if (isset($this->entityCache['workingCopy'][$key])) {
			return $this->entityCache['workingCopy'][$key];
		}

		// Get the entity parameters
		$parameters = [];
		if (isset($this->entityArray['workingCopy'][$key])) {
			$parameters = $this->entityArray['workingCopy'][$key];
		}
		else {
			throw new exception("Entity workingCopy.$key does not exist");
		}

		// Convert the third argument to an entity object
		if (!empty($parameters[2])) {
			$arr = explode(".", $parameters[2]);
			$parameters[2] = $this->getAccount(end($arr));
		}

		// Indicate which class to create
		$class = '\Primat\Deployer\Entity\WorkingCopy';

		// PHP version >= 5.6
		// $entity =  new $class(...$parameters);

		// PHP version < 5.6
		$reflection = new \ReflectionClass($class);
		$entity = $reflection->newInstanceArgs($parameters);

		$this->entityCache['workingCopy'][$key] = $entity;
		return $entity;
	}

	/**
	 * @param $key string The key of the entity to retrieve
	 * @return \Primat\Deployer\Entity\Host
	 * @throws exception
	 */
	public function getDatabase($key)
	{
		// Check the cache
		if (isset($this->entityCache['database'][$key])) {
			return $this->entityCache['database'][$key];
		}

		// Get the entity parameters
		$parameters = [];
		if (isset($this->entityArray['database'][$key])) {
			$parameters = $this->entityArray['database'][$key];
		}
		else {
			throw new exception("Entity database.$key does not exist");
		}

		// Convert the second argument to an entity object
		if (!empty($parameters[1])) {
			$arr = explode(".", $parameters[1]);
			$parameters[1] = $this->getAccount(end($arr));
		}

		// Convert the third argument to an entity object
		if (!empty($parameters[2])) {
			$arr = explode(".", $parameters[2]);
			$parameters[2] = $this->getHost(end($arr));
		}

		// Indicate which class to create
		$class = '\Primat\Deployer\Entity\Database';

		// PHP version >= 5.6
		// $entity =  new $class(...$parameters);

		// PHP version < 5.6
		$reflection = new \ReflectionClass($class);
		$entity = $reflection->newInstanceArgs($parameters);

		$this->entityCache['database'][$key] = $entity;
		return $entity;
	}

	/**
	 * @param $key string The key of the entity to retrieve
	 * @return \Primat\Deployer\Entity\Host
	 * @throws exception
	 */
	public function getEmail($key)
	{
		// Check the cache
		if (isset($this->entityCache['email'][$key])) {
			return $this->entityCache['email'][$key];
		}

		// Get the entity parameters
		$parameters = [];
		if (!empty($this->entityArray['email'][$key])) {
			$parameters = $this->entityArray['email'][$key];
		}
		else {
			throw new exception("Entity email.$key does not exist");
		}

		// Indicate which class to create
		$class = '\Primat\Deployer\Entity\Email';

		// PHP version >= 5.6
		// $entity =  new $class(...$parameters);

		// PHP version < 5.6
		$reflection = new \ReflectionClass($class);
		$entity = $reflection->newInstanceArgs($parameters);

		$this->entityCache['email'][$key] = $entity;
		return $entity;
	}

	/**
	 * @param $key string The key of the entity to retrieve
	 * @return \Primat\Deployer\Entity\Repository
	 * @throws exception
	 */
	public function getRepository($key)
	{
		// Check the cache
		if (isset($this->entityCache['repository'][$key])) {
			return $this->entityCache['repository'][$key];
		}

		// Get the entity parameters
		$parameters = [];
		if (isset($this->entityArray['repository'][$key])) {
			$parameters = $this->entityArray['repository'][$key];
		}
		else {
			throw new exception("Repository repository.$key does not exist");
		}

		// Convert the second argument to an entity object
		if (!empty($parameters[1])) {
			$arr = explode(".", $parameters[1]);
			$parameters[1] = $this->getAccount(end($arr));
		}

		// Indicate which class to create
		$class = '\Primat\Deployer\Entity\Repository';

		// PHP version >= 5.6
		// $entity =  new $class(...$parameters);

		// PHP version < 5.6
		$reflection = new \ReflectionClass($class);
		$entity = $reflection->newInstanceArgs($parameters);

		$this->entityCache['repository'][$key] = $entity;
		return $entity;
	}

	/**
	 * @param $key string The key of the entity to retrieve
	 * @return \Primat\Deployer\Entity\Repository
	 * @throws exception
	 */
	public function getRepositoryBranch($key)
	{
		// Check the cache
		if (isset($this->entityCache['repositoryBranch'][$key])) {
			return $this->entityCache['repositoryBranch'][$key];
		}

		// Get the entity parameters
		$parameters = [];
		if (isset($this->entityArray['repositoryBranch'][$key])) {
			$parameters = $this->entityArray['repositoryBranch'][$key];
		}
		else {
			throw new exception("Repository branch repositoryBranch.$key does not exist");
		}

		// Convert the second argument to an entity object
		if (!empty($parameters[1])) {
			$arr = explode(".", $parameters[1]);
			$parameters[1] = $this->getRepository(end($arr));
		}

		// Indicate which class to create
		$class = '\Primat\Deployer\Entity\RepositoryBranch';

		// PHP version >= 5.6
		// $entity =  new $class(...$parameters);

		// PHP version < 5.6
		$reflection = new \ReflectionClass($class);
		$entity = $reflection->newInstanceArgs($parameters);

		$this->entityCache['repositoryBranch'][$key] = $entity;
		return $entity;
	}

	/**
	 * @param $key string The key of the entity to retrieve
	 * @return \Primat\Deployer\Entity\Repository
	 * @throws exception
	 */
	public function getSmtpConnector($key)
	{
		// Check the cache
		if (isset($this->entityCache['smtpConnector'][$key])) {
			return $this->entityCache['smtpConnector'][$key];
		}

		// Get the entity parameters
		$parameters = [];
		if (isset($this->entityArray['smtpConnector'][$key])) {
			$parameters = $this->entityArray['smtpConnector'][$key];
		}
		else {
			throw new exception("SmtpConnector smtpConnector.$key does not exist");
		}

		// Convert the second argument to an entity object
		if (!empty($parameters[0])) {
			$arr = explode(".", $parameters[0]);
			$parameters[0] = $this->getHost(end($arr));
		}

		// Indicate which class to create
		$class = '\Primat\Deployer\Entity\Email\SmtpConnector';

		// PHP version >= 5.6
		// $entity =  new $class(...$parameters);

		// PHP version < 5.6
		$reflection = new \ReflectionClass($class);
		$entity = $reflection->newInstanceArgs($parameters);

		$this->entityCache['smtpConnector'][$key] = $entity;
		return $entity;
	}
}
