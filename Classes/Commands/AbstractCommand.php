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

use /** @noinspection PhpInternalEntityUsedInspection */ Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use MyProject\Proxies\__CG__\stdClass;
use Slack\Message\Attachment;
use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Slack\Message;
use T3Bot\Traits\ForgerTrait;
use T3Bot\Traits\GerritTrait;
use T3Bot\Traits\SlackTrait;

/**
 * Class AbstractCommand.
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
     * @var
     */
    protected $commandName;

    /**
     * @var array
     */
    protected $helpCommands = array();

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @var Payload
     */
    protected $payload;

    /**
     * @var RealTimeClient
     */
    protected $client;

    /**
     * AbstractCommand constructor.
     *
     * @param Payload $payload
     * @param RealTimeClient $client
     */
    public function __construct(Payload $payload, RealTimeClient $client)
    {
        $this->payload = $payload;
        $this->client = $client;
    }

    /**
     *
     */
    public function process()
    {
        $commandParts = explode(':', $this->payload->getData()['text']);
        $params = array();
        if (!empty($commandParts[1])) {
            array_shift($commandParts);
            $params = explode(' ', preg_replace('/\s+/', ' ', implode(':', $commandParts)));
        }

        $command = !empty($params[0]) ? $params[0] : 'help';
        $this->params = $params;
        $method = 'process'.ucfirst(strtolower($command));
        if (method_exists($this, $method)) {
            return $this->{$method}();
        } else {
            return $this->getHelp();
        }
    }

    /**
     * @param \T3Bot\Slack\Message|string $messageToSent
     * @param string $user
     */
    public function sendResponse($messageToSent, $user = null)
    {
        if ($user !== null) {
            $this->client->apiCall('im.open', ['user' => $user])
                ->then(function (Payload $response) use ($messageToSent) {
                    $channel = $response->getData()['channel']['id'];
                    if ($messageToSent instanceof Message) {
                        $data['unfurl_links'] = false;
                        $data['unfurl_media'] = false;
                        $data['parse'] = 'none';
                        $data['text'] = $messageToSent->getText();
                        $data['channel'] = $channel;
                        $attachments = $messageToSent->getAttachments();
                        if (count($attachments)) {
                            $data['attachments'] = array();
                        }
                        /** @var \T3Bot\Slack\Message\Attachment $attachment */
                        foreach ($attachments as $attachment) {
                            $data['attachments'][] = Attachment::fromData([
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
                        $message = new \Slack\Message\Message($this->client, $data);
                        $this->client->postMessage($message);
                    } elseif (is_string($messageToSent)) {
                        $data['unfurl_links'] = false;
                        $data['unfurl_media'] = false;
                        $data['parse'] = 'none';
                        $data['text'] = $messageToSent;
                        $data['channel'] = $channel;
                        $data['as_user'] = true;
                        $this->client->apiCall('chat.postMessage', $data);
                    }
                });
        } else {
            $channel = $this->payload->getData()['channel'];
            if ($messageToSent instanceof Message) {
                $data['unfurl_links'] = false;
                $data['unfurl_media'] = false;
                $data['parse'] = 'none';
                $data['text'] = $messageToSent->getText();
                $data['channel'] = $channel;
                $attachments = $messageToSent->getAttachments();
                if (count($attachments)) {
                    $data['attachments'] = array();
                }
                /** @var \T3Bot\Slack\Message\Attachment $attachment */
                foreach ($attachments as $attachment) {
                    $data['attachments'][] = Attachment::fromData([
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
                $message = new \Slack\Message\Message($this->client, $data);
                $this->client->postMessage($message);
            } elseif (is_string($messageToSent)) {
                $data['unfurl_links'] = false;
                $data['unfurl_media'] = false;
                $data['parse'] = 'none';
                $data['text'] = $messageToSent;
                $data['channel'] = $channel;
                $data['as_user'] = true;
                $this->client->apiCall('chat.postMessage', $data);
            }
        }
    }

    /**
     * generate help.
     *
     * @return string
     */
    public function getHelp()
    {
        $result = "*HELP*\n";
        foreach ($this->helpCommands as $command => $helpText) {
            $result .= "*{$this->commandName}:{$command}*: {$helpText} \n";
        }

        return $result;
    }

    /**
     * build a review message.
     *
     * @param stdClass $item the review item
     *
     * @return Message
     */
    protected function buildReviewMessage($item)
    {
        $created = substr($item->created, 0, 19);
        $branch = $item->branch;
        $text = '';

        $message = new Message();
        $attachment = new Message\Attachment();
        switch ($GLOBALS['config']['projectPhase']) {
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
        $attachment->setTitleLink('https://review.typo3.org/'.$item->_number);

        $text .= 'Branch: ' . $this->bold($branch) . ' | :calendar: ' . $this->bold($created)
            . ' | ID: ' . $this->bold($item->_number) . "\n";
        $text .= '<https://review.typo3.org/'.$item->_number.'|:arrow_right: Goto Review>';

        $attachment->setText($text);
        $attachment->setFallback($text);
        $message->setText(' ');
        $message->addAttachment($attachment);

        return $message;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getDatabaseConnection()
    {
        if (empty($GLOBALS['DB'])) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $config = new Configuration();
            $GLOBALS['DB'] = DriverManager::getConnection($GLOBALS['config']['db'], $config);
        }
        return $GLOBALS['DB'];
    }
}
