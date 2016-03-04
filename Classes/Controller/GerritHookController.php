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
use Slack\ApiClient;
use Slack\Message\Attachment;
use T3Bot\Slack\Message;
use T3Bot\Traits\GerritTrait;

/**
 * Class GerritHookController.
 */
class GerritHookController
{
    use GerritTrait;

    /**
     * @var ApiClient
     */
    protected $slackApiClient;

    /**
     * public method to start processing the request.
     *
     * @param string $hook
     * @param string $input
     */
    public function process($hook, $input = 'php://input')
    {
        $entityBody = file_get_contents($input);
        $json = json_decode($entityBody);

        if ($GLOBALS['config']['gerrit']['webhookToken'] !== $json->token) {
            return;
        }
        if ($json->project !== 'Packages/TYPO3.CMS') {
            // only core patches please...
            return;
        }
        $patchId = (int) str_replace('https://review.typo3.org/', '', $json->{'change-url'});
        $patchSet = (property_exists($json, 'patchset')) ? intval($json->patchset) : 0;
        $commit = $json->commit;
        $branch = $json->branch;

        switch ($hook) {
            case 'patchset-created':
                if ($patchSet == 1 && $branch == 'master') {
                    $item = $this->queryGerrit('change:'.$patchId);
                    $item = $item[0];
                    $created = substr($item->created, 0, 19);

                    $message = new Message();
                    $message->setText(' ');
                    $attachment = new Message\Attachment();

                    $attachment->setColor(Message\Attachment::COLOR_NOTICE);
                    $attachment->setTitle('[NEW] '.$item->subject);

                    $text = "Branch: *{$branch}* | :calendar: _{$created}_ | ID: {$item->_number}\n";
                    $text .= ":link: <https://review.typo3.org/{$item->_number}|Review now>";
                    $attachment->setText($text);
                    $attachment->setFallback($text);
                    $message->addAttachment($attachment);

                    foreach ($GLOBALS['config']['gerrit'][$hook]['channels'] as $channel) {
                        $this->postToSlack($message, $channel);
                    }
                }
                break;
            case 'change-merged':
                $item = $this->queryGerrit('change:'.$patchId);
                $item = $item[0];
                $created = substr($item->created, 0, 19);

                $message = new Message();
                $message->setText(' ');
                $attachment = new Message\Attachment();

                $attachment->setColor(Message\Attachment::COLOR_GOOD);
                $attachment->setTitle(':white_check_mark: [MERGED] '.$item->subject);

                $text = "Branch: {$branch} | :calendar: {$created} | ID: {$item->_number}\n";
                $text .= ":link: <https://review.typo3.org/{$item->_number}|Goto Review>";
                $attachment->setText($text);
                $attachment->setFallback($text);
                $message->addAttachment($attachment);

                foreach ($GLOBALS['config']['gerrit'][$hook]['channels'] as $channel) {
                    $this->postToSlack($message, $channel);
                }

                $files = $this->getFilesForPatch($patchId, $commit);
                $rstFiles = array();
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
                        $text = ':link: <https://git.typo3.org/Packages/TYPO3.CMS.git/blob/HEAD:/' . $fileName
                            . '|' . $fileName . '>';
                        $attachment->setText($text);
                        $attachment->setFallback($text);
                        $message->addAttachment($attachment);
                    }
                    foreach ($GLOBALS['config']['gerrit']['rst-merged']['channels'] as $channel) {
                        $this->postToSlack($message, $channel);
                    }
                }
                break;
        }
    }

    /**
     * @param Message $payload
     * @param string $channel
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
            /** @noinspection PhpUndefinedFieldInspection */
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
        /** @noinspection PhpInternalEntityUsedInspection */
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
