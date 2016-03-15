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

trait SlackTrait
{
    /**
     * make text bold.
     *
     * @param $string
     *
     * @return string
     */
    protected function bold($string)
    {
        return '*'.$string.'*';
    }

    /**
     * make text italic.
     *
     * @param $string
     *
     * @return string
     */
    protected function italic($string)
    {
        return '_'.$string.'_';
    }

    /**
     * build a review line.
     *
     * @param \stdClass $item the review item
     *
     * @return string
     */
    protected function buildReviewLine($item)
    {
        return $this->bold($item->subject).' <https://review.typo3.org/'.$item->_number
        .'|Review #'.$item->_number.' now>';
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function buildIssueMessage($item)
    {
        $created = substr($item->created_on, 0, 19);
        $updated = substr($item->updated_on, 0, 19);
        $text = $this->bold('['.$item->tracker->name.'] '.$item->subject)
            .' by '.$this->italic($item->author->name)."\n";
        $text .= 'Project: '.$this->bold($item->project->name);
        if ($item->category->name !== '') {
            $text .= ' | Category: '.$this->bold($item->category->name);
        }
        $text .= ' | Status: '.$this->bold($item->status->name)."\n";
        $text .= ':calendar: Created: '.$this->bold($created).' | Last update: '.$this->bold($updated)."\n";
        $text .= '<https://forge.typo3.org/issues/'.$item->id.'|:arrow_right: View on Forge>';

        return $text;
    }
}
