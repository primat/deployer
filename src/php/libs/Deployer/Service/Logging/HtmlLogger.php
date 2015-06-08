<?php namespace Primat\Deployer\Service\Logging;
/**
 * Created by PhpStorm
 * Date: 5/13/2015
 * Time: 11:52 PM
 */

/**
 * Class HtmlLogger
 * @package Primat\Deployer\Service\Logging
 */
class HtmlLogger implements ILogger
{
    /**
     * @param string $message
     * @return void
     */
    public function log($message)
    {
        echo nl2br($message);
        flush();
    }
}