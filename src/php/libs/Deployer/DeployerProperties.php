<?php namespace Primat\Deployer;
/**
 * Date: 5/12/2015
 * Time: 10:07 PM
 */

use Primat\Deployer\DeployerProject;
use Primat\Deployer\Entity\Account;
use Primat\Deployer\Entity\Database\Mysql;
use Primat\Deployer\Entity\Dir;
use Primat\Deployer\Entity\Email;
use Primat\Deployer\Entity\Email\SmtpConnector;
use Primat\Deployer\Entity\Host;
use Primat\Deployer\Entity\RsyncOptions;
use Primat\Deployer\Entity\WorkingCopy;

class DeployerProperties
{
    /** @var $hosts Account[] */
    protected $accounts;
    /** @var $hosts Host[] */
    protected $hosts;
    /** @var $hosts Dir[] */
    protected $dirs;

    public function addHost()
    {

    }

}