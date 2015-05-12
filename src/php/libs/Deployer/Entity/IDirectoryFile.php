<?php

namespace Primat\Deployer\Entity;


interface IDirectoryFile {

	public function getHost();
	public function getPath();
	public function getSeparator();
	public function isRemote();
}