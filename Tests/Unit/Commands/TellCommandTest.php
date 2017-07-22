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

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use React\EventLoop\LoopInterface;
use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Commands\TellCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class TellCommandTest.
 */

/** @noinspection LongInheritanceChainInspection */
class TellCommandTest extends BaseCommandTestCase
{
    /**
     *
     */
    public function tearDown()
    {
        DriverManager::getConnection($GLOBALS['config']['db'], new Configuration())
            ->delete('notifications', [
                'to_user' => 'U12345'
            ]);
        parent::tearDown();
    }

    /**
     * @return array
     */
    public function tellDataProvider() : array
    {
        return [
            'tell <@U12345> about review:47640' => ['tell <@U12345> about review:47640', 'OK, I will tell <@U12345> about your message'],
            'tell <@U12345> about forge:23456' => ['tell <@U12345> about forge:23456', 'OK, I will tell <@U12345> about your message'],
            'tell <@U12345> you are so nice' => ['tell <@U12345> you are so nice', 'OK, I will tell <@U12345> about your message'],
        ];
    }

    /**
     * @test
     * @dataProvider tellDataProvider
     *
     * @param string $message
     * @param string $expectedMessage
     */
    public function processTellReturnsCorrectResponse($message, $expectedMessage)
    {
        $this->initCommandWithPayload(TellCommand::class, [
            'user' => 'U54321',
            'text' => $message,
        ]);
        $result = $this->command->process();
        static::assertEquals($expectedMessage, $result);
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
        $command->expects(static::exactly(0))
            ->method('sendResponse');
        $command->processPresenceChange('U12345', 'away');
    }

    /**
     * @test
     * @dataProvider tellDataProvider
     */
    public function processPresenceChangeReturnsCorrectResponseForPresenceActive($message)
    {
        $this->initCommandWithPayload(TellCommand::class, [
            'user' => 'U54321',
            'text' => $message,
        ]);
        $this->command->process();

        $loop = $this->getMock(LoopInterface::class);
        $payload = new Payload([
            'user' => 'U12345',
            'presence' => 'active',
        ]);
        $client = $this->getMock(RealTimeClient::class, [], [$loop]);
        /** @var TellCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->getMock(TellCommand::class, ['sendResponse'], [$payload, $client]);
        $command
            ->expects(static::exactly(1))
            ->method('sendResponse');
        $command->processPresenceChange('U12345', 'active');
    }
}
