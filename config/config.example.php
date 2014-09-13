<?php

$GLOBALS['config'] = array(
	'slack'	=> array(
		'outgoingWebhookToken' => '', // secret token, to secure the request was received from slack.com
		'incomingWbhookToken'  => '', // secret token, to post into slack channel
	),
	'gerrit' => array(
		'webhookToken' => '', // secret token, to secure the request was received from gerrit
		'change-merged' => array(
			'channels' => array('#random')
		),
		'patchset-created' => array(
			'channels' => array('#random')
		),
	)
);