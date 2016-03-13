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
 * Class BeerCommand.
 */
class BeerCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $commandName = 'beer';

    /**
     * @var array
     */
    protected $helpCommands = [
        'help' => 'shows this help',
        'stats [username]' => 'show beer counter for [username]',
        'for [username]' => 'give [username] a T3Beer',
        'all' => 'show all beer counter',
        'top10' => 'show TOP 10',
    ];

    /**
     * stats for all beer counter.
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processAll()
    {
        return 'Yeah, '.$this->getBeerCountAll().' :t3beer: spend to all people';
    }

    /**
     * stats for TOP 10.
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processTop10()
    {
        $rows = $this->getBeerTop10();
        $text = array('*Yeah, here are the TOP 10*');
        foreach ($rows as $row) {
            $text[] = '<@'.$row['username'].'> has received '.$row['cnt'].' :t3beer:';
        }

        return implode("\n", $text);
    }

    /**
     * stats for beer counter.
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processStats()
    {
        $params = $this->params;
        array_shift($params);
        $username = trim($params[0]);
        if (strpos($username, '<') === 0 && substr($username, 1, 1) === '@') {
            $username = str_replace(['<', '>', '@'], '', $username);

            return '<@'.$username.'> has received '.$this->getBeerCountByUsername($username).' :t3beer: so far';
        } else {
            return '*Sorry, a username must start with a @-sign:*';
        }
    }

    /**
     * give someone a beer.
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processFor()
    {
        $from_user = $this->payload->getData()['user'];
        $params = $this->params;
        array_shift($params);
        $username = trim($params[0]);
        if (strpos($username, '<') === 0 && substr($username, 1, 1) === '@') {
            $username = str_replace(['<', '>', '@'], '', $username);
            $this->getDatabaseConnection()->insert('beers', [
                'to_user' => $username,
                'from_user' => $from_user,
            ]);

            return 'Yeah, one more :t3beer: for <@'.$username.'>'.chr(10).'<@'.$username.'> has received '
                .$this->getBeerCountByUsername($username).' :t3beer: so far';
        } else {
            return '*Sorry, a username must start with a @-sign:*';
        }
    }

    /**
     * @param $username
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getBeerCountByUsername($username)
    {
        return count($this->getDatabaseConnection()
            ->fetchAll('SELECT * FROM beers WHERE to_user = ?', array($username)));
    }

    /**
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getBeerCountAll()
    {
        return count($this->getDatabaseConnection()
            ->fetchAll('SELECT * FROM beers'));
    }

    /**
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getBeerTop10()
    {
        return $this->getDatabaseConnection()->fetchAll(
            'SELECT count(*) as cnt, to_user as username FROM beers GROUP BY to_user ORDER BY cnt DESC LIMIT 10'
        );
    }
}
