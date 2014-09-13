# Hi, I am T3Bot

I am in each channel on [slack](http://typo3.slack.com/), even if you do not see me. Talk to me by start a message with @T3Bot or with the command prefix.

A list of the commands I understand can be found in the [Wiki](http://wiki.typo3.org/T3Bot).

## Developer Notes

If you want to contribute, fork this repository and send a pull request.

### Command logic
Each command is capsulated into a command class. If you want to add new command, create a new command class in folder `lib/Commands/`.
The command class must extend the class `\T3Bot\Commands\AbstractCommand`.
For each subcommand simply add a `processXyz()` method, where Xyz is the subcommand to lowercase but first character uppercase.
Each process method has to return a string.

Here is an example demo class:

```php
namespace T3Bot\Commands;

class DemoCommand extends AbstractCommand {
  protected $commandName = 'demo'; // set this for generating the correct help

  /**
   * use the contructor to fill the helpCommands array
   */
  public function __construct() {
    $this->helpCommands['help'] = 'shows this help';
    $this->helpCommands['test <name>'] = 'Respond with Hello <name>';
  }

  /**
   * process the follwoing commands:
   * @T3Bot demo test name
   *
   * @return string
   */
  protected function processTest() {
    $name = isset($this->params[1]) ? intval($this->params[1]) : null;
    if ($name == null) {
        return "Hey, I need your name";
    }
    return "Hello {$name}";
  }
}
```
