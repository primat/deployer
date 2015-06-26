<?php namespace Primat\Deployer\Model;
/**
 * Created by PhpStorm
 * Date: 6/2/2015
 */

/**
 * Interface IScriptModel
 * @package Primat\Deployer\Model
 */
interface IScriptModel
{
	/**
	 * @param $id
	 * @return callable
	 */
	public function getScript($id);

	/**
	 * @param $id
	 * @return mixed
	 */
	public function scriptExists($id);
}
