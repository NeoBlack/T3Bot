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
}
