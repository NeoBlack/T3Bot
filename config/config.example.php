<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
$GLOBALS['config'] = array(
    // project phase
    'projectPhase' => \T3Bot\Commands\AbstractCommand::PROJECT_PHASE_DEVELOPMENT,
    // slack config
    'slack' => array(
        // emoji icon as bot avatar
        'botAvatar' => '',
        // bot auth token
        'botAuthToken' => '',
        // bot id (this is important, to prevent the bot to react on own messages
        'botId' => '',
        // Channels
        'channels' => [
            'channelCreated' => '#newChannel'
        ]
    ),
    'webhook' => array(
        't3o-registrations' => array(
            'securityToken' => '',
            'channels' => array('#t3o-registrations'),
        ),
    ),
    'gerrit' => array(
        // secret token, to ensure the request was received from gerrit
        'webhookToken' => '',
        'change-merged' => array(
            'channels' => array('#random'),
        ),
        'rst-merged' => array(
            'channels' => array('#random'),
        ),
        'patchset-created' => array(
            'channels' => array('#random'),
        ),
    ),
    'db' => array(
        'dbname' => 't3bot',
        'user' => 'root',
        'password' => '',
        'host' => '127.0.0.1',
        'driver' => 'pdo_mysql',
    ),
);
