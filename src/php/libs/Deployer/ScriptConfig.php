<?php  namespace Primat\Deployer; 
/**
 * Project: Deployer
 * Date: 01/06/15
 */

/**
 * Class ScriptContext
 * @package Primat\Deployer
 */
class ScriptConfig
{
	/** @var \Primat\Deployer\Entity[] $entities */
	protected $entities = [];
	/** @var string $scriptId */
	protected $scriptId = '';
	/** @var string[] $context */
	protected $settings = [];
	/** @var string $title */
	protected $title = null;

	/**
	 * @param string $scriptId
	 * @param string $title
	 * @param \Primat\Deployer\Entity[] $entities
	 * @param string[] $settings
	 */
	public function __construct($scriptId, $title, array $entities, array $settings)
	{
		$this->scriptId = $scriptId;
		$this->title = $title;
		$this->entities = $entities;
		$this->settings = $settings;
	}

	/**
	 * @return Entity[]
	 */
	public function getEntities()
	{
		return $this->entities;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		if (empty($this->title)) {
			return "Script: $this->scriptId";
		}
		return $this->title;
	}

	/**
	 * @return \string[]
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * @return string
	 */
	public function getScriptId()
	{
		return $this->scriptId;
	}
}
