<?php  namespace Primat\Deployer;
/**
 * Project: Deployer
 * Date: 25/05/15
 */

use Primat\Deployer\TaskCollection;

/**
 * Class ExecutionEnvironment
 * @package Primat\Deployer
 */
class ExecutionEnvironment
{
	/** @var string[] $context */
	protected $context = [];
	/** @var DeployerProject $project */
	protected $project;
	/** @var callable $script */
	protected $script;
	/** @var TaskCollection $tasks */
	protected $tasks;

	/**
	 * @param TaskCollection $tasks
	 * @param array $entities
	 * @param array $context
	 * @param callable $script
	 * @param array $args
	 */
	public function __construct(TaskCollection $tasks, array $entities, callable $script, array $context, array $args = [])
	{
		$this->tasks = $tasks;
		foreach ($entities as $key => $value){
			$this->{$key} = $value;
		}
		$this->context = $context;
		$this->script = $script;
		$this->args = $args;
	}

	/**
	 * @return mixed
	 */
	public function runScript()
	{
		$callback = $this->script;
		$callback = $callback->bindTo($this, $this);
		return call_user_func_array($callback, $this->args);
	}
}
