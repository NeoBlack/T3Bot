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

use /** @noinspection PhpInternalEntityUsedInspection */
    Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Slack\Message\Attachment;
use Slack\Payload;
use Slack\RealTimeClient;
use T3Bot\Slack\Message;

/**
 * Class AbstractCommand.
 */
abstract class AbstractCommand
{
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

        $this->params = $params;
        $command = isset($this->params[0]) ? $this->params[0] : 'help';
        $method = 'process'.ucfirst(strtolower($command));
        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method));
        } else {
            return $this->getHelp();
        }
    }

    /**
     * @param \T3Bot\Slack\Message|string $response
     */
    public function sendResponse($response)
    {
        if ($response instanceof Message) {
            $data['unfurl_links'] = false;
            $data['unfurl_media'] = false;
            $data['parse'] = 'none';
            $data['text'] = $response->getText();
            $data['channel'] = $this->payload->getData()['channel'];
            $attachments = $response->getAttachments();
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
                    'fields' => $attachment->getFields(),
                    'author_name' => $attachment->getAuthorName(),
                    'author_icon' => $attachment->getAuthorIcon(),
                    'author_link' => $attachment->getAuthorLink(),
                    'image_url' => $attachment->getImageUrl(),
                    'thumb_url' => $attachment->getThumbUrl(),
                ]);
            }
            $message = new \Slack\Message\Message($this->client, $data);
            $this->client->postMessage($message);
        } elseif (is_string($response)) {
            $data['unfurl_links'] = false;
            $data['unfurl_media'] = false;
            $data['parse'] = 'none';
            $data['text'] = $response;
            $data['channel'] = $this->payload->getData()['channel'];
            $data['as_user'] = true;
            $this->client->apiCall('chat.postMessage', $data);
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
     * @param object $item the review item
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
     * build a review line.
     *
     * @param object $item the review item
     *
     * @return string
     */
    protected function buildReviewLine($item)
    {
        return $this->bold($item->subject) . ' <https://review.typo3.org/' . $item->_number
        . '|Review #' . $item->_number . ' now>';
    }

    /**
     * make text bold.
     *
     * @param $string
     *
     * @return string
     */
    protected function bold($string)
    {
        return '*'.$string.'*';
    }

    /**
     * make text italic.
     *
     * @param $string
     *
     * @return string
     */
    protected function italic($string)
    {
        return '_'.$string.'_';
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
