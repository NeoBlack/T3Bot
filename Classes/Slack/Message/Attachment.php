<?php
/**
 * T3Bot.
 *
 * @author Frank Nägler <frank.naegler@typo3.org>
 *
 * @link http://www.t3bot.de
 * @link http://wiki.typo3.org/T3Bot
 */
namespace T3Bot\Slack\Message;

/**
 * Class Attachment.
 */
class Attachment
{
    const COLOR_NOTICE = '#cccccc';
    const COLOR_INFO = '#5bc0de';
    const COLOR_GOOD = '#5cb85c';
    const COLOR_WARNING = '#f0ad4e';
    const COLOR_DANGER = '#d9534f';

    /**
     * Required plain-text summary of the attachment.
     * A plain-text summary of the attachment. This text will be used in clients that
     * don't show formatted text (eg. IRC, mobile notifications) and should not contain
     * any markup.
     *
     * @var string
     */
    protected $fallback;

    /**
     * Color for the attachment
     * An optional value that can either be one of good, warning, danger, or
     * any hex color code (eg. #439FE0). This value is used to color the border
     * along the left side of the message attachment.
     *
     * @var string
     */
    protected $color = self::COLOR_INFO;

    /**
     * This is optional text that appears above the message attachment block.
     *
     * @var string
     */
    protected $pretext;

    /**
     * Small text used to display the author's name.
     *
     * @var string
     */
    protected $author_name;

    /**
     * A valid URL that will hyperlink the author_name text mentioned above.
     * Will only work if author_name is present.
     *
     * @var string
     */
    protected $author_link;

    /**
     * A valid URL that displays a small 16x16px image to the left of the author_name text.
     * Will only work if author_name is present.
     *
     * @var string
     */
    protected $author_icon;

    /**
     * The title is displayed as larger, bold text near the top of a message attachment.
     *
     * @var string
     */
    protected $title;

    /**
     * By passing a valid URL in the title_link parameter (optional), the title text
     * will be hyperlinked.
     *
     * @var string
     */
    protected $title_link;

    /**
     * This is the main text in a message attachment, and can contain standard message
     * markup. The content will automatically collapse if it contains 700+ characters
     * or 5+ linebreaks, and will display a "Show more..." link to expand the content.
     *
     * @var string
     */
    protected $text;

    /**
     * Fields are defined as an array, and hashes contained within it will be displayed
     * in a table inside the message attachment.
     *
     * @var array<\T3Bot\Slack\Message\Attachment\Field>
     */
    protected $fields = array();

    /**
     * A valid URL to an image file that will be displayed inside a message attachment.
     * We currently support the following formats: GIF, JPEG, PNG, and BMP.
     * Large images will be resized to a maximum width of 400px or a maximum height of
     * 500px, while still maintaining the original aspect ratio.
     *
     * @var string
     */
    protected $image_url;

    /**
     * A valid URL to an image file that will be displayed as a thumbnail on the right
     * side of a message attachment. We currently support the following formats: GIF,
     * JPEG, PNG, and BMP.
     * The thumbnail's longest dimension will be scaled down to 75px while maintaining
     * the aspect ratio of the image. The filesize of the image must also be less than
     * 500 KB.
     *
     * @var string
     */
    protected $thumb_url;

    /**
     * Constructor for an attachment.
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @param string $fallback
     */
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getPretext()
    {
        return $this->pretext;
    }

    /**
     * @param string $pretext
     */
    public function setPretext($pretext)
    {
        $this->pretext = $pretext;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->author_name;
    }

    /**
     * @param string $author_name
     */
    public function setAuthorName($author_name)
    {
        $this->author_name = $author_name;
    }

    /**
     * @return string
     */
    public function getAuthorLink()
    {
        return $this->author_link;
    }

    /**
     * @param string $author_link
     */
    public function setAuthorLink($author_link)
    {
        $this->author_link = $author_link;
    }

    /**
     * @return string
     */
    public function getAuthorIcon()
    {
        return $this->author_icon;
    }

    /**
     * @param string $author_icon
     */
    public function setAuthorIcon($author_icon)
    {
        $this->author_icon = $author_icon;
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
    public function getTitleLink()
    {
        return $this->title_link;
    }

    /**
     * @param string $title_link
     */
    public function setTitleLink($title_link)
    {
        $this->title_link = $title_link;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }

    /**
     * @param string $image_url
     */
    public function setImageUrl($image_url)
    {
        $this->image_url = $image_url;
    }

    /**
     * @return string
     */
    public function getThumbUrl()
    {
        return $this->thumb_url;
    }

    /**
     * @param string $thumb_url
     */
    public function setThumbUrl($thumb_url)
    {
        $this->thumb_url = $thumb_url;
    }
}
