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

/**
 * Class TellCommand.
 */
class TellCommand extends AbstractCommand
{
    const PRESENCE_ACTIVE = 'active';
    const PRESENCE_AWAY = 'away';

    /**
     * @var string
     */
    protected $commandName = 'tell';

    /**
     * @var array
     */
    protected $helpCommands = [
        'help' => 'shows this help',
        'tell [@to-user] about review:[Gerrit-ID]' => 'tell the target user about the given review',
        'tell [@to-user] about forge:[Issue-ID]' => 'tell the target user about the given forge issue',
        'tell [@to-user] [your message]' => 'tell the target user your message',
    ];

    /**
     * @return bool|string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function process()
    {
        $message = $this->payload->getData()['text'];
        $this->params = array_map('trim', explode(' ', $message));
        $result = false;
        if ($this->params[0] === 'tell') {
            $result = $this->processTell();
        }

        return $result;
    }

    /**
     * @param string $user
     * @param string $presence
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function processPresenceChange($user, $presence)
    {
        if ($presence === self::PRESENCE_ACTIVE) {
            $notifications = $this->getDatabaseConnection()
                ->fetchAll(
                    'SELECT * FROM notifications WHERE to_user = ? AND delivered = ?',
                    [$user, '0000-00-00 00:00:00']
                );
            foreach ($notifications as $notification) {
                if (strpos($notification['message'], 'review:') === 0) {
                    $parts = explode(':', $notification['message']);
                    $refId = (int) trim($parts[1]);
                    $result = $this->queryGerrit('change:'.$refId);
                    $msg = '*Hi <@'.$user.'>, <@'.$notification['from_user'].'>'
                        .' ask you to look at this patch:*';

                    if (is_array($result)) {
                        foreach ($result as $item) {
                            if ((int) $item->_number === $refId) {
                                $message = $this->buildReviewMessage($item);
                                $message->setText($msg);
                                $this->sendResponse($message, $user);
                            }
                        }
                    }
                } elseif (strpos($notification['message'], 'forge:') === 0) {
                    $parts = explode(':', $notification['message']);
                    $issueNumber = (int) trim($parts[1]);
                    $result = $this->queryForge('issues/'.$issueNumber);
                    if ($result) {
                        $msg = '*Hi <@'.$user.'>, <@'.$notification['from_user'].'>'
                            .' ask you to look at this issue:*';
                        $this->sendResponse($msg."\n".$this->buildIssueMessage($result->issue), $user);
                    }
                } else {
                    $msg = '*Hi <@'.$user.'>, here is a message from <@'.$notification['from_user'].'>'
                        .' for you:*';
                    $this->sendResponse($msg."\n".$notification['message'], $user);
                }
                $now = new \DateTime();
                $now->setTimestamp(time());
                $this->getDatabaseConnection()->update(
                    'notifications',
                    ['delivered' => $now],
                    ['id' => $notification['id']],
                    ['datetime']
                );
            }
        }
    }

    /**
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processTell()
    {
        $params = $this->params;
        array_shift($params);
        $toUser = array_shift($params);
        $toUser = str_replace(['<', '>', '@'], '', $toUser);
        if ($params[0] === 'about'
            && (strpos($params[1], 'review:') !== false || strpos($params[1], 'forge:') !== false)) {
            $message = $params[1];
        } else {
            $message = implode(' ', $params);
        }
        $this->getDatabaseConnection()->insert('notifications', [
            'from_user' => $this->payload->getData()['user'],
            'to_user' => $toUser,
            'message' => $message,
        ]);

        return 'OK, I will tell <@'.$toUser.'> about your message';
    }
}
