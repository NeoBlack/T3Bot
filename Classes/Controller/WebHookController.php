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
     * @var array
     */
    protected $colorMap = [
        'info' => Message\Attachment::COLOR_INFO,
        'ok' => Message\Attachment::COLOR_GOOD,
        'warning' => Message\Attachment::COLOR_WARNING,
        'danger' => Message\Attachment::COLOR_DANGER,
        'notice' => Message\Attachment::COLOR_NOTICE,
    ];

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
        $entityBody = file_get_contents($input);
        $json = json_decode($entityBody);
        $hookConfiguration = $this->configuration['webhook'][$hook] ?? [];

        if (empty($hookConfiguration) || $hookConfiguration['securityToken'] !== $json->securityToken) {
            return;
        }
        $this->sendMessage($json, $hookConfiguration);
    }

    /**
     * @param \stdClass $json
     * @param array $hookConfiguration
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function sendMessage(\stdClass $json, array $hookConfiguration)
    {
        $color = $this->colorMap[$json->color] ?? $json->color;

        $message = new Message();
        $message->setText(' ');
        $attachment = new Message\Attachment();

        $attachment->setColor($color);
        $attachment->setTitle($json->title);
        $attachment->setText($json->text);
        $attachment->setFallback($json->text);
        $message->addAttachment($attachment);

        if (is_array($hookConfiguration['channels'])) {
            foreach ($hookConfiguration['channels'] as $channel) {
                $this->postToSlack($message, $channel);
            }
        }
    }
}
