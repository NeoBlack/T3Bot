<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Commands;

use /* @noinspection PhpInternalEntityUsedInspection */ Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Slack\Message;
use T3Bot\Traits\ForgerTrait;
use T3Bot\Traits\GerritTrait;
use T3Bot\Traits\SlackTrait;

/**
 * Class AbstractCommand.
 *
 * @property string commandName
 * @property array helpCommands
 */
abstract class AbstractCommand
{
    use SlackTrait, ForgerTrait, GerritTrait;

    const PROJECT_PHASE_DEVELOPMENT = 'development';
    const PROJECT_PHASE_STABILISATION = 'stabilisation';
    const PROJECT_PHASE_SOFT_FREEZE = 'soft_freeze';
    const PROJECT_PHASE_CODE_FREEZE = 'code_freeze';
    const PROJECT_PHASE_FEATURE_FREEZE = 'feature_freeze';

    /**
     * @var string
     */
    protected $commandName;

    /**
     * @var array
     */
    protected $helpCommands = [];

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var Payload
     */
    protected $payload;

    /**
     * @var RealTimeClient
     */
    protected $client;

    /**
     * @var array|null
     */
    protected $configuration;

    /**
     * @var Connection
     */
    protected $databaseConnection;

    /**
     * @var array
     */
    protected $colors = [
        self::PROJECT_PHASE_STABILISATION => Message\Attachment::COLOR_WARNING,
        self::PROJECT_PHASE_SOFT_FREEZE => Message\Attachment::COLOR_DANGER,
        self::PROJECT_PHASE_CODE_FREEZE => Message\Attachment::COLOR_DANGER,
        self::PROJECT_PHASE_FEATURE_FREEZE => Message\Attachment::COLOR_DANGER,
    ];

    /**
     * @var array
     */
    protected $preTexts = [
        self::PROJECT_PHASE_STABILISATION => ':warning: *stabilisation phase*',
        self::PROJECT_PHASE_SOFT_FREEZE => ':no_entry: *soft merge freeze*',
        self::PROJECT_PHASE_CODE_FREEZE => ':no_entry: *merge freeze*',
        self::PROJECT_PHASE_FEATURE_FREEZE => ':no_entry: *FEATURE FREEZE*',
    ];

    /**
     * AbstractCommand constructor.
     *
     * @param Payload $payload
     * @param RealTimeClient $client
     * @param array|null $configuration
     */
    public function __construct(Payload $payload, RealTimeClient $client, array $configuration = null)
    {
        $this->payload = $payload;
        $this->client = $client;
        $this->configuration = $configuration;
    }

    /**
     *
     */
    public function process()
    {
        $commandParts = explode(':', $this->payload->getData()['text']);
        $params = [];
        if (!empty($commandParts[1])) {
            array_shift($commandParts);
            $params = explode(' ', preg_replace('/\s+/', ' ', implode(':', $commandParts)));
        }

        $command = !empty($params[0]) ? $params[0] : 'help';
        $this->params = $params;
        $method = 'process' . ucfirst(strtolower($command));
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return $this->getHelp();
    }

    /**
     * @param Message|string $messageToSent
     * @param string $user
     * @param string $channel the channel id
     */
    public function sendResponse($messageToSent, $user = null, $channel = null)
    {
        if ($user !== null) {
            $this->client->apiCall('im.open', ['user' => $user])
                ->then(function (Payload $response) use ($messageToSent) {
                    $channel = $response->getData()['channel']['id'];
                    $this->postMessage($messageToSent, $channel);
                });
        } else {
            $channel = $channel ?? $this->payload->getData()['channel'];
            $this->postMessage($messageToSent, $channel);
        }
    }

    /**
     * generate help.
     *
     * @return string
     */
    public function getHelp() : string
    {
        $result = '*HELP*' . chr(10);
        foreach ($this->helpCommands as $command => $helpText) {
            $result .= "*{$this->commandName}:{$command}*: {$helpText}" . chr(10);
        }

        return $result;
    }

    /**
     * build a review message.
     *
     * @param \stdClass $item the review item
     *
     * @return Message
     */
    protected function buildReviewMessage($item) : Message
    {
        $created = substr($item->created, 0, 19);
        $branch = $item->branch;

        $color = $this->colors[$this->configuration['projectPhase']] ?? Message\Attachment::COLOR_NOTICE;
        $preText = $this->preTexts[$this->configuration['projectPhase']] ?? '';

        $message = new Message();
        $attachment = new Message\Attachment();
        $attachment->setColor($color);
        $attachment->setPretext($preText);
        $attachment->setTitle($item->subject);
        $attachment->setTitleLink('https://review.typo3.org/' . $item->_number);

        $text = 'Branch: ' . $this->bold($branch) . ' | :calendar: ' . $this->bold($created)
            . ' | ID: ' . $this->bold($item->_number) . chr(10);
        $text .= '<https://review.typo3.org/' . $item->_number . '|:arrow_right: Goto Review>';

        $attachment->setText($text);
        $attachment->setFallback($text);
        $message->setText(' ');
        $message->addAttachment($attachment);

        return $message;
    }

    /**
     * @param Message|string $messageToSent
     * @param string $channel
     */
    protected function postMessage($messageToSent, string $channel)
    {
        if ($messageToSent instanceof Message) {
            $data = $this->getBaseDataArray($messageToSent->getText(), $channel);
            $attachments = $messageToSent->getAttachments();
            if (count($attachments)) {
                $data['attachments'] = [];
                foreach ($attachments as $attachment) {
                    $data['attachments'][] = $this->buildAttachment($attachment);
                }
            }
            $message = new \Slack\Message\Message($this->client, $data);
            $this->client->postMessage($message);
        } elseif (is_string($messageToSent)) {
            $data = $this->getBaseDataArray($messageToSent, $channel);
            $data['as_user'] = true;
            $this->client->apiCall('chat.postMessage', $data);
        }
    }

    /**
     * @return Connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getDatabaseConnection() : Connection
    {
        if ($this->databaseConnection === null) {
            $config = new Configuration();
            $this->databaseConnection = DriverManager::getConnection($this->configuration['db'], $config);
        }
        return $this->databaseConnection;
    }
}
