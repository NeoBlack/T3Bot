<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Tests\Unit\Slack;

use React\EventLoop\LoopInterface;
use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Commands\BeerCommand;
use T3Bot\Commands\BottyCommand;
use T3Bot\Commands\ForgeCommand;
use T3Bot\Commands\ReviewCommand;
use T3Bot\Commands\TellCommand;
use T3Bot\Commands\UtilCommand;
use T3Bot\Slack\CommandResolver;
use T3Bot\Tests\Unit\BaseTestCase;

/**
 * Class CommandResolverTest.
 */

/** @noinspection LongInheritanceChainInspection */
class CommandResolverTest extends BaseTestCase
{
    /**
     * DataProvider for all commands.
     *
     * @return array
     */
    public function commandResolverDataProvider()
    {
        return [
            'beer:foo' => ['beer:foo', BeerCommand::class],
            'botty:foo' => ['botty:foo', BottyCommand::class],
            'forge:foo' => ['forge:foo', ForgeCommand::class],
            'review:foo' => ['review:foo', ReviewCommand::class],
            'util:foo' => ['util:foo', UtilCommand::class],
            'tell <@user> about' => ['tell <@user> about', TellCommand::class],
            'bar foo botty foo bar' => ['bar foo botty foo bar', BottyCommand::class],
            'no command for any message' => ['no command for any message', false],
        ];
    }

    /**
     * @param string $message
     * @param string $expectedClass
     *
     * @test
     * @dataProvider commandResolverDataProvider
     */
    public function ensureToResolveCorrectCommand($message, $expectedClass)
    {
        // set config value for botId
        $GLOBALS['config']['slack']['botId'] = 'botty';

        $loop = $this->getMock(LoopInterface::class);
        /** @var Payload $payload */
        $payload = new Payload(['text' => $message]);
        /** @var RealTimeClient $client */
        $client = $this->getMock(RealTimeClient::class, [], [$loop]);
        $commandResolver = new CommandResolver($payload, $client);
        $subject = $commandResolver->resolveCommand($GLOBALS['config']);
        if ($expectedClass === false) {
            static::assertFalse($subject);
        } else {
            static::assertInstanceOf($expectedClass, $subject);
        }
    }
}
