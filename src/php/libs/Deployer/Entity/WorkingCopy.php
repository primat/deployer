<?php namespace Primat\Deployer\Entity;

use Primat\Deployer\Entity;
use Primat\Deployer\Exception;

/**
 * Class WorkingCopy
 * @package Primat\Deployer\Entity
 */
class WorkingCopy extends Entity
{
	/** @var RepositoryBranch $branch */
	public $branch;
	/** @var Dir $dir */
	public $dir;

	/**
	 * Constructor
	 * @param RepositoryBranch $branch
	 * @param string $workingCopyFolder
	 */
	public function __construct(RepositoryBranch $branch, $workingCopyFolder)
	{
		$this->branch = $branch;
		$this->dir = new Dir($workingCopyFolder);
	}
}
