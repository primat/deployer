<?php namespace Primat\Deployer\Task;

use \Primat\Deployer\Task;

/**
 *
 */
class ViewTask
{
	/** @var string $folderViews */
	protected $folderViews;

	/**
	 * Constructor
	 * @param $projectFolder
	 */
	public function __construct($projectFolder)
	{
		$this->projectFolder = $projectFolder;
	}

	/**
	 * @param \Primat\Deployer\Entity\SvnLogEntry[] $logEntries
	 * @return string
	 */
	public function getLogEntriesHtml(array $logEntries) {
		$changelogHtml = '';
		if (count($logEntries)) {
			$changelogHtml .= '<ul>';
			foreach($logEntries as $revision => $entry) { /* @var \Primat\Deployer\Entity\SvnLogEntry $entry */
				$changelogHtml .= "<li><strong>{$revision}</strong><br />" . nl2br(trim($entry->message)) . "<br/><br/></li>";
			}
			$changelogHtml .= '</ul>';
		}
		return $changelogHtml;
	}

	/**
	 * @param \Primat\Deployer\Entity\SvnLogEntry[] $logEntries
	 * @return string
	 */
	public function getLogEntriesText(array $logEntries) {
		$changelogText = '';
		foreach($logEntries as $revision => $entry) { /* @var \Primat\Deployer\Entity\SvnLogEntry $entry */
			$changelogText .= "{$revision}:\n" . trim($entry->message) . "\n\n";
		}
		return $changelogText;
	}

	/**
	 * @param $path
	 * @param array $data
	 * @param bool $noEcho
	 * @return string
	 */
	public function load($path, array $data = NULL, $noEcho = FALSE)
	{
		if (isset($data) && count($data)) {
			extract($data);
		}
		$content = '';
		if ($noEcho) {
			ob_start();
		}
		include $this->projectFolder . '/' . $path;
		if ($noEcho) {
			$content = ob_get_contents();
			ob_end_clean();
		}
		return $content;
	}
}
