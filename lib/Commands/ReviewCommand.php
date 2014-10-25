<?php
/**
 * T3Bot
 * @author Frank Nägler <typo3@naegler.net>
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

namespace T3Bot\Commands;

/**
 * Class ReviewCommand
 *
 * @package T3Bot\Commands
 */
class ReviewCommand extends AbstractCommand {
	protected $commandName = 'review';

	/**
	 *
	 */
	public function __construct() {
		$this->helpCommands['help'] = 'shows this help';
		$this->helpCommands['count [PROJECT=Packages/TYPO3.CMS]'] = 'shows the number of currently open reviews for [PROJECT]';
		$this->helpCommands['random'] = 'shows a random open review';
		$this->helpCommands['show <Ref-ID> [<Ref-ID-2>, [<Ref-ID-n>]]'] = 'shows the review by given change number(s)';
		$this->helpCommands['user <username> [PROJECT=Packages/TYPO3.CMS]'] = 'shows the open reviews by given username for [PROJECT]';
		$this->helpCommands['query <searchQuery>'] = 'shows the results for given <searchQuery>, max limit is 10';
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	protected function buildReviewMessage($item) {
		$created = substr($item->created, 0, 19);
		$updated = substr($item->updated, 0, 19);
		return "*{$item->subject}* by _{$item->owner->name}_
Created: {$created} | Last update: {$updated} | ID: {$item->_number}
<https://review.typo3.org/{$item->_number}|Review now>
";
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	protected function buildReviewLine($item) {
		return "*{$item->subject}* <https://review.typo3.org/{$item->_number}|Review #{$item->_number} now>";
	}

	/**
	 * process count
	 *
	 * @return string
	 */
	protected function processCount() {
		$project = isset($this->params[1]) ? $this->params[1] : 'Packages/TYPO3.CMS';
		$result = $this->queryGerrit("is:open project:{$project}");
		$count  = count($result);
		$result = $this->queryGerrit("label:Code-Review=-1 is:open project:{$project}");
		$countMinus1  = count($result);
		$result = $this->queryGerrit("label:Code-Review=-2 is:open project:{$project}");
		$countMinus2  = count($result);

		$returnString = '';
		$returnString .= "There are currently *{$count}* open reviews for project '{$project}' on https://review.typo3.org \n";
		$returnString .= "*{$countMinus1}* of *{$count}* open reviews voted with *-1* <https://review.typo3.org/#/q/label:Code-Review%253D-1+is:open+project:Packages/TYPO3.CMS|Check now> \n";
		$returnString .= "*{$countMinus2}* of *{$count}* open reviews voted with *-2* <https://review.typo3.org/#/q/label:Code-Review%253D-2+is:open+project:Packages/TYPO3.CMS|Check now> \n";
		return $returnString;
	}

	/**
	 * process random
	 *
	 * @return string
	 */
	protected function processRandom() {
		$result = $this->queryGerrit('is:open project:Packages/TYPO3.CMS');
		$item	= $result[array_rand($result)];
		return $this->buildReviewMessage($item);
	}

	/**
	 * process user
	 *
	 * @return string
	 */
	protected function processUser() {
		$username = isset($this->params[1]) ? $this->params[1] : null;
		$project  = isset($this->params[2]) ? $this->params[2] : 'Packages/TYPO3.CMS';
		if ($username === null) {
			return "hey, I need a username!";
		}
		$results = $this->queryGerrit('is:open owner:"'.$username.'" project:'.$project);
		if (count($results) > 0) {
			$listOfItems = array("*Here are the results for {$results[0]->owner->name}*:");
			foreach ($results as $item) {
				$listOfItems[] = $this->buildReviewLine($item);
			}
			return implode("\n", $listOfItems);
		} else {
			return "{$username} has no open reviews or username is unknown";
		}
	}

	/**
	 * process count
	 *
	 * @return string
	 */
	protected function processShow() {
		$refId = isset($this->params[1]) ? intval($this->params[1]) : null;
		if ($refId === null || $refId == 0) {
			return "hey, I need at least one change number!";
		}
		if (count($this->params) > 2) {
			$changeIds = array();
			for ($i=1;$i<count($this->params); $i++) {
				$changeIds[] = 'change:' . $this->params[$i];
			}
			$result = $this->queryGerrit(implode(' OR ', $changeIds));
			$listOfItems = array();
			foreach ($result as $item) {
				$listOfItems[] = $this->buildReviewLine($item);
			}
			return implode("\n", $listOfItems);
		} else {
			$result = $this->queryGerrit('change:'.$refId);
			foreach ($result as $item) {
				if ($item->_number == $refId) {
					return $this->buildReviewMessage($item);
				} else {
					return "{$refId} not found, sorry!";
				}
			}
		}
	}

	/**
	 * process query
	 *
	 * @return string
	 */
	protected function processQuery() {
		$query = isset($this->params[1]) ? $this->params[1] : null;
		if ($query === null) {
			return "hey, I need a query!";
		}

		$params = $this->params;
		array_shift($params);
		$query = implode('+', $params);
		$query = str_replace(' ', '+', $query);
		$results = $this->queryGerrit('limit:10 '.$query);
		if (count($results) > 0) {
			$listOfItems = array("*Here are the results for {$query}*:");
			foreach ($results as $item) {
				$listOfItems[] = $this->buildReviewLine($item);
			}
			return implode("\n", $listOfItems);
		}
		return "{$query} not found, sorry!";
	}

	/**
	 * @param $query
	 *
	 * @return object|array
	 */
	protected function queryGerrit($query) {
		$url = 'https://review.typo3.org/changes/?q=' . urlencode($query);
		$result = file_get_contents($url);
		$result = json_decode(str_replace(")]}'\n", '', $result));
		return $result;
	}
}