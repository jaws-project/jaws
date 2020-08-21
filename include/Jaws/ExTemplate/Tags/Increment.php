<?php
/**
 * Class for tag increment
 * Creates a new number variable, and increases its value by one every time it is called
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/variable/
 */
class Jaws_ExTemplate_Tags_Increment extends Jaws_ExTemplate_Tag
{
    /**
     * Name of the variable to increment
     *
     * @var string
     */
    private $toIncrement;

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param   string  $rootPath
     *
     * * @throws  Exception
     */
    public function __construct($markup, array &$tokens, $rootPath = null)
    {
        $syntax = new Jaws_Regexp('/(' . Jaws_ExTemplate::get('VARIABLE_NAME') . ')/');

        if ($syntax->match($markup)) {
            $this->toIncrement = $syntax->matches[0];
        } else {
            throw new Exception("Syntax Error in 'increment' - Valid syntax: increment [var]");
        }
    }

    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return string|void
     */
    public function render($context)
    {
        // If the value is not set in the environment check to see if it
        // exists in the context, and if not set it to -1
        if (!isset($context->environments[$this->toIncrement])) {
            // check for a context value
            $from_context = $context->get($this->toIncrement);

            // we already have a value in the context
            $context->environments[$this->toIncrement] = (null !== $from_context) ? $from_context : -1;
        }

        // Increment the value
        $context->environments[$this->toIncrement]++;

        return '';
    }

}