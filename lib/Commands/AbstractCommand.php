<?php
/**
 * T3Bot
 * @author Frank Nägler <typo3@naegler.net>
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

namespace T3Bot\Commands;

/**
 * Class AbstractCommand
 *
 * @package T3Bot\Commands
 */
abstract class AbstractCommand {
	const PROJECT_PHASE_DEVELOPMENT		= 'development';
	const PROJECT_PHASE_STABILISATION	= 'stabilisation';
	const PROJECT_PHASE_SOFT_FREEZE		= 'soft_freeze';
	const PROJECT_PHASE_CODE_FREEZE		= 'code_freeze';

	/**
	 * @var
	 */
	protected $commandName;

	/**
	 * @var array
	 */
	protected $helpCommands = array();

	/**
	 * @var array
	 */
	protected $params = array();

	abstract public function __construct();

	/**
	 * @param array $params
	 *
	 * @return mixed|string
	 */
	public function process(array $params = array()) {
		$this->params = $params;
		$command	  = isset($this->params[0]) ? $this->params[0] : 'help';
		$method = 'process' . ucfirst(strtolower($command));
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method));
		} else {
			return $this->getHelp();
		}
	}

	/**
	 * generate help
	 *
	 * @return string
	 */
	protected function getHelp() {
		$result = "*HELP*\n";
		foreach ($this->helpCommands as $command => $helpText) {
			$result .= "*{$this->commandName} {$command}*: {$helpText} \n";
		}
		return $result;
	}

	/**
	 * return number as emoji string
	 * @param $number
	 *
	 * @return string
	 */
	protected function getNumberAsEmoji($number) {
		$numbers = array(':zero:', ':one:', ':two:', ':three:', ':four:', ':five:', ':six:', ':seven:', ':eight:', ':nine:');
		$number = (string) $number;
		$result = '';
		foreach (str_split($number) as $char) {
			$result .= $numbers[(int) $char];
		}
		return $result;
	}

	/**
	 * build a review message
	 *
	 * @param object $item the review item
	 *
	 * @return string
	 */
	protected function buildReviewMessage($item) {
		$created = substr($item->created, 0, 19);
		$branch = $item->branch;

		switch ($GLOBALS['config']['projectPhase']) {
			case self::PROJECT_PHASE_STABILISATION:
				$text = ':warning: *stabilisation phase*' . "\n";
				break;
			case self::PROJECT_PHASE_SOFT_FREEZE:
				$text = ':no_entry: *soft merge freeze*' . "\n";
				break;
			case self::PROJECT_PHASE_CODE_FREEZE:
				$text = ':no_entry: *merge freeze*' . "\n";
				break;
			case self::PROJECT_PHASE_DEVELOPMENT:
			default:
				$text = '';
				break;
		}

		$text .= $this->bold($item->subject) . ' by ' . $this->italic($item->owner->name) . "\n";
		$text .= 'Branch: ' . $this->bold($branch) . ' | :calendar: ' . $this->bold($created) . ' | ID: ' . $this->bold($item->_number) . "\n";
		$text .= '<https://review.typo3.org/' . $item->_number . '|:arrow_right: Goto Review>';

		return $text;
	}

	/**
	 * build a review line
	 *
	 * @param object $item the review item
	 *
	 * @return string
	 */
	protected function buildReviewLine($item) {
		return $this->bold($item->subject) . ' <https://review.typo3.org/' . $item->_number . '|Review #' . $item->_number . ' now>';
	}

	/**
	 * query database
	 *
	 * @param string $query the SQL query
	 * @return resource
	 */
	protected function dbQuery($query) {
		return mysql_query($query, $GLOBALS['db_link']);
	}

	/**
	 * make text bold
	 *
	 * @param $string
	 * @return string
	 */
	protected function bold($string) {
		return '*' . $string . '*';
	}

	/**
	 * make text italic
	 *
	 * @param $string
	 * @return string
	 */
	protected function italic($string) {
		return '_' . $string . '_';
	}

}