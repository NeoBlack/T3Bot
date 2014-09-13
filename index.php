<?php
/**
 * T3Bot
 * @author Frank Nägler <typo3@naegler.net>
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

/**
 * autoloader for T3Bot namespaced classes
 * @param $class the classname
 */
function __autoload($class) {
	$namespaceParts = explode('\\', $class);
	if ($namespaceParts[0] == 'T3Bot') {
		array_shift($namespaceParts);
		$fileName  		= 'lib' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $namespaceParts) . '.php';
		if (file_exists($fileName)) {
			require $fileName;
		}
	}
}

// if we receive a POST request, it is for our bot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$slackCommandController = new \T3Bot\Controller\SlackCommandController();
	$slackCommandController->process();
} else {
	// if it is no post, send the info.html page
	echo file_get_contents('info.html');
}