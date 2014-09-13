<?php
/**
 * T3Bot
 * @author Frank Nägler <typo3@naegler.net>
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

namespace T3Bot\Controller;

/**
 * Class SlackCommandController
 *
 * @package T3Bot\Controller
 */
class SlackCommandController {
	const VERSION = '1.2.0';

	/** @var string current command */
	protected $command;

	/** @var array the params to the command */
	protected $params;

	/** @var string thats me, my name */
	protected $botName = '@T3Bot';

	/** @var string the username which talks to me */
	protected $username;

	/** @var string the mssage which was send */
	protected $message;

	/**
	 * the constructor parse the request and set some
	 * properties like username, message and params
	 */
	public function __construct() {
		$this->message  = $_REQUEST['text'];
		$this->username = $_REQUEST['user_name'];
		$this->params	= explode(' ', $this->message);
		// if the first word is the bot name, the second parameter is the command
		if (strtolower($this->params[0]) == strtolower($this->botName)) {
			// first remove the first word which is the bot name
			array_shift($this->params);
			$this->command	= ucfirst(strtolower(array_shift($this->params)));
		} else {
			// the first word is the command and subcommand splitted by a colon
			// the rest are the params
			$parts = explode(':', $this->params[0]);
			$this->command  	= ucfirst(strtolower($parts[0]));
			$this->params[0]	= $parts[1];
		}
	}

	/**
	 * @param $response
	 */
	protected function sendResponse($response) {
		$result = new \stdClass();
		$result->text = $response;
		echo json_encode($result);
	}

	/**
	 * public method to start processing the request
	 */
	public function process() {
		switch ($this->command) {
			case 'Help':
				$this->sendResponse($this->getHelp());
			break;
			case 'Version':
				$this->sendResponse(SlackCommandController::VERSION);
			break;
			case 'Debug':
				if ($this->username == 'neoblack') {
					$this->sendResponse(print_r($_REQUEST, true));
				}
			break;
			default:
				// each command is capsulated into a command class
				// try to find this command class and call the process method
				$commandClass	= '\\T3Bot\\Commands\\' . $this->command . 'Command';
				if (class_exists($commandClass)) {
					/** @var \T3Bot\Commands\AbstractCommand $commandInstance */
					$commandInstance = new $commandClass;
					$this->sendResponse($commandInstance->process($this->params));
				} else {
					// in case the command class not exists, try to scan
					// the message and response with a nice text
					$this->scanMessage();
				}
			break;
		}
	}

	/**
	 * @return string
	 */
	protected function getHelp() {
		$links = array(
			'My Homepage'		=> 'http://www.t3bot.de',
			'Github'			=> 'https://github.com/NeoBlack/T3Bot',
			'Help for Commands' => 'http://wiki.typo3.org/T3Bot'
		);
		$result = [];
		foreach ($links as $text => $link) {
			$result[] = ":link: <{$link}|{$text}>";
		}
		return implode(' | ', $result);
	}

	/**
	 * scan message for keywords
	 */
	protected function scanMessage() {
		$message = strtolower($this->message);
		$responses = array(
			'daddy'			=> "My daddy is Frank Nägler aka @neoblack",
			'n8'			=> "Good night @{$this->username}",
			'nacht'			=> "Good night @{$this->username}",
			'night'			=> "Good night @{$this->username}",
			'hello'			=> "Hello @{$this->username}, nice to see you!",
			'hallo'			=> "Hello @{$this->username}, nice to see you!",
			'ciao'			=> "Bye, bye @{$this->username}, see you later!",
			'cu'			=> "Bye, bye @{$this->username}, see you later!",
			'thx'			=> "You arw welcome @{$this->username}",
			'thank'			=> "You are welcome @{$this->username}",
			'drink'			=> "Coffee or beer @{$this->username}?",
			'coffee'		=> "Here is a :coffee: for you @{$this->username}",
			'beer'			=> "Here is a :t3beer: for you @{$this->username}",
			'coke'			=> "Coke is unhealthy @{$this->username}",
			'cola'			=> "Coke is unhealthy @{$this->username}",
			'cookie'		=> "Here is a :cookie: for you @{$this->username}",
			'typo3'			=> ":typo3: TYPO3 is the best open source CMS of world!",
			'dark'			=> "sure, we have cookies :cookie:",
		);
		foreach ($responses as $keyword => $response) {
			if (strpos($message, $keyword) !== false) {
				$this->sendResponse($response);
			}
		}
	}
}