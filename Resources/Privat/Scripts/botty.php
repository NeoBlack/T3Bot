<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Slack\Message\Attachment;
use Slack\Message\Message;
use T3Bot\Commands\AbstractCommand;
use T3Bot\Commands\ChannelCommand;
use T3Bot\Commands\TellCommand;
use T3Bot\Slack\CommandResolver;

require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../config/config.php';

// The Slack connection
$loop = React\EventLoop\Factory::create();

$client = new Slack\RealTimeClient($loop);
$client->setToken($GLOBALS['config']['slack']['botAuthToken']);

$client->on('message', function (Slack\Payload $payload) use ($client) {
    $user = $payload->getData()['user'] ?? '';
    if ($user === '') {
        return;
    }
    $blackList = array_map('trim', explode(',', $GLOBALS['config']['slack']['userBlacklist']));
    if (in_array($user, $blackList, true)) {
        $client->apiCall('im.open', ['user' => $user])
            ->then(function (Slack\Payload $response) use ($client) {
                $message = new Message($client, [
                    'unfurl_links' => false,
                    'unfurl_media' => false,
                    'parse' => 'none',
                    'text' => 'Sorry, but you are blacklisted!',
                    'channel' => $response->getData()['channel']['id']
                ]);
                $client->postMessage($message);
            });
    } else {
        if ($user !== $GLOBALS['config']['slack']['botId']) {
            $command = (new CommandResolver($payload, $client))->resolveCommand($GLOBALS['config']);
            if ($command instanceof AbstractCommand) {
                $result = $command->process();
                if ($result !== false) {
                    $command->sendResponse($result);
                } else {
                    $command->sendResponse($command->getHelp());
                }
            }
        }
    }
});

$client->on('channel_created', function (Slack\Payload $payload) use ($client) {
    if ($payload->getData()['user'] !== $GLOBALS['config']['slack']['botId']) {
        $command = new ChannelCommand($payload, $client, $GLOBALS['config']);
        $command->processChannelCreated($payload->getData());
    }
});

$client->on('presence_change', function (Slack\Payload $payload) use ($client) {
    if ($payload->getData()['user'] !== $GLOBALS['config']['slack']['botId']) {
        $command = new TellCommand($payload, $client, $GLOBALS['config']);
        $command->processPresenceChange($payload->getData()['user'], $payload->getData()['presence']);
    }
});

$client->connect()->then(function () {
    echo "Connected!\n";
});

$db = DriverManager::getConnection($GLOBALS['config']['db'], new Configuration());

$loop->addPeriodicTimer(5, function () use ($client, $db) {
    $messages = $db->createQueryBuilder()
        ->select('*')
        ->from('messages')
        ->orderBy('id', 'ASC')
        ->execute()
        ->fetchAll();
    foreach ($messages as $message) {
        $data = json_decode($message['message'], true);
        $attachments = $data['attachments'];
        $data['attachments'] = [];
        foreach ($attachments as $attachment) {
            $data['attachments'][] = Attachment::fromData($attachment);
        }
        // process data
        $messageToSent = new Message($client, $data);
        $client->postMessage($messageToSent);

        // delete message
        $db->delete('messages', ['id' => $message['id']]);
    }
});

$loop->run();
