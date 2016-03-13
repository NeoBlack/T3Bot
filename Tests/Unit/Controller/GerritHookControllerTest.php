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
use T3Bot\Controller\GerritHookController;
use T3Bot\Slack\Message;
use T3Bot\Tests\Unit\BaseTestCase;

class GerritHookControllerTest extends BaseTestCase
{
    public function setUp()
    {
        $GLOBALS['config']['gerrit']['webhookToken'] = 'unit-test-token';
        $GLOBALS['config']['gerrit']['change-merged'] = ['channels' => ['#change-merged']];
        $GLOBALS['config']['gerrit']['rst-merged'] = ['channels' => ['#rst-channel']];
        $GLOBALS['config']['gerrit']['patchset-created'] = ['channels' => ['#atchset-created']];
        $GLOBALS['config']['slack']['botAvatar'] = 'botty';
    }

    /**
     * @test
     */
    public function processChangeMergedWithValidJson()
    {
        $controller = $this->getMock(GerritHookController::class, ['postToSlack']);
        $controller
            ->expects($this->once())
            ->method('postToSlack');
        $controller->process('change-merged', __DIR__.'/../Fixtures/Valid/change-merged.json');
    }

    /**
     * @test
     */
    public function processChangeMergedWithAddedRstFile()
    {
        $mergeChannel = $GLOBALS['config']['gerrit']['change-merged']['channels'];
        $GLOBALS['config']['gerrit']['change-merged']['channels'] = [];
        $controller = $this->getMock(GerritHookController::class, ['postToSlack']);
        $controller
            ->expects($this->any())
            ->method('postToSlack')
            ->with(
                $this->isInstanceOf(Message::class),
                $this->equalTo('#rst-channel')
            );
        $controller->process('change-merged', __DIR__.'/../Fixtures/Valid/change-merged-with-added-rst.json');
        $GLOBALS['config']['gerrit']['change-merged']['channels'] = $mergeChannel;
    }

    /**
     * @test
     */
    public function processChangeMergedWithDeletedRstFile()
    {
        $mergeChannel = $GLOBALS['config']['gerrit']['change-merged']['channels'];
        $GLOBALS['config']['gerrit']['change-merged']['channels'] = [];
        $controller = $this->getMock(GerritHookController::class, ['postToSlack']);
        $controller
            ->expects($this->any())
            ->method('postToSlack')
            ->with(
                $this->isInstanceOf(Message::class),
                $this->equalTo('#rst-channel')
            );
        $controller->process('change-merged', __DIR__.'/../Fixtures/Valid/change-merged-with-deleted-rst.json');
        $GLOBALS['config']['gerrit']['change-merged']['channels'] = $mergeChannel;
    }

    /**
     * @test
     */
    public function processChangeMergedWithChangedRstFile()
    {
        $mergeChannel = $GLOBALS['config']['gerrit']['change-merged']['channels'];
        $GLOBALS['config']['gerrit']['change-merged']['channels'] = [];
        $controller = $this->getMock(GerritHookController::class, ['postToSlack']);
        $controller
            ->expects($this->any())
            ->method('postToSlack')
            ->with(
                $this->isInstanceOf(Message::class),
                $this->equalTo('#rst-channel')
            );
        $controller->process('change-merged', __DIR__.'/../Fixtures/Valid/change-merged-with-changed-rst.json');
        $GLOBALS['config']['gerrit']['change-merged']['channels'] = $mergeChannel;
    }

    /**
     * @test
     */
    public function processPatchsetCreatedWithValidJson()
    {
        $controller = $this->getMock(GerritHookController::class, ['postToSlack']);
        $controller
            ->expects($this->once())
            ->method('postToSlack');
        $controller->process('patchset-created', __DIR__.'/../Fixtures/Valid/patchset-created.json');
    }

    /**
     * @test
     */
    public function processChangeMergedWithInvalidJson()
    {
        $controller = $this->getMock(GerritHookController::class, ['postToSlack']);
        $controller
            ->expects($this->never())
            ->method('postToSlack');
        $controller->process('change-merged', __DIR__.'/../Fixtures/Invalid/change-merged.json');
    }

    /**
     * @test
     */
    public function processPatchsetCreatedWithInvalidJson()
    {
        $controller = $this->getMock(GerritHookController::class, ['postToSlack']);
        $controller
            ->expects($this->never())
            ->method('postToSlack');
        $controller->process('patchset-created', __DIR__.'/../Fixtures/Invalid/patchset-created.json');
    }

    /**
     * @test
     */
    public function processChangeMergedWithInvalidToken()
    {
        $controller = $this->getMock(GerritHookController::class, ['postToSlack']);
        $controller
            ->expects($this->never())
            ->method('postToSlack');
        $controller->process('change-merged', __DIR__.'/../Fixtures/Invalid/change-merged-invalid-token.json');
    }

    /**
     * @test
     */
    public function processPatchsetCreatedWithInvalidToken()
    {
        $controller = $this->getMock(GerritHookController::class, ['postToSlack']);
        $controller
            ->expects($this->never())
            ->method('postToSlack');
        $controller->process('patchset-created', __DIR__.'/../Fixtures/Invalid/patchset-created-invalid-token.json');
    }

    /**
     * @test
     */
    public function processChangeMergedWithValidJsonAddEntryToMessageQueue()
    {
        $controller = $this->getMock(GerritHookController::class, ['addMessageToQueue']);
        $controller
            ->expects($this->once())
            ->method('addMessageToQueue');
        $controller->process('change-merged', __DIR__.'/../Fixtures/Valid/change-merged.json');
    }

    /**
     * @test
     */
    public function processPatchsetCreatedWithValidJsonAddEntryToMessageQueue()
    {
        $controller = $this->getMock(GerritHookController::class, ['addMessageToQueue']);
        $controller
            ->expects($this->once())
            ->method('addMessageToQueue');
        $controller->process('patchset-created', __DIR__.'/../Fixtures/Valid/patchset-created.json');
    }

    /**
     * @test
     */
    public function addMessageToQueueCreatesEntryInDatabase()
    {
        $controller = $this->getMock(GerritHookController::class);
        $testMessage = [
            'message' => 'addMessageToQueueCreatesEntryInDatabase-test',
            'test-id' => uniqid('addMessageToQueueCreatesEntryInDatabase-test'),
        ];
        $result = json_encode($testMessage);
        $this->getDatabaseConnection()->delete('messages', ['message' => $result]);

        $this->invokeMethod($controller, 'addMessageToQueue', [$testMessage]);

        $records = $this->getDatabaseConnection()->fetchAll('SELECT * FROM messages WHERE message = ?', [$result]);

        $this->assertGreaterThan(0, count($records));
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
