<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
$GLOBALS['config'] = [
    // project phase
    'projectPhase' => \T3Bot\Commands\AbstractCommand::PROJECT_PHASE_DEVELOPMENT,
    // slack config
    'slack' => [
        // emoji icon as bot avatar
        'botAvatar' => '',
        // bot auth token
        'botAuthToken' => '',
        // bot id (this is important, to prevent the bot to react on own messages
        'botId' => '',
        // Channels
        'channels' => [
            'channelCreated' => '#newChannel'
        ],
        'userBlacklist' => []
    ],
    'webhook' => [
        't3o-registrations' => [
            'securityToken' => '',
            'channels' => ['#t3o-registrations'],
        ],
    ],
    'gerrit' => [
        // secret token, to ensure the request was received from gerrit
        'webhookToken' => '',
        'change-merged' => [
            'channels' => ['#random'],
        ],
        'rst-merged' => [
            'channels' => ['#random'],
        ],
        'patchset-created' => [
            'channels' => ['#random'],
        ],
    ],
    'db' => [
        'dbname' => 't3bot',
        'user' => 'root',
        'password' => '',
        'host' => '127.0.0.1',
        'driver' => 'pdo_mysql',
    ],
];
