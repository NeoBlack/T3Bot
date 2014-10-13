<?php

$GLOBALS['config'] = array(
	'slack'	=> array(
		// slack api host
		'apiHost' => 'typo3.slack.com',
		// secret token, to secure the request was received from slack.com
		'outgoingWebhookToken' => '',
		// secret token, to post into slack channel
		'incomingWebhookToken'  => '',
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
	)
);