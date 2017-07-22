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
use T3Bot\Commands\BeerCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class BeerCommandTest.
 */

/** @noinspection LongInheritanceChainInspection */
class BeerCommandTest extends BaseCommandTestCase
{
    /**
     *
     */
    public function tearDown()
    {
        DriverManager::getConnection($GLOBALS['config']['db'], new Configuration())
            ->delete('beers', [
                'to_user' => 'U23456',
                'from_user' => 'U12345',
            ]);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function processForReturnsCorrectResponseForWrongUsername()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U54321',
            'text' => 'beer:for max',
        ]);
        $result = $this->command->process();
        static::assertEquals('*Sorry, a username must start with a @-sign:*', $result);
    }

    /**
     * @test
     */
    public function processForReturnsCorrectResponseForCorrectUsername()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:for <@U23456>',
        ]);
        $result = $this->command->process();
        static::assertStringStartsWith('Yeah, one more :t3beer: for <@U23456>', $result);
    }

    /**
     * @test
     */
    public function processForReturnsCorrectResponseForCorrectUsernameWithin24Hours()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:for <@U23456>',
        ]);
        $this->command->process();
        $result = $this->command->process();
        static::assertStringStartsWith('You spend one :t3beer: to <@U23456> within in last 24 hours. Too much beer is unhealthy ;)', $result);
    }

    /**
     * @test
     */
    public function processAllReturnsCorrectResponse()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:all',
        ]);
        $result = $this->command->process();
        static::assertRegExp('/Yeah, ([0-9]*) :t3beer: spend to all people/', $result);
    }

    /**
     * @test
     */
    public function processTop10ReturnsCorrectResponse()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:top10',
        ]);
        $result = $this->command->process();
        static::assertStringStartsWith('*Yeah, here are the TOP 10*', $result);
    }

    /**
     * @test
     */
    public function processStatsReturnsCorrectResponseForCorrectUsername()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:stats <@U23456>',
        ]);
        $result = $this->command->process();
        static::assertRegExp('/<@U23456> has received ([0-9]*) :t3beer: so far/', $result);
    }

    /**
     * @test
     */
    public function processStatsReturnsCorrectResponseForinvalidUsername()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:stats foo',
        ]);
        $result = $this->command->process();
        static::assertEquals('*Sorry, a username must start with a @-sign:*', $result);
    }
}
