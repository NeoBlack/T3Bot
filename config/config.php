<?php

(new Dotenv\Dotenv(__DIR__ . '/../'))->load();

$GLOBALS['config'] = [
    // project phase
    'projectPhase' => getenv('PROJECT_PHASE'),

    // slack config
    'slack' => [
        // emoji icon as bot avatar
        'botAvatar' => '',
        // bot auth token
        // typo3.slack.com
        'botAuthToken' => getenv('SLACK_BOT_AUTH_TOKEN'),
        // bot id (this is important, to prevent the bot to react on own messages
        'botId' => getenv('SLACK_BOT_ID'),
        // Channels
        'channels' => [
            'channelCreated' => getenv('SLACK_CHANNEL_NEW_CHANNELS') // #typo3-cms-new-channel
        ],
        'userBlacklist' => getenv('SLACK_USER_BLACKLIST')
    ],
    'webhook' => [
        't3o-registrations' => [
            'securityToken' => getenv('WEBHOOK_T3O_REGISTRAION_SECURITY_TOKEN'),
            'channels' => ['#t3o-registrations']
        ]
    ],
    'gerrit' => [
        'webhookToken' => getenv('GERRIT_TOKEN'), // secret token, to secure the request was received from gerrit
        'change-merged' => [
            'channels' => ['#cms-ad-hoc-reviews', '#typo3-cms-coredev'],
        ],
        'rst-merged' => [
            'channels' => ['#rst-updates'],
        ],
        'patchset-created' => [
            'channels' => ['#cms-ad-hoc-reviews'],
        ],
    ],
    'db' => [
        'dbname' => getenv('DB_DBNAME'),
        'user' => getenv('DB_USER'),
        'password' => getenv('DB_PASS'),
        'host' => getenv('DB_HOST'),
        'driver' => 'pdo_mysql',
    ],
    'log' => [
        'file' => getenv('LOG_FILE'),
        'level' => getenv('LOG_LEVEL'),
    ]
];

// change avatar of bot from 1.12. to 26.12.
if (date('n') === '12' && (int) date('j') < 27) {
    $GLOBALS['config']['slack']['botAvatar'] = ':__t3botxmas__:';
}
