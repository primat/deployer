<?php namespace Primat\Deployer\Model;
/**
 * Created by PhpStorm
 * Date: 6/10/2015
 */

/**
 * Interface IProjectModel
 * @package Primat\Deployer\Model
 */
interface IProjectModel
{
	public function getFolder();
	public function getDbDumpFolder();
	public function getCacheFolder();
	public function getLogsFolder();
	public function getTempFolder();
	public function getViewsFolder();
}