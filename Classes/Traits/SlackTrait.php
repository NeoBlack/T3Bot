<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Traits;

use Slack\DataObject;
use T3Bot\Slack\Message\Attachment;

/**
 * Trait SlackTrait
 */
trait SlackTrait
{
    /**
     * make text bold.
     *
     * @param $string
     *
     * @return string
     */
    protected function bold($string) : string
    {
        return '*' . $string . '*';
    }

    /**
     * make text italic.
     *
     * @param $string
     *
     * @return string
     */
    protected function italic($string) : string
    {
        return '_' . $string . '_';
    }

    /**
     * build a review line.
     *
     * @param \stdClass $item the review item
     *
     * @return string
     */
    protected function buildReviewLine($item) : string
    {
        return $this->bold($item->subject) . ' <https://review.typo3.org/' . $item->_number
        . '|Review #' . $item->_number . ' now>';
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function buildIssueMessage($item) : string
    {
        $created = substr($item->created_on, 0, 19);
        $updated = substr($item->updated_on, 0, 19);
        $text = $this->bold('[' . $item->tracker->name . '] ' . $item->subject)
            . ' by ' . $this->italic($item->author->name) . chr(10);
        $text .= 'Project: ' . $this->bold($item->project->name);
        if (!empty($item->category->name)) {
            $text .= ' | Category: ' . $this->bold($item->category->name);
        }
        $text .= ' | Status: ' . $this->bold($item->status->name) . chr(10);
        $text .= ':calendar: Created: ' . $this->bold($created) . ' | Last update: ' . $this->bold($updated) . chr(10);
        $text .= '<https://forge.typo3.org/issues/' . $item->id . '|:arrow_right: View on Forge>';

        return $text;
    }

    /**
     * @param string $text
     * @param string $channel
     *
     * @return array
     */
    protected function getBaseDataArray(string $text, string $channel) : array
    {
        $data = [];
        $data['unfurl_links'] = false;
        $data['unfurl_media'] = false;
        $data['parse'] = 'none';
        $data['text'] = $text;
        $data['channel'] = $channel;
        return $data;
    }

    /**
     * @param Attachment $attachment
     *
     * @return DataObject
     */
    protected function buildAttachment(Attachment $attachment) : DataObject
    {
        return \Slack\Message\Attachment::fromData([
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
}
