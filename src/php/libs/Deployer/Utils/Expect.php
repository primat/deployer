<?php namespace Primat\Deployer\Utils;

/**
 * Class Expect
 * @package Primat\Deployer\Utils
 */
class Expect
{
	const SRC_REL_PATH = '../../../..';

	/** @var string $srcPath */
	protected $srcPath;
	/** @var Cygwin $cygwinService */
	protected $cygwinService;
	/** @var string $expectCmd */
	protected $expectCmd = 'expect';

	/**
	 * Constructor
	 * @param Cygwin $cygwinService
	 * @param string $expectCmd
	 */
	public function __construct(Cygwin $cygwinService, $expectCmd = 'expect')
	{
		$this->srcPath = realpath(__DIR__ . '/' . self::SRC_REL_PATH);
		$this->cygwinService = $cygwinService;
		$this->expectCmd = $expectCmd;
	}

	/**
	 * Gets the Expect command template used in commands which would normally require a person to interactively enter
	 * their (e.g. SSH) password
	 * @return string
	 */
	public function getPasswordCommandTemplate()
	{
		return $this->expectCmd . ' ' . $this->cygwinService->getCygPath($this->srcPath) . '/expect/pass.exp "%s" "%s"';
	}
}
