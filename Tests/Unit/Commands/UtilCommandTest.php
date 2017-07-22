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

use T3Bot\Commands\UtilCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class UtilCommandTest.
 */

/** @noinspection LongInheritanceChainInspection */
class UtilCommandTest extends BaseCommandTestCase
{
    /**
     * @test
     */
    public function processCoinReturnsCorrectResponseForNoOptions()
    {
        $this->initCommandWithPayload(UtilCommand::class, [
            'user' => 'U54321',
            'text' => 'util:coin',
        ]);
        $result = $this->command->process();
        static::assertEquals('*Botty says:* _A complicated decision ..._', $result);
    }

    /**
     * @test
     */
    public function processCoinReturnsCorrectResponseForOneOption()
    {
        $this->initCommandWithPayload(UtilCommand::class, [
            'user' => 'U54321',
            'text' => 'util:coin a',
        ]);
        $result = $this->command->process();
        static::assertEquals('*Botty says:* _A complicated decision ..._', $result);
    }

    /**
     * @test
     */
    public function processCoinReturnsCorrectResponseForTwoOptions()
    {
        $this->initCommandWithPayload(UtilCommand::class, [
            'user' => 'U54321',
            'text' => 'util:coin a, b',
        ]);
        $result = $this->command->process();
        static::assertContains($result, ['*Botty says:* _a_', '*Botty says:* _b_']);
    }

    /**
     * @test
     */
    public function processCoinReturnsCorrectResponseForThreeOptions()
    {
        $this->initCommandWithPayload(UtilCommand::class, [
            'user' => 'U54321',
            'text' => 'util:coin a, b, c',
        ]);
        $result = $this->command->process();
        static::assertContains($result, ['*Botty says:* _a_', '*Botty says:* _b_', '*Botty says:* _c_']);
    }

    /**
     * @test
     */
    public function processCoinReturnsCorrectResponseForTwoIdenticalOptions()
    {
        $this->initCommandWithPayload(UtilCommand::class, [
            'user' => 'U54321',
            'text' => 'util:coin a, a',
        ]);
        $result = $this->command->process();
        static::assertEquals('*Botty says:* _it is undecidable ..._', $result);
    }
}
