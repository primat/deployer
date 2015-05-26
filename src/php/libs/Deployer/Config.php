<?php namespace Primat\Deployer;

/**
 * Config class takes care of storing variable application settings
 */
class Config
{
	/** @var string[] $config */
	protected $config;
	/** @var bool $isImmutable */
	protected $isImmutable;

	/**
	 * Constructor
	 * @param array $defaultValues
	 * @param bool $isImmutable
	 */
	public function __construct(array $defaultValues = [], $isImmutable = false)
	{
		$this->isImmutable = $isImmutable;
		$this->config = $defaultValues;
	}

	/**
	 * Gets a value from the config based on a provided key
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default = '')
	{
		if (isset($this->config[$key])) {
			return $this->config[$key];
		}
		return $default;
	}

	/**
	 * Set the config value, pointed to by $key, to $value
	 * @param $key
	 * @param $value
	 */
	public function set($key, $value)
	{
		$this->config[$key] = $value;
	}

	/**
	 * @param $configFilePath
	 * @throws Exception
	 */
	public function loadFile($configFilePath)
	{
		// Load the config from the given path
		$config = array();
		if (is_file($configFilePath)) {
			$config = include $configFilePath;
		}
		else {
			throw new Exception('Unable to load config at ' . $configFilePath);
		}

		if (! is_array($config)) {
			throw new Exception("Config file $configFilePath is not returning an array");
		}

		$this->config = $config + $this->config; // Keeps left hand side elements if there are dupes
	}

	/**
	 * @param string $format
	 */
	public function output($format = 'text')
	{
		if ($format === 'html') {
			echo '<pre>';
			echo $this->toString();
			echo '</pre>';
		}
		else {
			echo $this->toString() . "\n";
		}
	}

	/**
	 * Displays the config data structure
	 * @return mixed
	 */
	public function toString()
	{
		return var_export($this->config, TRUE);
	}
}
