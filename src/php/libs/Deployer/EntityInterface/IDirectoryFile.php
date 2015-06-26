<?php namespace Primat\Deployer\EntityInterface;

/**
 * Interface IDirectoryFile
 * @package Primat\Deployer\EntityInterface
 */
interface IDirectoryFile {

	public function getHost();
	public function getPath();
	public function getSeparator();
	public function isRemote();
}
