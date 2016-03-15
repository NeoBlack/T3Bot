<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

namespace T3Bot\Controller;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Slack\Message\Attachment;
use T3Bot\Slack\Message;

/**
 * Class WebHookController.
 */
class WebHookController
{
    /**
     * public method to start processing the request.
     *
     * @param string $hook
     * @param string $input
     *
     * Input example:
     * {
     *   "securityToken": "a valid security token",
     *   "color": [info|ok|warning|danger|notice|#HEXCODE],
     *   "title": "Title of the message"
     *   "text": "Text of the message"
     * }
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function process($hook, $input = 'php://input')
    {
        if (empty($GLOBALS['config']['webhook'][$hook])) {
            return;
        }
        $hookConfiguration = $GLOBALS['config']['webhook'][$hook];

        $entityBody = file_get_contents($input);
        $json = json_decode($entityBody);

        if ($hookConfiguration['securityToken'] !== $json->securityToken) {
            return;
        }

        switch ($json->color) {
            case 'info':
                $color = Message\Attachment::COLOR_INFO;
                break;
            case 'ok':
                $color = Message\Attachment::COLOR_GOOD;
                break;
            case 'warning':
                $color = Message\Attachment::COLOR_WARNING;
                break;
            case 'danger':
                $color = Message\Attachment::COLOR_DANGER;
                break;
            case 'notice':
                $color = Message\Attachment::COLOR_NOTICE;
                break;
            default:
                $color = $json->color;
                break;
        }

        $message = new Message();
        $message->setText(' ');
        $attachment = new Message\Attachment();

        $attachment->setColor($color);
        $attachment->setTitle($json->title);
        $attachment->setText($json->text);
        $attachment->setFallback($json->text);
        $message->addAttachment($attachment);

        foreach ($hookConfiguration['channels'] as $channel) {
            $this->postToSlack($message, $channel);
        }
    }

    /**
     * @param Message $payload
     * @param string  $channel
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function postToSlack(Message $payload, $channel)
    {
        $data['unfurl_links'] = false;
        $data['unfurl_media'] = false;
        $data['parse'] = 'none';
        $data['text'] = $payload->getText();
        $data['channel'] = $channel;
        $attachments = $payload->getAttachments();
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
        if (!empty($GLOBALS['config']['slack']['botAvatar'])) {
            /* @noinspection PhpUndefinedFieldInspection */
            $data['icon_emoji'] = $GLOBALS['config']['slack']['botAvatar'];
        }

        // since the bot is a real bot, we can't push directly to slack
        // lets put the message into our messages pool
        $this->addMessageToQueue($data);
    }

    /**
     * @param array $data
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function addMessageToQueue(array $data)
    {
        /* @noinspection PhpInternalEntityUsedInspection */
        $config = new Configuration();
        $db = DriverManager::getConnection($GLOBALS['config']['db'], $config);
        $db->insert('messages', ['message' => json_encode($data)]);
    }
}
