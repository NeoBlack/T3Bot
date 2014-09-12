<?php

function __autoload($class) {
	$namespaceParts = explode('\\', $class);
	if ($namespaceParts[0] == 'T3Bot') {
		array_shift($namespaceParts);
		$fileName  		= implode(DIRECTORY_SEPARATOR, $namespaceParts) . '.php';
		if (file_exists($fileName)) {
			require $fileName;
		}
	}
}

$slackCommandController = new \T3Bot\Controller\SlackCommandController();
$slackCommandController->process();
