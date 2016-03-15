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

use T3Bot\Slack\Message;

/**
 * Class WebHookController.
 */
class WebHookController extends AbstractHookController
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
}
