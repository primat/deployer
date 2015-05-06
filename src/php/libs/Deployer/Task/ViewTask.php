<?php namespace Deployer\Task;

use \Deployer\Task;

/**
 *
 */
class ViewTask extends Task
{
	/**
	 * @param \Deployer\Entity\SvnLogEntry[] $logEntries
	 * @return string
	 */
	public static function getLogEntriesHtml(array $logEntries) {
		$changelogHtml = '';
		if (count($logEntries)) {
			$changelogHtml .= '<ul>';
			foreach($logEntries as $revision => $entry) { /* @var \Deployer\Entity\SvnLogEntry $entry */
				$changelogHtml .= "<li><strong>{$revision}</strong><br />" . nl2br(trim($entry->message)) . "<br/><br/></li>";
			}
			$changelogHtml .= '</ul>';
		}
		return $changelogHtml;
	}

	/**
	 * @param \Deployer\Entity\SvnLogEntry[] $logEntries
	 * @return string
	 */
	public static function getLogEntriesText(array $logEntries) {
		$changelogText = '';
		foreach($logEntries as $revision => $entry) { /* @var \Deployer\Entity\SvnLogEntry $entry */
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
	public static function load($path, array $data = NULL, $noEcho = FALSE)
	{
		if (isset($data) && count($data)) {
			extract($data);
		}
		$content = '';
		if ($noEcho) {
			ob_start();
		}
		include SCRIPT_DIR . '/' . $path;
		if ($noEcho) {
			$content = ob_get_contents();
			ob_end_clean();
		}
		return $content;
	}
}
