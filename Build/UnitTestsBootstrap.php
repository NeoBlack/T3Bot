<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
class UnitTestsBootstrap
{
    /**
     * Bootstraps the system for unit tests.
     */
    public function bootstrapSystem()
    {
        $this->enableDisplayErrors()
            ->initializeConfiguration();
    }

    /**
     * Makes sure error messages during the tests get displayed no matter what is set in php.ini.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function enableDisplayErrors()
    {
        @ini_set('display_errors', 1);

        return $this;
    }

    /**
     * Provides the default configuration.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function initializeConfiguration()
    {
        include __DIR__.'/../vendor/autoload.php';
        include __DIR__.'/../config/config.php';

        return $this;
    }
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
$bootstrap = new UnitTestsBootstrap();
$bootstrap->bootstrapSystem();
unset($bootstrap);
