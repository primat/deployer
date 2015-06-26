<?php namespace Primat\Deployer\Model;
/**
 * Created by PhpStorm
 * Date: 6/2/2015
 */

/**
 * Interface IEntityModel
 * @package Primat\Deployer\Model
 */
interface IEntityModel
{
	public function getEntity($type, $key);
}
