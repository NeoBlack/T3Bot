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

use React\EventLoop\LoopInterface;
use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Commands\TellCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class TellCommandTest.
 */
class TellCommandTest extends BaseCommandTestCase
{
    /**
     * @return array
     */
    public function tellDataProvider()
    {
        return [
            'tell <@U12345> about review:12345' => ['tell <@U12345> about review:12345', 'OK, I will tell <@U12345> about your message'],
            'tell <@U12345> about forge:12345' => ['tell <@U12345> about forge:12345', 'OK, I will tell <@U12345> about your message'],
            'tell <@U12345> you are so nice' => ['tell <@U12345> you are so nice', 'OK, I will tell <@U12345> about your message'],
        ];
    }

    /**
     * @test
     * @dataProvider tellDataProvider
     */
    public function processTellReturnsCorrectResponse($message, $expectedMessage)
    {
        $this->initCommandWithPayload(TellCommand::class, [
            'user' => 'U54321',
            'text' => $message,
        ]);
        $result = $this->command->process();
        $this->assertEquals($expectedMessage, $result);
    }

    /**
     * @test
     */
    public function processPresenceChangeReturnsCorrectResponseForPresenceAway()
    {
        $loop = $this->getMock(LoopInterface::class);
        /** @var Payload $payload */
        $payload = new Payload([
            'user' => 'U12345',
            'presence' => 'away',
        ]);
        /** @var RealTimeClient $client */
        $client = $this->getMock(RealTimeClient::class, [], [$loop]);
        /** @var TellCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->getMock(TellCommand::class, ['sendResponse'], [$payload, $client]);
        $command->expects($this->exactly(0))
            ->method('sendResponse');
        $command->processPresenceChange('U12345', 'away');
    }

    /**
     * @test
     */
    public function processPresenceChangeReturnsCorrectResponseForPresenceActive()
    {
        $loop = $this->getMock(LoopInterface::class);
        /** @var Payload $payload */
        $payload = new Payload([
            'user' => 'U12345',
            'presence' => 'active',
        ]);
        /** @var RealTimeClient $client */
        $client = $this->getMock(RealTimeClient::class, [], [$loop]);
        /** @var TellCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->getMock(TellCommand::class, ['sendResponse'], [$payload, $client]);
        $command->expects($this->exactly(3))
            ->method('sendResponse');
        $command->processPresenceChange('U12345', 'active');
    }
}
