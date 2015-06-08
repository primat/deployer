<?php  namespace Primat\Deployer\Entity\Svn; 
/**
 * Project: Cogeco deployer scripts
 * Date: 05/06/15
 */

use Primat\Deployer\Entity\WorkingCopy;

/**
 * Class SvnWorkingCopy
 * @package Primat\Deployer\Entity\Svn
 */
class SvnWorkingCopy extends WorkingCopy
{
	/** @var SvnBranch $branch */
	public $branch;
	/** @var SvnInfo $info */
	protected $info;

	/**
	 * Constructor
	 * @param SvnBranch $branch
	 * @param string $workingCopyFolder
	 * @param SvnInfo $info
	 */
	public function __construct(SvnBranch $branch, $workingCopyFolder, SvnInfo $info = null)
	{
		parent::__construct($branch, $workingCopyFolder);
		$this->info = $info;
	}

	//
	// Getters and Setters
	//

	/**
	 * @return SvnInfo
	 */
	public function getInfo()
	{
		return $this->info;
	}

	/**
	 * @param SvnInfo $info
	 */
	public function setInfo(SvnInfo $info)
	{
		$this->info = $info;
	}

}
