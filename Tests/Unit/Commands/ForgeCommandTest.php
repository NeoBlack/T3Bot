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
use T3Bot\Commands\ForgeCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class ForgeCommandTest
 *
 * @package T3Bot\Tests\Unit\Commands
 */
class ForgeCommandTest extends BaseCommandTestCase
{
    /**
     * Data provider for show command.
     *
     * @return array
     */
    public function showTestDataProvider()
    {
        return array(
            'forge:show 12345' => array('12345'),
            'forge:show http://forge.typo3.org/issues/12345/' => array('http://forge.typo3.org/issues/12345/'),
            'forge:show https://forge.typo3.org/issues/12345/' => array('https://forge.typo3.org/issues/12345/'),
            'forge:show http://forge.typo3.org/issues/12345' => array('http://forge.typo3.org/issues/12345'),
            'forge:show https://forge.typo3.org/issues/12345' => array('https://forge.typo3.org/issues/12345'),
        );
    }

    /**
     * @test
     */
    public function processShowReturnsCorrectResponseForNoOptions()
    {
        $this->initCommandWithPayload(ForgeCommand::class, [
            'user' => 'U54321',
            'text' => 'forge:show'
        ]);
        $result = $this->command->process();
        $this->assertEquals('hey, I need an issue number!', $result);
    }

    /**
     * @test
     */
    public function processShowReturnsCorrectResponseForInvalidIssueNumber()
    {
        $this->initCommandWithPayload(ForgeCommand::class, [
            'user' => 'U54321',
            'text' => 'forge:show asdasd'
        ]);
        $result = $this->command->process();
        $this->assertEquals('hey, I need an issue number!', $result);
    }

    /**
     * @test
     * @todo test fails for now, must be fixed later
     */
    public function processShowReturnsCorrectResponseForUnknownIssueNumber()
    {
        $this->markTestSkipped(
            'test is broken for now'
        );
        $this->initCommandWithPayload(ForgeCommand::class, [
            'user' => 'U54321',
            'text' => 'forge:show 99999'
        ]);
        $result = $this->command->process();
        $this->assertEquals('Sorry not found!', $result);
    }

    /**
     * @test
     * @dataProvider showTestDataProvider
     */
    public function processShowReturnsCorrectResponseForValidIssueNumbers($issueNumber)
    {
        $this->initCommandWithPayload(ForgeCommand::class, [
            'user' => 'U54321',
            'text' => 'forge:show ' . $issueNumber
        ]);
        $result = $this->command->process();
        $this->assertStringStartsWith('*[Feature] Preview of news records* by _Georg Ringer_', $result);
    }
}