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

use /** @noinspection PhpInternalEntityUsedInspection */
    Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Slack\ApiClient;
use Slack\Message\Attachment;
use T3Bot\Slack\Message;

/**
 * Class GerritHookController.
 */
class GerritHookController
{
    /**
     * @var ApiClient
     */
    protected $slackApiClient;

    /**
     * public method to start processing the request.
     *
     * @param string $hook
     */
    public function process($hook)
    {
        $entityBody = file_get_contents('php://input');
        $json = json_decode($entityBody);

        if ($GLOBALS['config']['gerrit']['webhookToken'] != $json->token) {
            exit;
        }
        if ($json->project !== 'Packages/TYPO3.CMS') {
            // only core patches please...
            exit;
        }
        $patchId = (int) str_replace('https://review.typo3.org/', '', $json->{'change-url'});
        $patchSet = (property_exists($json, 'patchset')) ? intval($json->patchset) : 0;
        $commit = $json->commit;
        $branch = $json->branch;

        switch ($hook) {
            case 'patchset-created':
                if ($patchSet == 1 && $branch == 'master') {
                    foreach ($GLOBALS['config']['gerrit'][$hook]['channels'] as $channel) {
                        $item = $this->queryGerrit('change:'.$patchId);
                        $item = $item[0];
                        $created = substr($item->created, 0, 19);

                        $message = new Message();
                        $message->setText(' ');
                        $attachment = new Message\Attachment();

                        $attachment->setColor(Message\Attachment::COLOR_NOTICE);
                        $attachment->setTitle('[NEW] '.$item->subject);
                        if (property_exists($item, 'owner') && property_exists($item->owner, 'name')) {
                            $attachment->setAuthorName($item->owner->name);
                        }

                        $text = "Branch: *{$branch}* | :calendar: _{$created}_ | ID: {$item->_number}\n";
                        $text .= ":link: <https://review.typo3.org/{$item->_number}|Review now>";
                        $attachment->setText($text);
                        $attachment->setFallback($text);
                        $message->addAttachment($attachment);
                        $this->postToSlack($message, $channel);
                    }
                }
                break;
            case 'change-merged':
                $item = $this->queryGerrit('change:'.$patchId);
                $item = $item[0];
                foreach ($GLOBALS['config']['gerrit'][$hook]['channels'] as $channel) {
                    $created = substr($item->created, 0, 19);

                    $message = new Message();
                    $message->setText(' ');
                    $attachment = new Message\Attachment();

                    $attachment->setColor(Message\Attachment::COLOR_GOOD);
                    $attachment->setTitle(':white_check_mark: [MERGED] '.$item->subject);
                    if (property_exists($item, 'owner') && property_exists($item->owner, 'name')) {
                        $attachment->setAuthorName($item->owner->name);
                    }

                    $text = "Branch: {$branch} | :calendar: {$created} | ID: {$item->_number}\n";
                    $text .= ":link: <https://review.typo3.org/{$item->_number}|Goto Review>";
                    $attachment->setText($text);
                    $attachment->setFallback($text);
                    $message->addAttachment($attachment);
                    $this->postToSlack($message, $channel);
                }
                $files = $this->getFilesForPatch($patchId, $commit);
                $rstFiles = array();
                foreach ($files as $fileName => $changeInfo) {
                    if ($this->endsWith(strtolower($fileName), '.rst')) {
                        $rstFiles[$fileName] = $changeInfo;
                    }
                }
                if (count($rstFiles) > 0) {
                    $channel = '#fntest';
                    foreach ($rstFiles as $fileName => $changeInfo) {
                        $status = !empty($changeInfo->status) ? $changeInfo->status : null;

                        $message = new Message();
                        $message->setText(' ');
                        $attachment = new Message\Attachment();

                        switch ($status) {
                            case 'A':
                                $attachment->setColor(Message\Attachment::COLOR_GOOD);
                                $attachment->setTitle(':white_check_mark: [MERGED] ' . $item->subject);
                                if (property_exists($item, 'owner') && property_exists($item->owner, 'name')) {
                                    $attachment->setAuthorName($item->owner->name);
                                }
                                $text = "A new documentation file has been added\n";
                                $text .= ':link: <https://git.typo3.org/Packages/TYPO3.CMS.git/blob/HEAD:/' . $fileName
                                    . '|Show reST file>';
                                $attachment->setText($text);
                                $attachment->setFallback($text);
                                $message->addAttachment($attachment);
                                break;
                            case 'D':
                                $attachment->setColor(Message\Attachment::COLOR_WARNING);
                                $attachment->setTitle(':white_check_mark: [MERGED] '.$item->subject);
                                if (property_exists($item, 'owner') && property_exists($item->owner, 'name')) {
                                    $attachment->setAuthorName($item->owner->name);
                                }
                                $text = "A documentation file has been removed\n";
                                $text .= ':link: <https://git.typo3.org/Packages/TYPO3.CMS.git/blob/HEAD:/' . $fileName
                                    . '|Show reST file>';
                                $attachment->setText($text);
                                $attachment->setFallback($text);
                                $message->addAttachment($attachment);
                                break;
                            default:
                                $attachment->setColor(Message\Attachment::COLOR_WARNING);
                                $attachment->setTitle(':white_check_mark: [MERGED] '.$item->subject);
                                if (property_exists($item, 'owner') && property_exists($item->owner, 'name')) {
                                    $attachment->setAuthorName($item->owner->name);
                                }
                                $text = "A documentation file has been updated\n";
                                $text .= ':link: <https://git.typo3.org/Packages/TYPO3.CMS.git/blob/HEAD:/' . $fileName
                                    . '|Show reST file>';
                                $attachment->setText($text);
                                $attachment->setFallback($text);
                                $message->addAttachment($attachment);
                                break;
                        }
                        $this->postToSlack($message, $channel);
                        sleep(1);
                    }
                }
                break;
            default:
                exit;
            break;
        }
    }

    /**
     * @param $query
     *
     * @return object|array
     */
    protected function queryGerrit($query)
    {
        $url = 'https://review.typo3.org/changes/?q='.$query;
        $result = file_get_contents($url);
        $result = json_decode(str_replace(")]}'\n", '', $result));

        return $result;
    }

    /**
     * @param int $changeId
     * @param int $revision
     *
     * @return mixed|string
     */
    protected function getFilesForPatch($changeId, $revision)
    {
        $url = 'https://review.typo3.org/changes/' . $changeId . '/revisions/' . $revision . '/files';
        $result = file_get_contents($url);
        $result = json_decode(str_replace(")]}'\n", '', $result));

        return $result;
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
                'fields' => $attachment->getFields(),
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
