<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/config/config.php';

// The Slack connection
$loop = React\EventLoop\Factory::create();

$client = new Slack\RealTimeClient($loop);
$client->setToken($GLOBALS['config']['slack']['botAuthToken']);

$client->on('message', function (Slack\Payload $payload) use ($client) {
    if ($payload->getData()['user'] !== $GLOBALS['config']['slack']['botId']) {
        $commandResolver = new \T3Bot\Slack\CommandResolver($payload, $client);
        $command = $commandResolver->resolveCommand();
        if ($command instanceof \T3Bot\Commands\AbstractCommand) {
            $result = $command->process();
            if ($result !== false) {
                $command->sendResponse($result);
            } else {
                $command->sendResponse($command->getHelp());
            }
        }
    }
});

$client->on('presence_change', function (Slack\Payload $payload) use ($client) {
    if ($payload->getData()['user'] !== $GLOBALS['config']['slack']['botId']) {
        $command = new \T3Bot\Commands\TellCommand($payload, $client);
        $command->processPresenceChange($payload->getData()['user'], $payload->getData()['presence']);
    }
});

$client->connect()->then(function () use ($client) {
    echo "Connected!\n";
});

/* @noinspection PhpInternalEntityUsedInspection */
$db = \Doctrine\DBAL\DriverManager::getConnection($GLOBALS['config']['db'], new \Doctrine\DBAL\Configuration());

$loop->addPeriodicTimer(5, function () use ($client, $db) {
    $messages = $db->fetchAll('SELECT * FROM messages ORDER BY id ASC');
    foreach ($messages as $message) {
        $data = json_decode($message['message'], true);
        $attachments = $data['attachments'];
        $data['attachments'] = [];
        foreach ($attachments as $attachment) {
            $data['attachments'][] = \Slack\Message\Attachment::fromData($attachment);
        }
        // process data
        $messageToSent = new \Slack\Message\Message($client, $data);
        $client->postMessage($messageToSent);

        // delete message
        $db->delete('messages', ['id' => $message['id']]);
    }
});

$loop->run();
