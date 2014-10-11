<?php
/**
 * T3Bot
 * @author Frank Nägler <typo3@naegler.net>
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

/**
 * autoloader for T3Bot namespaced classes
 * @param string $class the classname
 */
function __autoload($class) {
	$rootPath	= dirname(__FILE__) . '/../../';
	$namespaceParts = explode('\\', $class);
	if ($namespaceParts[0] == 'T3Bot') {
		array_shift($namespaceParts);
		$fileName  		= $rootPath . 'lib' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $namespaceParts) . '.php';
		if (file_exists($fileName)) {
			require $fileName;
		}
	}
}

require_once(dirname(__FILE__) . '/../../config/config.php');

file_put_contents('gerrit.log', file_get_contents('gerrit.log') . "\n" . file_get_contents('php://input'));
// if we receive a POST request, it is for our bot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$slackCommandController = new \T3Bot\Controller\GerritHookController();
	switch ($_REQUEST['action']) {
		case 'change-merged':
		case '/var/gerrit/review/hooks/change-merged':
			$slackCommandController->process('change-merged');
			break;
		case 'patchset-created':
		case '/var/gerrit/review/hooks/patchset-created':
			$slackCommandController->process('patchset-created');
			break;
	}
}