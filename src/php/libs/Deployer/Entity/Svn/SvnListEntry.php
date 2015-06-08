<?php  namespace Primat\Deployer\Entity\Svn;
/**
 * Project: Cogeco deployer scripts
 * Date: 05/06/15
 */

use Primat\Deployer\Entity;
use Primat\Deployer\Exception;

/**
 * Class SvnListEntry
 * @package Primat\Deployer\Entity
 */
class SvnListEntry
{
	public $revision = 0;
	public $author = '';
	public $date = '';
	public $name ='';

	/**
	 * @param \SimpleXMLElement $xmlObj
	 */
	public function __construct(\SimpleXMLElement $xmlObj)
	{
		/*
		<?xml version="1.0" encoding="UTF-8"?>
		<lists>
			<list path="https://subversion.cogeco.com/svn/External/wwwCogecoCa/branches">
				<entry kind="dir">
					<name>FSecure-c_vhum-2015042015</name>
					<commit revision="3079">
						<author>cbredowburgel</author>
						<date>2015-04-29T18:12:05.001680Z</date>
					</commit>
				</entry>
			</list>
		</lists>
		*/

		$this->revision = (int)$xmlObj->commit['revision'];
		$this->author = (string)$xmlObj->commit->author;
		$this->date = (string)$xmlObj->commit->date;
		$this->name = (string)$xmlObj->name;
	}
}
