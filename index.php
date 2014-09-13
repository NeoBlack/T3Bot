<?php

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$slackCommandController = new \T3Bot\Controller\SlackCommandController();
	$slackCommandController->process();
} else {
	echo file_get_contents('info.html');
}