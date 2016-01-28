<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <typo3@naegler.net>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Commands;

/**
 * Class ForgeCommand.
 */
class UtilCommand extends AbstractCommand
{
    protected $commandName = 'util';

    /**
     *
     */
    public function __construct()
    {
        $this->helpCommands['help'] = 'shows this help';
        $this->helpCommands['coin <options>'] = 'coin toss with <options> (separate by comma)';
    }

    protected function processCoin()
    {
        $params = $this->params;
        array_shift($params);
        $params = implode(' ', $params);
        $options = array_map('trim', explode(',', $params));
        if (count($options) === 1) {
            return '*T3Bot says:* _A complicated decision ..._';
        }
        if (count(array_unique($options)) === 1) {
            return '*T3Bot says:* _it is undecidable ..._';
        }

        $option = $options[rand(0, count($options) - 1)];

        return '*T3Bot says:* _'.$option.'_';
    }
}
