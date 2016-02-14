<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

namespace T3Bot\Slack;

use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Commands\BottyCommand;

/**
 * Class CommandResolver
 *
 * @package T3Bot
 */
class CommandResolver
{
    /**
     * @var RealTimeClient
     */
    protected $client;

    /**
     * @var Payload
     */
    protected $payload;

    public function __construct(Payload $payload, RealTimeClient $client)
    {
        $this->payload = $payload;
        $this->client = $client;
    }

    /**
     * @return \T3Bot\Commands\AbstractCommand|bool
     */
    public function resolveCommand()
    {
        $message = $this->payload->getData()['text'];
        $parts = explode(':', $message);
        $command = ucfirst(strtolower($parts[0]));
        $commandClass = '\\T3Bot\\Commands\\' . $command . 'Command';
        if (class_exists($commandClass)) {
            return new $commandClass($this->payload, $this->client);
        }

        $parts = explode(' ', $message);
        $command = ucfirst(strtolower($parts[0]));
        $commandClass = '\\T3Bot\\Commands\\' . $command . 'Command';
        if (class_exists($commandClass)) {
            return new $commandClass($this->payload, $this->client);
        }

        $resultCommand = false;
        if (strpos($message, 'botty') !== false || strpos($message, $GLOBALS['config']['slack']['botId']) !== false) {
            $resultCommand = new BottyCommand($this->payload, $this->client);
        }
        return $resultCommand;
    }
}
