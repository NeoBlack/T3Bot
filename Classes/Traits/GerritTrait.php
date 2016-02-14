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

trait GerritTrait
{
    /**
     * @param string $query
     *
     * @return object|bool
     */
    protected function queryGerrit($query)
    {
        $url = 'https://review.typo3.org/changes/?q='.urlencode($query);

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);

        $result = false;
        if (!curl_errno($ch)) {
            curl_close($ch);
            $result = json_decode(str_replace(")]}'\n", '', $data));
        }
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

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);

        $result = false;
        if (!curl_errno($ch)) {
            curl_close($ch);
            $result = json_decode(str_replace(")]}'\n", '', $data), true);
        }
        return $result;
    }

    /**
     * build a review line.
     *
     * @param object $item the review item
     *
     * @return string
     */
    protected function buildReviewLine($item)
    {
        return $this->bold($item->subject) . ' <https://review.typo3.org/' . $item->_number
        . '|Review #' . $item->_number . ' now>';
    }

}