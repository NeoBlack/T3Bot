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
    // action could contains the path /var/gerrit/review/hooks/, remove it
    $action = str_replace('/var/gerrit/review/hooks/', '', $_REQUEST['action']);

    (new \T3Bot\Controller\GerritHookController($GLOBALS['config']))
        ->process($action);
}

