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
}