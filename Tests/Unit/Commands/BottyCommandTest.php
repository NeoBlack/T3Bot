<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */

namespace T3Bot\Tests\Unit\Commands;

use T3Bot\Commands\BottyCommand;
use T3Bot\Tests\Unit\BaseCommandTestCase;

/**
 * Class BottyCommandTest.
 */
class BottyCommandTest extends BaseCommandTestCase
{
    /**
     * Data provider.
     *
     * @return array
     */
    public function testDataProvider()
    {
        $username = '<@U54321>';

        return array(
            'daddy' => ['daddy', 'My daddy is Frank Nägler aka <@neoblack>'],
            'n8' => ['n8', 'Good night '.$username.'! :sleeping:'],
            'nacht' => ['nacht', 'Good night '.$username.'! :sleeping:'],
            'night' => ['night', 'Good night '.$username.'! :sleeping:'],
            'hello' => ['hello', 'Hello '.$username.', nice to see you!'],
            'hallo' => ['hallo', 'Hello '.$username.', nice to see you!'],
            'ciao' => ['ciao', 'Bye, bye '.$username.', cu later alligator! :wave:'],
            'cu' => ['cu', 'Bye, bye '.$username.', cu later alligator! :wave:'],
            'thx' => ['thx', 'You are welcome '.$username.'!'],
            'thank' => ['thank', 'You are welcome '.$username.'!'],
            'drink' => ['drink', 'Coffee or beer '.$username.'?'],
            'coffee' => ['coffee', 'Here is a :coffee: for you '.$username.'!'],
            'beer' => ['beer', 'Here is a :t3beer: for you '.$username.'!'],
            'coke' => ['coke', 'Coke is unhealthy '.$username.'!'],
            'cola' => ['cola', 'Coke is unhealthy '.$username.'!'],
            'cookie' => ['cookie', 'Here is a :cookie: for you '.$username.'!'],
            'typo3' => ['typo3', ':typo3: TYPO3 CMS is the best open source CMS of the world!'],
            'dark' => ['dark', 'sure, we have cookies :cookie:'],
            //'cat' => ['cat', 'ok, here is some cat content '.$cats[array_rand($cats)]],
            'love' => ['love', 'I love you too, '.$username.':kiss:'],
            'no-matching' => ['foobar', null],
        );
    }

    /**
     * @test
     * @dataProvider testDataProvider
     */
    public function processShowReturnsCorrectResponseForKnownKeywords($keyword, $response)
    {
        $this->initCommandWithPayload(BottyCommand::class, [
            'user' => 'U54321',
            'text' => 'botty '.$keyword,
        ]);
        $result = $this->command->process();
        if ($response === null) {
            $this->assertNull($response);
        } else {
            $this->assertStringStartsWith($response, $result);
        }
    }

    /**
     * @test
     * @dataProvider testDataProvider
     */
    public function processShowReturnsCorrectResponseForHelpKeyword()
    {
        $this->initCommandWithPayload(BottyCommand::class, [
            'user' => 'U54321',
            'text' => 'botty help',
        ]);
        $result = $this->command->process();
        $this->assertEquals(':link: <http://www.t3bot.de|My Homepage> | :link: <https://github.com/NeoBlack/T3Bot|Github> | :link: <http://wiki.typo3.org/T3Bot|Help for Commands>', $result);
    }
}
