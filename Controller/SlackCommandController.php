<?php

namespace T3Bot\Controller;

class SlackCommandController {
	const VERSION = '1.1.0';

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

	public function __construct() {
		$this->params	= explode(' ', $_REQUEST['text']);
		$this->message  = $_REQUEST['text'];
		$this->username = $_REQUEST['user_name'];
		// first remove the first word which is the bot name
		array_shift($this->params);
		// second get the command, the rest are the params
		$this->command	= ucfirst(strtolower(array_shift($this->params)));
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
	 *
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
				$commandClass	= '\\T3Bot\\Commands\\' . $this->command . 'Command';
				if (class_exists($commandClass)) {
					/** @var \T3Bot\Commands\AbstractCommand $commandInstance */
					$commandInstance = new $commandClass;
					$this->sendResponse($commandInstance->process($this->params));
				} else {
					$this->scanMessage();
				}
			break;
		}
	}

	/**
	 * @return string
	 */
	protected function getHelp() {
		return "*HELP*
*{$this->botName} review help:* get help for the review command
*{$this->botName} forge help:* get help for the forge command
";
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
			'thx'			=> "Your welcome @{$this->username}",
			'thanks'		=> "Your welcome @{$this->username}",
			'drink'			=> "Coffee or beer @{$this->username}?",
			'coffee'		=> "Here is a :coffee: for you @{$this->username}",
			'beer'			=> "Here is a :t3beer: for you @{$this->username}",
			'coke'			=> "Coke is unhealthy @{$this->username}",
			'cola'			=> "Coke is unhealthy @{$this->username}",
			'cookie'		=> "Here is a :cookie: for you @{$this->username}",
			'typo3'			=> ":typo3: TYPO3 is the best open source CMS of world!",
		);
		foreach ($responses as $keyword => $response) {
			if (strpos($message, $keyword) !== false) {
				$this->sendResponse($response);
			}
		}
	}
}