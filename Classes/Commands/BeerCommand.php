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
 * Class BeerCommand.
 *
 * @property string commandName
 * @property array helpCommands
 */
class BeerCommand extends AbstractCommand
{
    /**
     * AbstractCommand constructor.
     *
     * @param Payload        $payload
     * @param RealTimeClient $client
     */
    public function __construct(Payload $payload, RealTimeClient $client)
    {
        $this->commandName = 'beer';
        $this->helpCommands = [
            'help' => 'shows this help',
            'stats [username]' => 'show beer counter for [username]',
            'for [username]' => 'give [username] a T3Beer',
            'all' => 'show all beer counter',
            'top10' => 'show TOP 10',
        ];
        parent::__construct($payload, $client);
    }

    /**
     * stats for all beer counter.
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processAll() : string
    {
        return 'Yeah, ' . $this->getBeerCountAll() . ' :t3beer: spend to all people';
    }

    /**
     * stats for TOP 10.
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processTop10() : string
    {
        $rows = $this->getBeerTop10();
        $text = ['*Yeah, here are the TOP 10*'];
        foreach ($rows as $row) {
            $text[] = '<@' . $row['username'] . '> has received ' . $row['cnt'] . ' :t3beer:';
        }

        return implode(chr(10), $text);
    }

    /**
     * stats for beer counter.
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processStats() : string
    {
        $params = $this->params;
        array_shift($params);
        $username = trim($params[0]);
        if (strpos($username, '<') === 0 && $username[1] === '@') {
            $username = str_replace(['<', '>', '@'], '', $username);

            return '<@' . $username . '> has received ' . $this->getBeerCountByUsername($username) . ' :t3beer: so far';
        }
        return '*Sorry, a username must start with a @-sign:*';
    }

    /**
     * give someone a beer.
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processFor() : string
    {
        $from_user = $this->payload->getData()['user'];
        $params = $this->params;
        array_shift($params);
        $username = trim($params[0]);
        if (strpos($username, '<') === 0 && $username[1] === '@') {
            $username = str_replace(['<', '>', '@'], '', $username);
            $queryBuilder = $this->getDatabaseConnection()
                ->createQueryBuilder();
            $record = $queryBuilder
                ->select('tstamp')
                ->from('beers')
                ->where($queryBuilder->expr()->eq('to_user', $queryBuilder->createNamedParameter($username)))
                ->andWhere($queryBuilder->expr()->eq('from_user', $queryBuilder->createNamedParameter($from_user)))
                ->orderBy('tstamp', 'DESC')
                ->setMaxResults(1)
                ->execute()
                ->fetch();
            $addBeer = false;
            if (empty($record)) {
                $addBeer = true;
            } elseif ($record['tstamp'] + 86400 < time()) {
                $addBeer = true;
            }
            if ($addBeer) {
                $data = [
                    'to_user' => $username,
                    'from_user' => $from_user,
                    'tstamp' => time()
                ];
                $this->getDatabaseConnection()->insert('beers', $data);
                return 'Yeah, one more :t3beer: for <@' . $username . '>' . chr(10) . '<@' . $username . '> has received '
                    . $this->getBeerCountByUsername($username) . ' :t3beer: so far';
            }
            return 'You spend one :t3beer: to <@' . $username . '> within in last 24 hours. Too much beer is unhealthy ;)';
        }
        return '*Sorry, a username must start with a @-sign:*';
    }

    /**
     * @param $username
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getBeerCountByUsername($username) : int
    {
        $queryBuilder = $this->getDatabaseConnection()
            ->createQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from('beers')
            ->where($queryBuilder->expr()->eq('to_user', $queryBuilder->createNamedParameter($username)))
            ->execute()
            ->rowCount();
    }

    /**
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getBeerCountAll() : int
    {
        return $this->getDatabaseConnection()
            ->createQueryBuilder()
            ->select('*')
            ->from('beers')
            ->execute()
            ->rowCount();
    }

    /**
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getBeerTop10() : array
    {
        return $this->getDatabaseConnection()
            ->createQueryBuilder()
            ->select('count(*) AS cnt', 'to_user AS username')
            ->from('beers')
            ->groupBy('to_user')
            ->orderBy('cnt', 'DESC')
            ->setMaxResults(10)
            ->execute()
            ->fetchAll();
    }
}
