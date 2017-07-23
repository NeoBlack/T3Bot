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
use T3Bot\Commands\AbstractCommand;
use T3Bot\Commands\BottyCommand;

/**
 * Class CommandResolver.
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
     * @param array|null $configuration
     *
     * @return bool|AbstractCommand
     */
    public function resolveCommand(array $configuration = null)
    {
        $message = $this->payload->getData()['text'];
        $commandClass = $this->detectCommandClass($message);
        if (class_exists($commandClass)) {
            return new $commandClass($this->payload, $this->client, $configuration);
        }

        if (strpos($message, 'botty') !== false || strpos($message, $configuration['slack']['botId']) !== false) {
            return new BottyCommand($this->payload, $this->client, $configuration);
        }

        return false;
    }

    /**
     * @param string $message
     *
     * @return string
     */
    protected function detectCommandClass(string $message) : string
    {
        $delimiter = strpos($message, ':') !== false ? ':' : ' ';
        $parts = explode($delimiter, $message);
        return '\\T3Bot\\Commands\\' . ucfirst(strtolower($parts[0])) . 'Command';
    }
}
