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

use Prophecy\Argument;
use T3Bot\Commands\BeerCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class BeerCommandTest
 *
 * @package T3Bot\Tests\Unit\Commands
 */
class BeerCommandTest extends BaseCommandTestCase
{
    /**
     * @test
     */
    public function processForReturnsCorrectResponseForWrongUsername()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U54321',
            'text' => 'beer:for max'
        ]);
        $result = $this->command->process();
        $this->assertEquals('*Sorry, a username must start with a @-sign:*', $result);
    }

    /**
     * @test
     */
    public function processForReturnsCorrectResponseForCorrectUsername()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:for <@U23456>'
        ]);
        $result = $this->command->process();
        $this->assertStringStartsWith('Yeah, one more :t3beer: for <@U23456>', $result);
    }

    /**
     * @test
     */
    public function processAllReturnsCorrectResponse()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:all'
        ]);
        $result = $this->command->process();
        $this->assertRegExp('/Yeah, ([0-9]*) :t3beer: spend to all people/', $result);
    }

    /**
     * @test
     */
    public function processTop10ReturnsCorrectResponse()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:top10'
        ]);
        $result = $this->command->process();
        $this->assertStringStartsWith('*Yeah, here are the TOP 10*', $result);
    }

    /**
     * @test
     */
    public function processStatsReturnsCorrectResponseForCorrectUsername()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:stats <@U23456>'
        ]);
        $result = $this->command->process();
        $this->assertRegExp('/<@U23456> has received ([0-9]*) :t3beer: so far/', $result);
    }

    /**
     * @test
     */
    public function processStatsReturnsCorrectResponseForinvalidUsername()
    {
        $this->initCommandWithPayload(BeerCommand::class, [
            'user' => 'U12345',
            'text' => 'beer:stats foo'
        ]);
        $result = $this->command->process();
        $this->assertEquals('*Sorry, a username must start with a @-sign:*', $result);
    }
}