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
use T3Bot\Slack\Message;
use T3Bot\Traits\SlackTrait;

/**
 * Class AbstractHookController.
 */
abstract class AbstractHookController
{
    use SlackTrait;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * GerritHookController constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        $this->configuration = $configuration;
    }

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
        $data = $this->getBaseDataArray($payload->getText(), $channel);
        $attachments = $payload->getAttachments();
        if (count($attachments)) {
            $data['attachments'] = [];
            foreach ($attachments as $attachment) {
                $data['attachments'][] = $this->buildAttachment($attachment);
            }
        }
        if (!empty($this->configuration['slack']['botAvatar'])) {
            $data['icon_emoji'] = $this->configuration['slack']['botAvatar'];
        }
        $this->addMessageToQueue($data);
    }

    /**
     * @param array $data
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function addMessageToQueue(array $data)
    {
        $config = new Configuration();
        $db = DriverManager::getConnection($this->configuration['db'], $config);
        $db->insert('messages', ['message' => json_encode($data)]);
    }

    /**
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    protected function endsWith($haystack, $needle) : bool
    {
        // search forward starting from end minus needle length characters
        return $needle === '' || (
            ($temp = strlen($haystack) - strlen($needle)) >= 0
            && strpos($haystack, $needle, $temp) !== false
        );
    }
}
