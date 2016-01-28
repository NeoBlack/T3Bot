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
class BeerCommand extends AbstractCommand
{
    protected $commandName = 'beer';

    /**
     *
     */
    public function __construct()
    {
        $this->helpCommands['help'] = 'shows this help';
        $this->helpCommands['stats <username>'] = 'show beer counter for <username>';
        $this->helpCommands['for <username>'] = 'give <username> a T3Beer';
        $this->helpCommands['all'] = 'show all beer counter';
        $this->helpCommands['top10'] = 'show TOP 10';
    }

    /**
     * stats for all beer counter.
     *
     * @return string
     */
    protected function processAll()
    {
        return 'Yeah, '.$this->getBeerCountAll().' :t3beer: spend to all people';
    }

    /**
     * stats for TOP 10.
     *
     * @return string
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
     */
    protected function processStats()
    {
        $params = $this->params;
        array_shift($params);
        $username = trim($params[0]);
        if (substr($username, 0, 1) === '<' && substr($username, 1, 1) === '@') {
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
     */
    protected function processFor()
    {
        $from_user = $_REQUEST['user_id'];
        $params = $this->params;
        array_shift($params);
        $username = trim($params[0]);
        if (substr($username, 0, 1) === '<' && substr($username, 1, 1) === '@') {
            $username = str_replace(['<', '>', '@'], '', $username);
            $db = $this->getDatabaseConnection();

            $sql = 'INSERT INTO beers (to_user, from_user) VALUES (\''.$db->real_escape_string($username).'\', \''.$db->real_escape_string($from_user).'\')';
            $db->query($sql);

            return 'Yeah, one more :t3beer: for <@'.$username.'>'.chr(10).'<@'.$username.'> has received '.$this->getBeerCountByUsername($username).' :t3beer: so far';
        } else {
            return '*Sorry, a username must start with a @-sign:*';
        }
    }

    /**
     * @param $username
     *
     * @return int
     */
    protected function getBeerCountByUsername($username)
    {
        $db = $this->getDatabaseConnection();
        $sql = 'SELECT * FROM beers WHERE to_user = \''.$db->real_escape_string($username).'\'';
        $result = $db->query($sql);

        return $result->num_rows;
    }

    /**
     * @return int
     */
    protected function getBeerCountAll()
    {
        $db = $this->getDatabaseConnection();
        $sql = 'SELECT * FROM beers';
        $result = $db->query($sql);

        return $result->num_rows;
    }

    /**
     * @return array
     */
    protected function getBeerTop10()
    {
        $db = $this->getDatabaseConnection();
        $sql = 'SELECT count(to_user) as cnt, to_user FROM beers GROUP BY to_user ORDER BY cnt DESC LIMIT 10';
        $result = $db->query($sql);
        $results = array();
        while ($row = $result->fetch_assoc()) {
            $results[] = array(
                'cnt' => $row['cnt'],
                'username' => $row['to_user'],
            );
        }

        return $results;
    }
}
