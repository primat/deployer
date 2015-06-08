<?php namespace Primat\Deployer;
/**
 * Project: Deployer
 * Date: 19/05/15
 */

use Primat\Deployer\Model\TaskModel;

/**
 * Class DeployerScript
 * @package Primat\Deployer
 */
class Script
{
    /** @var string $name The name of the script */
    protected $name = '';
	/** @var string[] $settings Script specific settings */
	protected $settings = [];

	// Script run-time properties

	/** @var string[] $context */
	protected $context = [];
	/** @var callable[] $scripts List of other used scripts */
	protected $scripts = [];
	/** @var TaskModel $tasks */
	protected $tasks;

	/**
	 * @return callable
	 */
	public function getScriptClosure(){}

	/**
	 * Initial app and task settings
	 * @return array
	 */
	public function getName()
	{
		// Set the default human readable script name as the current class name if not set already in subclass
		if (empty($this->name)) {
			return get_class($this);
		}
		return $this->name;
	}

	/**
	 * Initial app and task settings
	 * @return string[]
	 */
	public function getSettings()
	{
		return $this->settings;
	}
}
