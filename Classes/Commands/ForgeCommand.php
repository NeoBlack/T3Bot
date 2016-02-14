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

use T3Bot\Traits\ForgerTrait;

/**
 * Class ForgeCommand.
 */
class ForgeCommand extends AbstractCommand
{
    use ForgerTrait;

    /**
     * @var string
     */
    protected $commandName = 'forge';

    /**
     * @var array
     */
    protected $helpCommands = [
        'help' => 'shows this help',
        'show [Issue-ID]' => 'shows the issue by given [Issue-ID]'
    ];

    /**
     * process show.
     *
     * @return string
     */
    protected function processShow()
    {
        $urlPattern = '/http[s]*:\/\/forge.typo3.org\/issues\/([0-9]*)(?:.*)*/i';
        $issueNumber = isset($this->params[1]) ? $this->params[1] : '';
        if (preg_match_all($urlPattern, $issueNumber, $matches)) {
            $issueNumber = (int)$matches[1][0];
        } else {
            $issueNumber = (int)$issueNumber;
        }
        if ($issueNumber === null || $issueNumber == 0) {
            return 'hey, I need an issue number!';
        }
        $result = $this->queryForge("issues/{$issueNumber}");
        if ($result) {
            return $this->buildIssueMessage($result->issue);
        } else {
            return 'Sorry not found!';
        }
    }
}
