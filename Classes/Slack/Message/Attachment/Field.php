<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Slack\Message\Attachment;

/**
 * Class Field.
 *
 * Fields are defined as an array, and hashes contained within it will
 * be displayed in a table inside the message attachment.
 */
class Field
{
    /**
     * Shown as a bold heading above the value text. It cannot contain
     * markup and will be escaped for you.
     *
     * @var string
     */
    protected $title;

    /**
     * The text value of the field. It may contain standard message markup
     * and must be escaped as normal. May be multi-line.
     *
     * @var string
     */
    protected $value;

    /**
     * An optional flag indicating whether the value is short enough to be
     * displayed side-by-side with other values.
     *
     * @var bool
     */
    protected $short = false;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if (!empty($data)) {
            foreach ($data as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                }
            }
        }
    }

    /**
     * @return \stdClass
     */
    public function asStdClass()
    {
        $result = new \stdClass();
        $properties = get_class_vars(get_class($this));
        foreach ($properties as $property => $value) {
            if (!empty($this->$property)) {
                $result->$property = $this->$property;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isShort()
    {
        return $this->short;
    }

    /**
     * @param bool $short
     */
    public function setShort($short)
    {
        $this->short = $short;
    }
}
