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

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait LoggerTrait
{
    protected $logger;

    /**
     * @return Logger
     * @throws Exception
     */
    protected function getLogger(): Logger
    {
        if ($this->logger === null) {
            $this->logger = new Logger('application');
            $this->logger->pushHandler(new StreamHandler($GLOBALS['config']['log']['file'], (int)$GLOBALS['config']['log']['level']));
        }
        return $this->logger;
    }
}
