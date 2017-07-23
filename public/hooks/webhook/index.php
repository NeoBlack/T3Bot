<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../config/config.php';

// if we receive a POST request, it is for our bot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new \T3Bot\Controller\WebHookController($GLOBALS['config']))
        ->process($_REQUEST['hook']);
}
