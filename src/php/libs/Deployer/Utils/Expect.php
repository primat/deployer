<?php namespace Primat\Deployer\Utils;

use \Primat\Deployer\Utils\Cygwin;

/**
 * Class Expect
 * @package Primat\Deployer\Utils
 */
class Expect
{
	/** @var string $srcPath */
	protected $srcPath;
	/** @var Cygwin $cygWin */
	protected $cygwin;
	/** @var string $expectCmd */
	protected $expectCmd = 'expect';

	/**
	 * Constructor
	 * @param string $srcPath
	 * @param Cygwin $cygwin
	 * @param string $expectCmd
	 */
	public function __construct($srcPath, Cygwin $cygwin, $expectCmd = 'expect')
	{
		$this->srcPath = $srcPath;
		$this->cygwin = $cygwin;
		$this->expectCmd = $expectCmd;
	}

	/**
	 * Gets the Expect command template used in commands which would normally require a person to interactively enter
	 * their (e.g. SSH) password
	 * @return string
	 */
	public function getPasswordCommandTemplate()
	{
		return $this->expectCmd . ' ' . $this->cygwin->cygPath($this->srcPath) . '/expect/pass.exp "%s" "%s"';
	}
}
