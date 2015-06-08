<?php namespace Primat\Deployer\Entity\Svn;

use Primat\Deployer\Entity;
use Primat\Deployer\Exception\EntityException;

/**
 *
 */
class SvnInfo extends Entity
{
	public $absPath = '';
	public $currentRevision;
	public $commitRevision;
	public $commitDate;
	public $commitAuthor;
	public $rootUrl;
	public $uri;
	public $uuid;

	/**
	 * Constructor
	 * @param string $xml
	 * @throws EntityException
	 */
	public function __construct($xml)
	{		//print_r($xml);
/*
<?xml version="1.0" encoding="UTF-8"?>
<info>
	<entry kind="dir" path="MyAccountFE" revision="18881">
		<url>https://source.cogeco.com/repository/corp/web/trunk/WebMarketing/MyAccountFE</url>
		<repository>
			<root>https://source.cogeco.com/repository/corp/web</root>
			<uuid>8f82aa4d-edf7-ec26-c545-ff29a6b4c01e</uuid>
		</repository>
		<commit revision="18869">
			<author>c_mbeauchemin@COGECO.COM</author>
			<date>2013-11-26T20:05:14.741748Z</date>
		</commit>
	</entry>
</info>
*/
		$xmlObj = @simplexml_load_string($xml);
		if (! $xmlObj) {
			throw new EntityException("Unable to unmarshall XML:\n" . $xml . "\n");
		}

		if (isset($xmlObj->entry['revision'])) {
			$this->currentRevision = (int)$xmlObj->entry['revision'];
		}
		else {
			throw new EntityException("Missing revision attribute in XML:\n" . $xml . "\n");
		}

		if (isset($xmlObj->entry->commit['revision'])) {
			$this->commitRevision = (int)$xmlObj->entry->commit['revision'];
		}
		else {
			throw new EntityException("Missing commit revision attribute in XML:\n" . $xml . "\n");
		}

		if (isset($xmlObj->entry->commit->date)) {
			$this->commitDate = (string)$xmlObj->entry->commit->date;
		}
		else {
			throw new EntityException("Missing commit date tag in XML:\n" . $xml . "\n");
		}

		if (isset($xmlObj->entry->commit->author)) {
			$this->commitAuthor = (string)$xmlObj->entry->commit->author;
		}
		else {
			throw new EntityException("Missing commit author tag in XML:\n" . $xml . "\n");
		}

		if (isset($xmlObj->entry->url)) {
			$url = (string)$xmlObj->entry->url;
		}
		else {
			throw new EntityException("Missing url tag in XML:\n" . $xml . "\n");
		}

		if (isset($xmlObj->entry->repository->root)) {
			$this->rootUrl = (string)$xmlObj->entry->repository->root;
		}
		else {
			throw new EntityException("Missing repository root tag in XML:\n" . $xml . "\n");
		}

		if (isset($xmlObj->entry->repository->uuid)) {
			$this->uuid = (string)$xmlObj->entry->repository->uuid;
		}
		else {
			throw new EntityException("Missing repository uuid tag in XML:\n" . $xml . "\n");
		}

		if (isset($xmlObj->entry->repository->root)) {
			$this->rootUrl = (string)$xmlObj->entry->repository->root;
		}
		else {
			throw new EntityException("Missing repository root tag in XML:\n" . $xml . "\n");
		}

		if (isset($xmlObj->entry->{'wc-info'}->{'wcroot-abspath'})) {
			$this->absPath = (string)$xmlObj->entry->{'wc-info'}->{'wcroot-abspath'};
		}

		$this->uri = str_replace($this->rootUrl, '', $url);
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->rootUrl . $this->uri;
	}
}
