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
use T3Bot\Commands\ReviewCommand;
use T3Bot\Slack\Message;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class ReviewCommandTest
 *
 * @package T3Bot\Tests\Unit\Commands
 */
class ReviewCommandTest extends BaseCommandTestCase
{
    /**
     * Data provider for show command.
     *
     * @return array
     */
    public function showTestDataProvider()
    {
        return array(
            'review:show 12345' => array('12345'),
            'review:show http://review.typo3.org/#/c/12345/' => array('http://review.typo3.org/#/c/12345/'),
            'review:show https://review.typo3.org/#/c/12345/' => array('https://review.typo3.org/#/c/12345/'),
            'review:show http://review.typo3.org/#/c/12345/1' => array('http://review.typo3.org/#/c/12345/1'),
            'review:show https://review.typo3.org/#/c/12345/1' => array('https://review.typo3.org/#/c/12345/1'),
        );
    }

    /**
     * @test
     */
    public function ensureHelpCommandWorks()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:foo'
        ]);
        $result = $this->command->process();
        $this->assertStringStartsWith('*HELP*', $result);
    }

    /**
     * @test
     */
    public function processCountReturnsCorrectOutputForDefaultProject()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:count'
        ]);
        $result = $this->command->process();
        $expectedResult = '/There are currently \*([0-9]*)\* open reviews for project _Packages\/TYPO3.CMS_/';
        $this->assertRegExp($expectedResult, $result);
    }

    /**
     * @test
     */
    public function processRandomReturnsCorrectOutput()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:random'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $this->assertInstanceOf(Message::class, $result);
        $attachments = $result->getAttachments();
        /** @var Message\Attachment $attachment */
        foreach ($attachments as $attachment) {
            $this->assertNotEmpty($attachment->getTitle());
        }
    }

    /**
     * @test
     */
    public function processUserReturnsCorrectOutputForNoUser()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:user'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $this->assertEquals('hey, I need a username!', $result);
    }

    /**
     * @test
     */
    public function processUserReturnsCorrectOutputForValidUser()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:user neoblack'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $this->assertContains('*Here are the results for neoblack*:', $result);
    }

    /**
     * @test
     * @dataProvider showTestDataProvider
     */
    public function processShowReturnsCorrectOutputForValidRefIds($refId)
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:show ' . $refId
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $this->assertInstanceOf(Message::class, $result);
        $attachments = $result->getAttachments();
        /** @var Message\Attachment $attachment */
        foreach ($attachments as $attachment) {
            $this->assertEquals('[BUGFIX] Log route values if a route can\'t be resolved', $attachment->getTitle());
        }
    }

    /**
     * @test
     */
    public function processShowReturnsCorrectOutputForMultipleValidRefIds()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:show 12345 23456'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = '*[BUGFIX] Cast autoload and classAliasMap to Array* <https://review.typo3.org/23456|Review #23456 now>' . chr(10);
        $expectedString .= '*[BUGFIX] Log route values if a route can\'t be resolved* <https://review.typo3.org/12345|Review #12345 now>';
        $this->assertEquals($expectedString, $result);
    }

    /**
     * @test
     */
    public function processShowReturnsCorrectOutputForNoRefIds()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:show'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = 'hey, I need at least one change number!';
        $this->assertEquals($expectedString, $result);
    }

    /**
     * @test
     */
    public function processShowReturnsCorrectOutputForInvalidRefId()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:show x11111'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = 'hey, I need at least one change number!';
        $this->assertEquals($expectedString, $result);
    }

    /**
     * @test
     * @todo test fails for now, must be fixed later
     */
    public function processShowReturnsCorrectOutputForUnknownRefId()
    {
        $this->markTestSkipped(
            'test is broken for now'
        );
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:show 999999'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = 'hey, I need at least one change number!';
        $this->assertEquals($expectedString, $result);
    }

    /**
     * @test
     */
    public function processQueryReturnsCorrectOutputForNoQuery()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:query'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = 'hey, I need a query!';
        $this->assertEquals($expectedString, $result);
    }

    /**
     * @test
     */
    public function processQueryReturnsCorrectOutputForTestQuery()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:query test'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = '*Here are the results for test*:';
        $this->assertStringStartsWith($expectedString, $result);
    }

    /**
     * @test
     */
    public function processQueryReturnsCorrectOutputForValidQueryWithNoResults()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:query öäauieqd-asucc3ucbauiscaui-sd'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = 'öäauieqd-asucc3ucbauiscaui-sd not found, sorry!';
        $this->assertStringStartsWith($expectedString, $result);
    }

    /**
     * @test
     */
    public function processMergedReturnsCorrectOutputForNoDate()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:merged'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = 'hey, I need a date in the format YYYY-MM-DD!';
        $this->assertEquals($expectedString, $result);
    }

    /**
     * @test
     */
    public function processMergedReturnsCorrectOutputForInvalidDate()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:merged 01.01.2015'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = 'hey, I need a date in the format YYYY-MM-DD!';
        $this->assertEquals($expectedString, $result);
    }

    /**
     * @test
     */
    public function processMergedReturnsCorrectOutputForValidDate()
    {
        $this->initCommandWithPayload(ReviewCommand::class, [
            'user' => 'U54321',
            'text' => 'review:merged 2015-01-01'
        ]);
        /** @var Message $result */
        $result = $this->command->process();
        $expectedString = '/Good job folks, since 2015-01-01 you merged \*([0-9]*)\* patches into master/';
        $this->assertRegExp($expectedString, $result);
    }
}