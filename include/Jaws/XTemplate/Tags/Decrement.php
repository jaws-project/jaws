<?php
/**
 * Class for tag decrement
 * Used to decrement a counter into a template
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/variable/
 */
class Jaws_XTemplate_Tags_Decrement extends Jaws_XTemplate_Tag
{
    /**
     * Name of the variable to decrement
     *
     * @var int
     */
    private $toDecrement;

    /**
     * Constructor
     *
     * @param   string  $markup
     * @param   array   $tokens
     *
     * @throws  Exception
     */
    public function __construct($markup, array &$tokens)
    {
        $syntax = new Jaws_Regexp('/(' . Jaws_XTemplate::get('VARIABLE_NAME') . ')/');

        if ($syntax->match($markup)) {
            $this->toDecrement = $syntax->matches[0];
        } else {
            throw new Exception("Syntax Error in 'decrement' - Valid syntax: decrement [var]");
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
        // if the value is not set in the environment check to see if it
        // exists in the context, and if not set it to 0
        if (!isset($context->environments[$this->toDecrement])) {
            // check for a context value
            $fromContext = $context->get($this->toDecrement);

            // we already have a value in the context
            $context->environments[$this->toDecrement] = (null !== $fromContext) ? $fromContext : 0;
        }

        // decrement the environment value
        $context->environments[$this->toDecrement]--;

        return '';
    }

}