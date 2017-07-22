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

use Slack\Payload;
use Slack\RealTimeClient;

/**
 * Class UtilCommand.
 *
 * @property string commandName
 * @property array helpCommands
 */
class UtilCommand extends AbstractCommand
{
    /**
     * AbstractCommand constructor.
     *
     * @param Payload        $payload
     * @param RealTimeClient $client
     */
    public function __construct(Payload $payload, RealTimeClient $client)
    {
        $this->commandName = 'util';
        $this->helpCommands = [
            'help' => 'shows this help',
            'coin [options]' => 'coin toss with [options] (separate by comma)',
        ];
        parent::__construct($payload, $client);
    }

    /**
     * @return string
     */
    protected function processCoin() : string
    {
        $params = $this->params;
        array_shift($params);
        $params = implode(' ', $params);
        $options = array_map('trim', explode(',', $params));
        if (count($options) === 1) {
            return '*Botty says:* _A complicated decision ..._';
        }
        if (count(array_count_values($options)) === 1) {
            return '*Botty says:* _it is undecidable ..._';
        }

        $option = $options[random_int(0, count($options) - 1)];

        return '*Botty says:* _' . $option . '_';
    }
}
