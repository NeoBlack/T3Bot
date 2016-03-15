<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

namespace T3Bot\Tests\Unit\Controller;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use T3Bot\Controller\WebHookController;
use T3Bot\Slack\Message;
use T3Bot\Tests\Unit\BaseTestCase;

/** @noinspection LongInheritanceChainInspection */
class WebHookControllerTest extends BaseTestCase
{
    /**
     *
     */
    public function setUp()
    {
        $GLOBALS['config']['webhook']['t3o-registrations'] = array(
            'securityToken' => 'SECURE_VALID_TOKEN',
            'channels' => array('#t3o-registrations')
        );
        $GLOBALS['config']['slack']['botAvatar'] = 'botty';
    }

    /**
     * @return array
     */
    public function validWebhookDataProvider()
    {
        return [
            'webhook' => [__DIR__ . '/../Fixtures/Valid/webhook.json'],
            'webhook_danger' => [__DIR__ . '/../Fixtures/Valid/webhook_danger.json'],
            'webhook_info' => [__DIR__ . '/../Fixtures/Valid/webhook_info.json'],
            'webhook_notice' => [__DIR__ . '/../Fixtures/Valid/webhook_notice.json'],
            'webhook_ok' => [__DIR__ . '/../Fixtures/Valid/webhook_ok.json'],
            'webhook_warning' => [__DIR__ . '/../Fixtures/Valid/webhook_warning.json']
        ];
    }

    /**
     * @test
     */
    public function processWebhookWithUnknownHook()
    {
        $controller = $this->getMock(WebHookController::class, ['postToSlack']);
        $controller
            ->expects(static::never())
            ->method('postToSlack');
        $controller->process('unknow', __DIR__ . '/../Fixtures/Valid/webhook.json');
    }

    /**
     * @test
     * @dataProvider validWebhookDataProvider
     */
    public function processWebhookWithValidJson($jsonFile)
    {
        $controller = $this->getMock(WebHookController::class, ['postToSlack']);
        $controller
            ->expects(static::once())
            ->method('postToSlack');
        $controller->process('t3o-registrations', $jsonFile);
    }

    /**
     * @test
     */
    public function processWebhookWithInvalidJson()
    {
        $controller = $this->getMock(WebHookController::class, ['postToSlack']);
        $controller
            ->expects(static::never())
            ->method('postToSlack');
        $controller->process('t3o-registrations', __DIR__ . '/../Fixtures/Invalid/webhook.json');
    }

    /**
     * @test
     */
    public function processChangeMergedWithValidJsonAddEntryToMessageQueue()
    {
        $controller = $this->getMock(WebHookController::class, ['addMessageToQueue']);
        $controller
            ->expects(static::once())
            ->method('addMessageToQueue');
        $controller->process('t3o-registrations', __DIR__ . '/../Fixtures/Valid/webhook.json');
    }

    /**
     * @test
     */
    public function addMessageToQueueCreatesEntryInDatabase()
    {
        $controller = $this->getMock(WebHookController::class);
        $testMessage = [
            'message' => 'addMessageToQueueCreatesEntryInDatabase-test',
            'test-id' => uniqid('addMessageToQueueCreatesEntryInDatabase-test', true),
        ];
        $result = json_encode($testMessage);
        $this->getDatabaseConnection()->delete('messages', ['message' => $result]);

        $this->invokeMethod($controller, 'addMessageToQueue', [$testMessage]);

        $records = $this->getDatabaseConnection()->fetchAll('SELECT * FROM messages WHERE message = ?', [$result]);

        static::assertGreaterThan(0, count($records));
        $this->getDatabaseConnection()->delete('messages', ['message' => $result]);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDatabaseConnection()
    {
        /* @noinspection PhpInternalEntityUsedInspection */
        $config = new Configuration();

        return DriverManager::getConnection($GLOBALS['config']['db'], $config);
    }
}
