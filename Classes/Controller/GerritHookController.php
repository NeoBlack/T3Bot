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
use T3Bot\Traits\GerritTrait;

/**
 * Class GerritHookController.
 */
class GerritHookController extends AbstractHookController
{
    use GerritTrait;

    /**
     * public method to start processing the request.
     *
     * @param string $hook
     * @param string $input
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function process($hook, $input = 'php://input')
    {
        $entityBody = file_get_contents($input);
        $json = json_decode($entityBody);

        if ($this->configuration['gerrit']['webhookToken'] !== $json->token) {
            return;
        }
        if ($json->project !== 'Packages/TYPO3.CMS') {
            // only core patches please...
            return;
        }
        $patchId = (int) str_replace('https://review.typo3.org/', '', $json->{'change-url'});
        $patchSet = property_exists($json, 'patchset') ? (int) $json->patchset : 0;
        $commit = $json->commit;
        $branch = $json->branch;

        switch ($hook) {
            case 'patchset-created':
                if ($patchSet === 1 && $branch === 'master') {
                    $item = $this->queryGerrit('change:' . $patchId);
                    $item = $item[0];
                    $created = substr($item->created, 0, 19);

                    $text = "Branch: *{$branch}* | :calendar: _{$created}_ | ID: {$item->_number}\n";
                    $text .= ":link: <https://review.typo3.org/{$item->_number}|Review now>";

                    $message = $this->buildMessage('[NEW] ' . $item->subject, $text);
                    $this->sendMessageToChannel($hook, $message);
                }
                break;
            case 'change-merged':
                $item = $this->queryGerrit('change:' . $patchId);
                $item = $item[0];
                $created = substr($item->created, 0, 19);

                $text = "Branch: {$branch} | :calendar: {$created} | ID: {$item->_number}\n";
                $text .= ":link: <https://review.typo3.org/{$item->_number}|Goto Review>";

                $message = $this->buildMessage(':white_check_mark: [MERGED] ' . $item->subject, $text, Message\Attachment::COLOR_GOOD);
                $this->sendMessageToChannel($hook, $message);

                $this->checkFiles($patchId, $commit);
                break;
        }
    }

    /**
     * @param string $title
     * @param string $text
     * @param string $color
     *
     * @return Message
     */
    protected function buildMessage(string $title, string $text, string $color = Message\Attachment::COLOR_NOTICE) : Message
    {
        $message = new Message();
        $message->setText(' ');
        $attachment = new Message\Attachment();

        $attachment->setColor($color);
        $attachment->setTitle($title);

        $attachment->setText($text);
        $attachment->setFallback($text);
        $message->addAttachment($attachment);
        return $message;
    }

    /**
     * @param int $patchId
     * @param int $commit
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function checkFiles($patchId, $commit)
    {
        $files = $this->getFilesForPatch($patchId, $commit);
        $rstFiles = [];
        if (is_array($files)) {
            foreach ($files as $fileName => $changeInfo) {
                if ($this->endsWith(strtolower($fileName), '.rst')) {
                    $rstFiles[$fileName] = $changeInfo;
                }
            }
        }
        if (count($rstFiles) > 0) {
            $message = new Message();
            $message->setText(' ');
            foreach ($rstFiles as $fileName => $changeInfo) {
                $attachment = new Message\Attachment();
                $status = !empty($changeInfo['status']) ? $changeInfo['status'] : null;
                switch ($status) {
                    case 'A':
                        $attachment->setColor(Message\Attachment::COLOR_GOOD);
                        $attachment->setTitle('A new documentation file has been added');
                        break;
                    case 'D':
                        $attachment->setColor(Message\Attachment::COLOR_WARNING);
                        $attachment->setTitle('A documentation file has been removed');
                        break;
                    default:
                        $attachment->setColor(Message\Attachment::COLOR_WARNING);
                        $attachment->setTitle('A documentation file has been updated');
                        break;
                }
                $text = ':link: <https://git.typo3.org/Packages/TYPO3.CMS.git/blob/HEAD:/' . $fileName . '|' . $fileName . '>';
                $attachment->setText($text);
                $attachment->setFallback($text);
                $message->addAttachment($attachment);
            }
            $this->sendMessageToChannel('rst-merged', $message);
        }
    }

    /**
     * @param string $hook
     * @param Message $message
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function sendMessageToChannel(string $hook, Message $message)
    {
        if (is_array($this->configuration['gerrit'][$hook]['channels'])) {
            foreach ($this->configuration['gerrit'][$hook]['channels'] as $channel) {
                $this->postToSlack($message, $channel);
            }
        }
    }
}
