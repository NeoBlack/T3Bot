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
    $hookCommandController = new \T3Bot\Controller\GerritHookController($GLOBALS['config']);
    switch ($_REQUEST['action']) {
        case 'change-merged':
        case '/var/gerrit/review/hooks/change-merged':
            $hookCommandController->process('change-merged');
            break;
        case 'patchset-created':
        case '/var/gerrit/review/hooks/patchset-created':
            $hookCommandController->process('patchset-created');
            break;
    }
}
