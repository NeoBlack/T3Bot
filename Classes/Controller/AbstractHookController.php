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
 * Class AbstractHookController.
 */
abstract class AbstractHookController
{
    /**
     * public method to start processing the request.
     *
     * @param string $hook
     * @param string $input
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    abstract public function process($hook, $input = 'php://input');

    /**
     * @param Message $payload
     * @param string  $channel
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function postToSlack(Message $payload, $channel)
    {
        $data = [];
        $data['unfurl_links'] = false;
        $data['unfurl_media'] = false;
        $data['parse'] = 'none';
        $data['text'] = $payload->getText();
        $data['channel'] = $channel;
        $attachments = $payload->getAttachments();
        if (count($attachments)) {
            $data['attachments'] = [];
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

    /**
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    protected function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === '' || (
            ($temp = strlen($haystack) - strlen($needle)) >= 0
            && strpos($haystack, $needle, $temp) !== false
        );
    }
}
