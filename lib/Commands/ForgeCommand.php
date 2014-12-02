<?php
/**
 * T3Bot
 * @author Frank Nägler <typo3@naegler.net>
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

namespace T3Bot\Commands;

/**
 * Class ForgeCommand
 *
 * @package T3Bot\Commands
 */
class ForgeCommand extends AbstractCommand {
	protected $commandName = 'forge';

	/**
	 *
	 */
	public function __construct() {
		$this->helpCommands['help'] = 'shows this help';
		$this->helpCommands['show <Issue-ID>'] = 'shows the issue by given <Issue-ID>';
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	protected function buildIssueMessage($item) {
		$created = substr($item->created_on, 0, 19);
		$updated = substr($item->updated_on, 0, 19);
		$text  = $this->bold('[' . $item->tracker->name . '] ' . $item->subject) . ' by ' . $this->italic($item->author->name) . "\n";
		$text .= 'Project: ' . $this->bold($item->project->name);
		if (strlen(trim($item->category->name)) > 0) {
			$text .= ' | Category: ' . $this->bold($item->category->name);
		}
		$text .= ' | Status: ' . $this->bold($item->status->name) . "\n";
		$text .= ':calendar: Created: ' . $this->bold($created) . ' | Last update: ' . $this->bold($updated) . "\n";
		$text .= '<https://forge.typo3.org/issues/' . $item->id . '|:arrow_right: View on Forge>';
		return $text;
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	protected function buildIssueLine($item) {
		return "*[{$item->tracker->name}] {$item->subject}* <https://forge.typo3.org/issues/{$item->id}|View on Forge>";
	}

	/**
	 * process show
	 *
	 * @return string
	 */
	protected function processShow() {
		$issueNumber = isset($this->params[1]) ? intval($this->params[1]) : null;
		if ($issueNumber === null || $issueNumber == 0) {
			return "hey, I need an issue number!";
		}
		$result = $this->queryForge("issues/{$issueNumber}");
		if ($result) {
			return $this->buildIssueMessage($result->issue);
		} else {
			return "Sorry not found!";
		}
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	protected function queryForge($query) {
		$url = "https://forge.typo3.org/{$query}.json";
		$result = file_get_contents($url);
		return json_decode($result);
	}
}