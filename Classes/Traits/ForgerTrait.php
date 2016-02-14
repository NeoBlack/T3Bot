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

trait ForgerTrait
{
    /**
     * @param string $query
     *
     * @return mixed|bool
     */
    protected function queryForge($query)
    {
        $url = "https://forge.typo3.org/{$query}.json";

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);

        $result = false;
        if (!curl_errno($ch)) {
            curl_close($ch);
            $result = json_decode($data);
        }
        return $result;
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
        $text = $this->bold('[' . $item->tracker->name . '] ' . $item->subject)
            . ' by ' . $this->italic($item->author->name) . "\n";
        $text .= 'Project: '.$this->bold($item->project->name);
        if (!empty($item->category->name)) {
            $text .= ' | Category: '.$this->bold($item->category->name);
        }
        $text .= ' | Status: '.$this->bold($item->status->name)."\n";
        $text .= ':calendar: Created: '.$this->bold($created).' | Last update: '.$this->bold($updated)."\n";
        $text .= '<https://forge.typo3.org/issues/'.$item->id.'|:arrow_right: View on Forge>';

        return $text;
    }
}