<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Commands;

/**
 * Class UtilCommand.
 */
class UtilCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $commandName = 'util';

    /**
     * @var array
     */
    protected $helpCommands = [
        'help' => 'shows this help',
        'coin [options]' => 'coin toss with [options] (separate by comma)'
    ];

    /**
     * @return string
     */
    protected function processCoin()
    {
        $params = $this->params;
        array_shift($params);
        $params = implode(' ', $params);
        $options = array_map('trim', explode(',', $params));
        if (count($options) === 1) {
            return '*Botty says:* _A complicated decision ..._';
        }
        if (count(array_unique($options)) === 1) {
            return '*Botty says:* _it is undecidable ..._';
        }

        $option = $options[rand(0, count($options) - 1)];

        return '*Botty says:* _'.$option.'_';
    }
}
