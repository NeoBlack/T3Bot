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
     * @return string
     */
    public function process()
    {
        return $this->processMessage();
    }

    /**
     * @return string
     */
    public function processMessage()
    {
        $message = strtolower($this->payload->getData()['text']);
        $username = '<@' . $this->payload->getData()['user'] . '>';

        if (strpos($message, 'help') !== false) {
            $links = [
                'My Homepage' => 'http://www.t3bot.de',
                'Github' => 'https://github.com/NeoBlack/T3Bot',
                'Help for Commands' => 'http://wiki.typo3.org/T3Bot',
            ];

            $result = [];
            foreach ($links as $text => $link) {
                $result[] = ":link: <{$link}|{$text}>";
            }

            return implode(' | ', $result);
        }

        $cats = [':smiley_cat:', ':smile_cat:', ':heart_eyes_cat:', ':kissing_cat:', ':smirk_cat:', ':scream_cat:',
            ':crying_cat_face:', ':joy_cat:' , ':pouting_cat:', ];

        $responses = [
            'daddy' => 'My daddy is Frank Nägler aka <@neoblack>',
            'n8' => 'Good night ' . $username . '! :sleeping:',
            'nacht' => 'Good night ' . $username . '! :sleeping:',
            'night' => 'Good night ' . $username . '! :sleeping:',
            'hello' => 'Hello ' . $username . ', nice to see you!',
            'hallo' => 'Hello ' . $username . ', nice to see you!',
            'ciao' => 'Bye, bye ' . $username . ', cu later alligator! :wave:',
            'cu' => 'Bye, bye ' . $username . ', cu later alligator! :wave:',
            'thx' => 'You are welcome ' . $username . '!',
            'thank' => 'You are welcome ' . $username . '!',
            'drink' => 'Coffee or beer ' . $username . '?',
            'coffee' => 'Here is a :coffee: for you ' . $username . '!',
            'beer' => 'Here is a :t3beer: for you ' . $username . '!',
            'coke' => 'Coke is unhealthy ' . $username . '!',
            'cola' => 'Coke is unhealthy ' . $username . '!',
            'cookie' => 'Here is a :cookie: for you ' . $username . '!',
            'typo3' => ':typo3: TYPO3 CMS is the best open source CMS of the world!',
            'dark' => 'sure, we have cookies :cookie:',
            'cat' => 'ok, here is some cat content ' . $cats[array_rand($cats)],
            'love' => 'I love you too, ' . $username . ':kiss:',
        ];
        $messageToSend = '';
        foreach ($responses as $keyword => $response) {
            if (strpos($message, $keyword) !== false) {
                $messageToSend = $response;
                break;
            }
        }

        return $messageToSend;
    }
}
