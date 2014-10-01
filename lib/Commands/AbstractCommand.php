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
}