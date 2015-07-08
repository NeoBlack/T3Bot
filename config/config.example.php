<?php

$GLOBALS['config'] = array(
	// project phase
	'projectPhase' => \T3Bot\Commands\AbstractCommand::PROJECT_PHASE_DEVELOPMENT,
	// slack config
	'slack'	=> array(
		// slack api host
		'apiHost' => 'typo3.slack.com',
		// secret token, to secure the request was received from slack.com
		'outgoingWebhookToken' => '',
		// secret token, to post into slack channel
		'incomingWebhookToken'  => '',
		// emoji icon as bot avatar
		'botAvatar' => '',
	),
	'gerrit' => array(
		// secret token, to secure the request was received from gerrit
		'webhookToken' => '',
		'change-merged' => array(
			'channels' => array('#random')
		),
		'patchset-created' => array(
			'channels' => array('#random')
		),
	),
	'db' => array(
		'host' => '127.0.0.1',
		'username' => '',
		'password' => '',
		'schema' => 't3bot'
	)
);