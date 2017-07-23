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
 * Class BottyCommand.
 */
class BottyCommand extends AbstractCommand
{
    /**
     * @var array
     */
    protected $cats = [':smiley_cat:', ':smile_cat:', ':heart_eyes_cat:', ':kissing_cat:',
        ':smirk_cat:', ':scream_cat:', ':crying_cat_face:', ':joy_cat:' , ':pouting_cat:', ];

    /**
     * @var array
     */
    protected $responses = [
        'daddy' => 'My daddy is Frank Nägler aka <@neoblack>',
        'n8' => 'Good night %s! :sleeping:',
        'nacht' => 'Good night %s! :sleeping:',
        'night' => 'Good night %s! :sleeping:',
        'hello' => 'Hello %s, nice to see you!',
        'hallo' => 'Hello %s, nice to see you!',
        'ciao' => 'Bye, bye %s, cu later alligator! :wave:',
        'cu' => 'Bye, bye %s, cu later alligator! :wave:',
        'thx' => 'You are welcome %s!',
        'thank' => 'You are welcome %s!',
        'drink' => 'Coffee or beer %s?',
        'coffee' => 'Here is a :coffee: for you %s!',
        'beer' => 'Here is a :t3beer: for you %s!',
        'coke' => 'Coke is unhealthy %s!',
        'cola' => 'Coke is unhealthy %s!',
        'cookie' => 'Here is a :cookie: for you %s!',
        'typo3' => ':typo3: TYPO3 CMS is the best open source CMS of the world!',
        'dark' => 'sure, we have cookies :cookie:',
        'cat' => 'ok, here is some cat content %s',
        'love' => 'I love you too, %s :kiss:',
    ];

    /**
     * @var array
     */
    protected $links = [
        'My Homepage' => 'http://www.t3bot.de',
        'Github' => 'https://github.com/NeoBlack/T3Bot',
        'Help for Commands' => 'http://wiki.typo3.org/T3Bot',
    ];

    /**
     * @return string
     */
    public function process() : string
    {
        return $this->processMessage();
    }

    /**
     * @return string
     */
    public function processMessage() : string
    {
        $message = strtolower($this->payload->getData()['text']);
        $username = '<@' . $this->payload->getData()['user'] . '>';

        if (strpos($message, 'help') !== false) {
            $result = [];
            foreach ($this->links as $text => $link) {
                $result[] = ":link: <{$link}|{$text}>";
            }
            return implode(' | ', $result);
        }
        foreach ($this->responses as $keyword => $response) {
            $value = $keyword === 'cat' ? $this->cats[array_rand($this->cats)] : $username;
            if (strpos($message, $keyword) !== false) {
                return sprintf($response, $value);
            }
        }

        return '';
    }
}
