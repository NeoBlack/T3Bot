<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Tests\Unit\Commands;

use PHPUnit\Framework\Assert;
use React\EventLoop\LoopInterface;
use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Commands\ChannelCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class UtilCommandTest.
 */

/** @noinspection LongInheritanceChainInspection */
class ChannelCommandTest extends BaseCommandTestCase
{
    /** @var  ChannelCommand */
    protected $command;

    /**
     * @test
     */
    public function procesReturnFalse()
    {
        $this->initCommandWithPayload(ChannelCommand::class, []);
        self::assertFalse($this->command->process());
    }

    /**
     * @test
     */
    public function processChannelCreatedCallSendResponseWithCorrectText()
    {
        $loop = $this->getMock(LoopInterface::class);
        $payload = new Payload([
            'user' => 'U12345',
        ]);
        $client = $this->getMock(RealTimeClient::class, [], [$loop]);
        /** @var ChannelCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->getMock(ChannelCommand::class, ['sendResponse'], [$payload, $client, $GLOBALS['config']]);
        $command
            ->expects(static::exactly(1))
            ->method('sendResponse')
            ->will($this->returnCallback(function($message) {
                Assert::assertSame('<@U024BE7LH> opened channel #fun, join it <#C024BE91L>', $message->getText());
            }));

        $command->processChannelCreated([
            'type' => 'channel_created',
            'channel' => [
                'id' => 'C024BE91L',
                'name' => 'fun',
                'created' => 1360782804,
                'creator' => 'U024BE7LH'
            ],
        ]);
    }
}
