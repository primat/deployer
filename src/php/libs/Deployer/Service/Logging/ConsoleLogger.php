<?php namespace Primat\Deployer\Service\Logging;
/**
 * Created by PhpStorm.
 * Date: 5/13/2015
 */

/**
 * Class ConsoleLogger
 * @package Primat\Deployer\Service\Logging
 */
class ConsoleLogger implements ILogger
{
    /**
     * @param string $message
     */
    public function log($message)
    {
        echo $message;
    }
}
