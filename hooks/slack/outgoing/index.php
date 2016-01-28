<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <typo3@naegler.net>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

require_once dirname(__FILE__).'/../../../vendor/autoload.php';
require_once dirname(__FILE__).'/../../../config/config.php';

// if we receive a POST request, it is for our bot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slackCommandController = new \T3Bot\Controller\SlackCommandController();
    $slackCommandController->process();
}
