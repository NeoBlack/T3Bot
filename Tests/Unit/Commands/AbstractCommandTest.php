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
use \T3Bot\Slack\Message;
use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Commands\AbstractCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class AbstractCommandTest
 *
 * @package T3Bot\Tests\Unit\Commands
 */
class AbstractCommandTest extends BaseCommandTestCase
{
    /**
     * @test
     */
    public function ensureSendResponseHandlingForStringResponse()
    {
        /** @var Payload $payload */
        $payload = new Payload([
            'text' => 'test message',
            'channel' => '#fntest'
        ]);
        /** @var RealTimeClient $client */
        $client = $this->prophesize(RealTimeClient::class);

        $client->apiCall('chat.postMessage', [
            "unfurl_links" => false,
            "unfurl_media" => false,
            "parse" => "none",
            "text" => "this is a test string",
            "channel" => "#fntest",
            "as_user" => true
        ])->willReturn(true);

        /** @var AbstractCommand $stub */
        $stub = $this->getMockForAbstractClass(AbstractCommand::class, [$payload, $client->reveal()]);
        $stub->sendResponse('this is a test string');
    }

    /**
     * @test
     */
    public function ensureSendResponseHandlingForMessageResponse()
    {
        /** @var Payload $payload */
        $payload = new Payload([
            'text' => 'test message',
            'channel' => '#fntest'
        ]);
        /** @var RealTimeClient $client */
        $client = $this->prophesize(RealTimeClient::class);
        $client->postMessage(Argument::any())->willReturn(true);

        $message = new Message(['icon_emoji' => 'foo']);
        $attachment = new Message\Attachment(['title' => 'Test']);
        $attachment->setTitle('Test');
        $attachment->setTitleLink('http://www.google.de');
        $attachment->setText('Test');
        $attachment->setFallback('Test');
        $attachment->setAuthorName('Test');
        $attachment->setAuthorLink('http://www.google.de');
        $attachment->setAuthorIcon('foo');
        $attachment->setImageUrl('http://www.google.de');
        $attachment->setThumbUrl('http://www.google.de');

        $message->setText('Test');
        $message->addAttachment($attachment);

        /** @var AbstractCommand $stub */
        $stub = $this->getMockForAbstractClass(AbstractCommand::class, [$payload, $client->reveal()]);
        $stub->sendResponse($message);

        $this->assertEquals('foo', $message->getIconEmoji());
        $message->setIconEmoji('bar');
        $this->assertEquals('bar', $message->getIconEmoji());

        $message->setAttachments([$attachment]);
        $this->assertEquals([$attachment], $message->getAttachments());
    }

    /**
     * @test
     */
    public function ensureSendResponseHandlingForStringResponseWithUser()
    {
        $this->markTestSkipped('not implemented yet');
        // @TODO: this method should test the same stuff like ensureSendResponseHandlingForStringResponse
        // @TODO: but for a user (direct message, instead of channel message)
    }

    /**
     * @test
     */
    public function ensureSendResponseHandlingForMessageResponseWithUser()
    {
        $this->markTestSkipped('not implemented yet');
        // @TODO: this method should test the same stuff like ensureSendResponseHandlingForMessageResponse
        // @TODO: but for a user (direct message, instead of channel message)
    }
}