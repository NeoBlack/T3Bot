<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <typo3@naegler.net>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

require_once dirname(__FILE__).'/../../vendor/autoload.php';
require_once dirname(__FILE__).'/../../config/config.php';

// if we receive a POST request, it is for our bot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slackCommandController = new \T3Bot\Controller\GerritHookController();
    switch ($_REQUEST['action']) {
        case 'change-merged':
        case '/var/gerrit/review/hooks/change-merged':
            $slackCommandController->process('change-merged');
            break;
        case 'patchset-created':
        case '/var/gerrit/review/hooks/patchset-created':
            $slackCommandController->process('patchset-created');
            break;
    }
}
