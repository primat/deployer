<?php namespace Primat\Deployer\Entity;

use \Primat\Deployer\Entity;

/**
 * A class for storing Email related data such as a list of recipients or reply-to address, for example
 * Class Email
 * @package Primat\Deployer\Entity
 */
class Email extends Entity
{
	/** @var string $fromAddress */
	public $fromAddress = '';
	/** @var string $fromName */
	public $fromName = '';
	/** @var string $replyAddress */
	public $replyAddress = '';
	/** @var string $replyName */
	public $replyName = '';
	/** @var string[] $to */
	public $to = array();
	/** @var string $subject */
	public $subject = '';
	/** @var string $bodyText */
	public $bodyText = '';
	/** @var string $bodyHtml */
	public $bodyHtml = '';
	/** @var mixed[] $attachments */
	public $attachments = array();
	/** @var string $encoding */
	public $encoding = 'UTF-8';

	/**
	 * @param $from
	 * @param $to
	 * @param $subject
	 */
	public function __construct($from, $to, $subject)
	{
		if (isset($from[0]) && isset($from[1])) {
			$this->fromAddress = $from[0];
			$this->fromName = $from[1];
		}
		else {
			$this->fromAddress = $from;
		}
		$this->to = $to;
		$this->subject = $subject;
	}
}
