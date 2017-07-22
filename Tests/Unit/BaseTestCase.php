<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Tests\Unit;

use MyProject\Proxies\__CG__\stdClass;

/**
 * Class BaseTestCase.
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Call protected/private method of a class.
     *
     * @param mixed  &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param stdClass $object
     * @param string   $property
     * @param mixed    $value
     */
    public function setProperty(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $_property = $reflection->getProperty($property);
        $_property->setAccessible(true);
        $object->$property = $value;
    }
}
