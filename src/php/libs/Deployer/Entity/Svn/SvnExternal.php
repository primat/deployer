<?php namespace Primat\Deployer\Entity\Svn;

use Primat\Deployer\Entity;
use Primat\Deployer\Exception;

/**
 *
 */
class SvnExternal extends Entity
{
	public $basePath;
	public $relPath;
	public $url;
	public $revision;

	/**
	 * Constructor
	 * @param $basePath
	 * @param $relPath
	 * @param $url
	 * @param string $revision
	 */
	public function __construct($basePath, $relPath, $url, $revision = '')
	{
		$this->basePath = $basePath;
		$this->relPath = $relPath;
		$this->url = $url;
		$this->revision = $revision;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->basePath . '/' . $this->relPath;
	}
}
