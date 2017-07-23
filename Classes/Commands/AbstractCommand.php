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
use Slack\DataObject;
use Slack\Message\Attachment;
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
        $text = '';

        $message = new Message();
        $attachment = new Message\Attachment();
        switch ($this->configuration['projectPhase']) {
            case self::PROJECT_PHASE_STABILISATION:
                $attachment->setColor(Message\Attachment::COLOR_WARNING);
                $attachment->setPretext(':warning: *stabilisation phase*');
                break;
            case self::PROJECT_PHASE_SOFT_FREEZE:
                $attachment->setColor(Message\Attachment::COLOR_DANGER);
                $attachment->setPretext(':no_entry: *soft merge freeze*');
                break;
            case self::PROJECT_PHASE_CODE_FREEZE:
                $attachment->setColor(Message\Attachment::COLOR_DANGER);
                $attachment->setPretext(':no_entry: *merge freeze*');
                break;
            case self::PROJECT_PHASE_FEATURE_FREEZE:
                $attachment->setColor(Message\Attachment::COLOR_DANGER);
                $attachment->setPretext(':no_entry: *FEATURE FREEZE*');
                break;
            case self::PROJECT_PHASE_DEVELOPMENT:
            default:
                $attachment->setColor(Message\Attachment::COLOR_NOTICE);
                break;
        }
        $attachment->setTitle($item->subject);
        $attachment->setTitleLink('https://review.typo3.org/' . $item->_number);

        $text .= 'Branch: ' . $this->bold($branch) . ' | :calendar: ' . $this->bold($created)
            . ' | ID: ' . $this->bold($item->_number) . chr(10);
        $text .= '<https://review.typo3.org/' . $item->_number . '|:arrow_right: Goto Review>';

        $attachment->setText($text);
        $attachment->setFallback($text);
        $message->setText(' ');
        $message->addAttachment($attachment);

        return $message;
    }

    /**
     * @param string $text
     * @param string $channel
     *
     * @return array
     */
    protected function getBaseDataArray(string $text, string $channel) : array
    {
        $data = [];
        $data['unfurl_links'] = false;
        $data['unfurl_media'] = false;
        $data['parse'] = 'none';
        $data['text'] = $text;
        $data['channel'] = $channel;
        return $data;
    }

    /**
     * @param Message\Attachment $attachment
     *
     * @return DataObject
     */
    protected function buildAttachment(Message\Attachment $attachment) : DataObject
    {
        return Attachment::fromData([
            'title' => $attachment->getTitle(),
            'title_link' => $attachment->getTitleLink(),
            'text' => $attachment->getText(),
            'fallback' => $attachment->getFallback(),
            'color' => $attachment->getColor(),
            'pretext' => $attachment->getPretext(),
            'author_name' => $attachment->getAuthorName(),
            'author_icon' => $attachment->getAuthorIcon(),
            'author_link' => $attachment->getAuthorLink(),
            'image_url' => $attachment->getImageUrl(),
            'thumb_url' => $attachment->getThumbUrl(),
        ]);
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
            }
            foreach ($attachments as $attachment) {
                $data['attachments'][] = $this->buildAttachment($attachment);
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
