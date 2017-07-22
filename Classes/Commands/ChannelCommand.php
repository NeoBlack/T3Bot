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

use T3Bot\Slack\Message;

/**
 * Class ChannelCommand.
 */
class ChannelCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $commandName = 'channel';

    /**
     * @var array
     */
    protected $helpCommands = [
        'help' => 'shows this help'
    ];

    /**
     * @return bool|string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function process()
    {
        return false;
    }

    /**
     * @param array $data
     * @example $data:
     * [
     *  "type": "channel_created",
     *  "channel": {
     *      "id": "C024BE91L",
     *      "name": "fun",
     *      "created": 1360782804,
     *      "creator": "U024BE7LH"
     *  }
     * ]
     */
    public function processChannelCreated(array $data)
    {
        $channel = $data['channel'];
        $message = new Message();
        $message->setText(sprintf(
            '<@%s> opened channel #%s, join it <#%s>',
            $channel['creator'],
            $channel['name'],
            $channel['id']
        ));
        $this->sendResponse($message, null, $GLOBALS['config']['slack']['channels']['channelCreated']);
    }
}
