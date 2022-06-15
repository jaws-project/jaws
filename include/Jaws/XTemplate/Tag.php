<?php
/**
 * Base class for template tags
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @see         https://github.com/harrydeluxe/php-liquid
 */
abstract class Jaws_XTemplate_Tag
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * The markup for the tag
     *
     * @var string
     */
    protected $markup;

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Constructor
     *
     * @param   string  $markup
     * @param   array   $tokens
     */
    public function __construct(array &$tokens, $markup)
    {
        $this->app = Jaws::getInstance();

        $this->markup = $markup;
        $this->parse($tokens);
    }

    /**
     * Parse the given tokens.
     *
     * @param   array   $tokens
     */
    public function parse(array &$tokens)
    {
        // Do nothing by default
    }

    /**
     * Render the tag with the given context.
     *
     * @param   object  $context    Jaws_XTemplate_Context object
     *
     * @return  string
     */
    abstract public function render($context);

    /**
     * Extracts tag attributes from a markup string.
     *
     * @param   string  $markup
     *
     * @return  void
     */
    protected function extractAttributes($markup)
    {
        $this->attributes = array();

        $attributeRegexp = new Jaws_Regexp(Jaws_XTemplate_Parser::get('TAG_ATTRIBUTES'));

        $attributeRegexp->scan($markup);

        foreach ($attributeRegexp->matches as $match) {
            $this->attributes[$match[0]] = $match[1];
        }
    }

    /**
     * Returns the name of the tag.
     *
     * @return  string
     */
    protected function name()
    {
        return strtolower(get_class($this));
    }

}