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
        $json = json_decode(file_get_contents($input));

        if (
            $json->project !== 'Packages/TYPO3.CMS' ||
            $json->token !== $this->configuration['gerrit']['webhookToken']
        ) {
            return;
        }
        $patchId = (int) str_replace('https://review.typo3.org/', '', $json->{'change-url'});
        $patchSet = property_exists($json, 'patchset') ? (int) $json->patchset : 0;

        $item = $this->queryGerrit('change:' . $patchId);
        $item = $item[0];
        $created = substr($item->created, 0, 19);
        $text = "Branch: {$json->branch} | :calendar: {$created} | ID: {$item->_number}\n";
        $text .= ":link: <https://review.typo3.org/{$item->_number}|Goto Review>";
        if ($hook === 'patchset-created' && $patchSet === 1 && $json->branch === 'master') {
            $message = $this->buildMessage('[NEW] ' . $item->subject, $text);
            $this->sendMessageToChannel($hook, $message);
        } elseif ($hook === 'change-merged') {
            $message = $this->buildMessage(':white_check_mark: [MERGED] ' . $item->subject, $text, Message\Attachment::COLOR_GOOD);
            $this->sendMessageToChannel($hook, $message);
            $this->checkFiles($patchId, $json->commit);
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
        if (!empty($rstFiles)) {
            $message = new Message();
            $message->setText(' ');
            foreach ($rstFiles as $fileName => $changeInfo) {
                $attachment = new Message\Attachment();
                $status = $changeInfo['status'] ?? 'default';
                $color = [
                    'A' => Message\Attachment::COLOR_GOOD,
                    'D' => Message\Attachment::COLOR_WARNING,
                    'default' => Message\Attachment::COLOR_WARNING,
                ];
                $text = [
                    'A' => 'A new documentation file has been added',
                    'D' => 'A documentation file has been removed',
                    'default' => 'A documentation file has been updated',
                ];
                $attachment->setColor($color[$status]);
                $attachment->setTitle($text[$status]);

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
