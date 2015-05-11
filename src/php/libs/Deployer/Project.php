<?php namespace Deployer;
/**
 * Project: deployer
 * User: mprice
 * Date: 07/05/15
 */

/**
 * Class Project
 * @package Deployer
 */
class Project
{
	protected $projectFolder = '';

	/**
	 * Constructor
	 * @param $projectFolder
	 */
	public function __construct($projectFolder) {
		$this->$projectFolder = $projectFolder;
	}
}
