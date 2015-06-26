<?php namespace Primat\Deployer\Service\Logging;
/**
 * Created by PhpStorm.
 * Date: 5/13/2015
 */

/**
 * Interface ILogger
 * @package Primat\Deployer\Service\Logging
 */
interface ILogger
{
    /**
     * @param string $message
     * @return void
     */
    public function log($message);
}
