<?php  namespace Primat\Deployer;
/**
 * Project: Deployer
 * Date: 25/05/15
 */

use Primat\Deployer\Model\IScriptModel;
use Primat\Deployer\Model\TaskModel;

/**
 * Class ExecutionEnvironment
 * @package Primat\Deployer
 */
class ExecutionEnvironment
{
	// The following properties should be protected but have been made public to remove errors in IDE type hinting in
	// older versions of Phpstorm
	/** @var array $scriptModel */
	public $scriptModel;
	/** @var string[] $settings */
	public $settings = [];
	/** @var TaskModel $task */
	public $task;

	/**
	 * @param TaskModel $tasks
	 * @param IScriptModel $scriptModel
	 * @param array $entities
	 * @param array $settings
	 */
	public function __construct(TaskModel $tasks, IScriptModel $scriptModel, array $entities, array $settings)
	{
		$this->task = $tasks;
		$this->scriptModel = $scriptModel;
		foreach ($entities as $key => $value){
			$this->{$key} = $value;
		}
		$this->settings = $settings;
	}

	/**
	 * @param $name
	 * @param array $arguments
	 * @return bool|mixed
	 */
	public function callScript($name, $arguments = [])
	{
		$script = $this->scriptModel->getScript($name);
		$script = $script->bindTo($this, $this);
		call_user_func_array($script, $arguments);
	}
}
