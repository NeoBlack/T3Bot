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

use T3Bot\Commands\ForgeCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class ForgeCommandTest.
 */
class ForgeCommandTest extends BaseCommandTestCase
{
    /**
     * Data provider for show command.
     *
     * @return array
     */
    public function showTestDataProvider() : array
    {
        return [
            'forge:show 23456' => ['23456'],
            'forge:show http://forge.typo3.org/issues/23456/' => ['http://forge.typo3.org/issues/23456/'],
            'forge:show https://forge.typo3.org/issues/23456/' => ['https://forge.typo3.org/issues/23456/'],
            'forge:show http://forge.typo3.org/issues/23456' => ['http://forge.typo3.org/issues/23456'],
            'forge:show https://forge.typo3.org/issues/23456' => ['https://forge.typo3.org/issues/23456'],
        ];
    }

    /**
     * @test
     */
    public function processShowReturnsCorrectResponseForNoOptions()
    {
        $this->initCommandWithPayload(ForgeCommand::class, [
            'user' => 'U54321',
            'text' => 'forge:show',
        ]);
        $result = $this->command->process();
        static::assertEquals('hey, I need an issue number!', $result);
    }

    /**
     * @test
     */
    public function processShowReturnsCorrectResponseForInvalidIssueNumber()
    {
        $this->initCommandWithPayload(ForgeCommand::class, [
            'user' => 'U54321',
            'text' => 'forge:show asdasd',
        ]);
        $result = $this->command->process();
        static::assertEquals('hey, I need an issue number!', $result);
    }

    /**
     * @test
     */
    public function processShowReturnsCorrectResponseForUnknownIssueNumber()
    {
        $this->initCommandWithPayload(ForgeCommand::class, [
            'user' => 'U54321',
            'text' => 'forge:show 99999',
        ]);
        $result = $this->command->process();
        static::assertEquals('Sorry not found!', $result);
    }

    /**
     * @test
     * @dataProvider showTestDataProvider
     *
     * @param string $issueNumber
     */
    public function processShowReturnsCorrectResponseForValidIssueNumbers($issueNumber)
    {
        $this->initCommandWithPayload(ForgeCommand::class, [
            'user' => 'U54321',
            'text' => 'forge:show ' . $issueNumber,
        ]);
        $result = $this->command->process();

        static::assertStringStartsWith('*[Bug] Cannot edit media links with IE8* by _Michael Gotzen_', $result);
    }

    /**
     * @test
     */
    public function processShowReturnsCorrectResponseForValidIssueNumberInclusiveCategory()
    {
        $this->initCommandWithPayload(ForgeCommand::class, [
            'user' => 'U54321',
            'text' => 'forge:show 3097',
        ]);
        $result = $this->command->process();

        static::assertContains('Category: *t3editor*', $result);
    }
}
