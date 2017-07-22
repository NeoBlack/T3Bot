<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Tests\Unit;

use React\EventLoop\LoopInterface;
use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Commands\AbstractCommand;

/**
 * Class BaseCommandTestCase.
 */

/** @noinspection LongInheritanceChainInspection */
class BaseCommandTestCase extends BaseTestCase
{
    /**
     * @var AbstractCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $command;

    /**
     * @param string $commandClass
     * @param array $payloadData
     */
    protected function initCommandWithPayload(string $commandClass, array $payloadData)
    {
        $loop = $this->getMock(LoopInterface::class);
        /** @var Payload $payload */
        $payload = new Payload($payloadData);
        /** @var RealTimeClient $client */
        $client = $this->getMock(RealTimeClient::class, [], [$loop]);
        $this->command = new $commandClass($payload, $client, $GLOBALS['config']);
    }
}
