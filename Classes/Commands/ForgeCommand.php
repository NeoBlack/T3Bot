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
 * Class ForgeCommand.
 *
 * @property string commandName
 * @property array helpCommands
 */
class ForgeCommand extends AbstractCommand
{
    /**
     * AbstractCommand constructor.
     *
     * @param Payload        $payload
     * @param RealTimeClient $client
     */
    public function __construct(Payload $payload, RealTimeClient $client)
    {
        $this->commandName = 'forge';
        $this->helpCommands = [
            'help' => 'shows this help',
            'show [Issue-ID]' => 'shows the issue by given [Issue-ID]',
        ];
        parent::__construct($payload, $client);
    }

    /**
     * process show.
     *
     * @return string
     */
    protected function processShow() : string
    {
        $urlPattern = '/http[s]*:\/\/forge.typo3.org\/issues\/([\d]*)(?:.*)/i';
        $issueNumber = !empty($this->params[1]) ? $this->params[1] : '';
        if (preg_match_all($urlPattern, $issueNumber, $matches)) {
            $issueNumber = (int) $matches[1][0];
        } else {
            $issueNumber = (int) $issueNumber;
        }
        if ($issueNumber === null || $issueNumber === 0) {
            return 'hey, I need an issue number!';
        }
        $result = $this->queryForge("issues/{$issueNumber}");
        if ($result) {
            return $this->buildIssueMessage($result->issue);
        }
        return 'Sorry not found!';
    }
}
