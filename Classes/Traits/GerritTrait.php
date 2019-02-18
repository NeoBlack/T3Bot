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
     * @return \stdClass|array|bool
     */
    protected function queryGerrit($query)
    {
        return $this->remoteCall('https://review.typo3.org/changes/?q=' . urlencode($query));
    }

    /**
     * @param int $changeId
     * @param int $revision
     *
     * @return mixed|string
     */
    protected function getFilesForPatch($changeId, $revision)
    {
        return $this->remoteCall(
            'https://review.typo3.org/changes/' . $changeId . '/revisions/' . $revision . '/files'
        );
    }

    /**
     * Resolve patch ID from URL
     * @param string $url
     * @return string
     */
    protected function resolvePatchIdFromUrl($url): string {
        $re = '@https://review\.typo3\.org/c/Packages/TYPO3\.CMS/\+/([0-9]*)@m';
        preg_match_all($re, $url, $matches, PREG_SET_ORDER, 0);
        return $matches[0][1];
    }

    /**
     * @param string $url
     *
     * @return bool|mixed
     */
    protected function remoteCall($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);

        $result = false;
        if (!curl_errno($ch)) {
            curl_close($ch);
            $result = json_decode(str_replace(")]}'" . chr(10), '', $data));
        }

        return $result;
    }
}
