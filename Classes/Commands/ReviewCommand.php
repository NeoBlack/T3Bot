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
use T3Bot\Slack\Message;

/**
 * Class ReviewCommand.
 *
 * @property string commandName
 * @property array helpCommands
 */
class ReviewCommand extends AbstractCommand
{
    /**
     * AbstractCommand constructor.
     *
     * @param Payload $payload
     * @param RealTimeClient $client
     * @param array|null $configuration
     */
    public function __construct(Payload $payload, RealTimeClient $client, array $configuration = null)
    {
        $this->commandName = 'review';
        $this->helpCommands = [
            'help' => 'shows this help',
            'count [PROJECT=Packages/TYPO3.CMS]' => 'shows the number of currently open reviews for [PROJECT]',
            'random' => 'shows a random open review',
            'show [Ref-ID] [[Ref-ID-2] [[Ref-ID-n]]]' => 'shows the review by given change number(s). Do not use separators other than space.',
            'user [username] [PROJECT=Packages/TYPO3.CMS]' => 'shows the open reviews by given username for [PROJECT]',
            'query [searchQuery]' => 'shows the results for given [searchQuery], max limit is 50',
            'merged [YYYY-MM-DD]' => 'shows a count of merged patches on master since given date',
        ];
        parent::__construct($payload, $client, $configuration);
    }

    /**
     * process count.
     *
     * @return string
     */
    protected function processCount() : string
    {
        $project = !empty($this->params[1]) ? $this->params[1] : 'Packages/TYPO3.CMS';
        $result = $this->queryGerrit("is:open branch:master -message:WIP project:{$project}");
        $count = count($result);
        $result = $this->queryGerrit("label:Code-Review=-1 is:open branch:master -message:WIP project:{$project}");
        $countMinus1 = count($result);
        $result = $this->queryGerrit("label:Code-Review=-2 is:open branch:master -message:WIP project:{$project}");
        $countMinus2 = count($result);

        $returnString = '';
        $returnString .= 'There are currently ' . $this->bold($count) . ' open reviews for project '
            . $this->italic($project) . ' and branch master on <https://review.typo3.org/#/q/project:' . $project
            . '+status:open+branch:master|https://review.typo3.org>' . chr(10);
        $returnString .= $this->bold($countMinus1) . ' of ' . $this->bold($count) . ' open reviews voted with '
            . $this->bold('-1') . ' <https://review.typo3.org/#/q/label:Code-Review%253D-1+is:open+branch:'
            . 'master+project:' . $project . '|Check now> ' . chr(10);
        $returnString .= $this->bold($countMinus2) . ' of ' . $this->bold($count) . ' open reviews voted with '
            . $this->bold('-2') . ' <https://review.typo3.org/#/q/label:Code-Review%253D-2+is:open+branch:'
            . 'master+project:' . $project . '|Check now>';

        return $returnString;
    }

    /**
     * process random.
     *
     * @return Message
     */
    protected function processRandom() : Message
    {
        /** @var array $result */
        $result = $this->queryGerrit('is:open project:Packages/TYPO3.CMS');
        $item = $result[array_rand($result)];

        return $this->buildReviewMessage($item);
    }

    /**
     * process user.
     *
     * @return string
     */
    protected function processUser() : string
    {
        $username = !empty($this->params[1]) ? $this->params[1] : null;
        $project = !empty($this->params[2]) ? $this->params[2] : 'Packages/TYPO3.CMS';
        if ($username === null) {
            return 'hey, I need a username!';
        }
        $results = $this->queryGerrit('is:open owner:"' . $username . '" project:' . $project);
        if (count($results) > 0) {
            $listOfItems = ['*Here are the results for ' . $username . '*:'];
            if (is_array($results)) {
                foreach ($results as $item) {
                    $listOfItems[] = $this->buildReviewLine($item);
                }
            }

            return implode(chr(10), $listOfItems);
        }
        return $username . ' has no open reviews or username is unknown';
    }

    /**
     * process count.
     *
     * @return string|Message
     */
    protected function processShow()
    {
        $urlPattern = '/http[s]*:\/\/review.typo3.org\/[#\/c]*([\d]*)(?:.*)/i';
        $refId = $this->params[1] ?? 0;
        if (preg_match_all($urlPattern, $refId, $matches)) {
            $refId = (int) $matches[1][0];
        } else {
            $refId = (int) $refId;
        }
        if ($refId === 0) {
            return 'hey, I need at least one change number!';
        }
        $paramsCount = count($this->params);
        if ($paramsCount > 2) {
            $returnMessage = $this->buildReviewLineOutput();
        } else {
            $returnMessage = $this->buildReviewMessageOutput($refId);
        }

        return $returnMessage;
    }

    /**
     * @return string
     */
    protected function buildReviewLineOutput() : string
    {
        $paramsCount = count($this->params);
        $changeIds = [];
        for ($i = 1; $i < $paramsCount; ++$i) {
            $changeIds[] = 'change:' . $this->params[$i];
        }
        $result = $this->queryGerrit(implode(' OR ', $changeIds));
        $listOfItems = [];
        if (is_array($result)) {
            foreach ($result as $item) {
                $listOfItems[] = $this->buildReviewLine($item);
            }
        }
        return implode(chr(10), $listOfItems);
    }

    /**
     * @param int $refId
     *
     * @return string|Message
     */
    protected function buildReviewMessageOutput(int $refId)
    {
        $result = $this->queryGerrit('change:' . $refId);
        if (!empty($result)) {
            foreach ($result as $item) {
                if ($item->_number === $refId) {
                    return $this->buildReviewMessage($item);
                }
            }
        }
        return "{$refId} not found, sorry!";
    }

    /**
     * process query.
     *
     * @return string
     */
    protected function processQuery() : string
    {
        array_shift($this->params);
        $query = trim(implode(' ', $this->params));
        if ($query === '') {
            return 'hey, I need a query!';
        }

        $results = $this->queryGerrit('limit:50 ' . $query);
        if (!empty($results)) {
            $listOfItems = ["*Here are the results for {$query}*:"];
            foreach ($results as $item) {
                $listOfItems[] = $this->buildReviewLine($item);
            }
            return implode(chr(10), $listOfItems);
        }

        return "{$query} not found, sorry!";
    }

    /**
     * @return string
     */
    protected function processMerged() : string
    {
        $query = 'project:Packages/TYPO3.CMS status:merged after:###DATE### branch:master';

        $date = !empty($this->params[1]) ? $this->params[1] : '';
        if (!$this->isDateFormatCorrect($date)) {
            return 'hey, I need a date in the format YYYY-MM-DD!';
        }
        $query = str_replace('###DATE###', $date, $query);
        $result = $this->queryGerrit($query);

        $cnt = count($result);

        return 'Good job folks, since ' . $date . ' you merged *' . $cnt . '* patches into master';
    }

    /**
     * check format of given date.
     *
     * @param $date
     *
     * @return bool
     */
    protected function isDateFormatCorrect($date) : bool
    {
        return preg_match('/[\d]{4}-[\d]{2}-[\d]{2}/', $date) === 1;
    }
}
