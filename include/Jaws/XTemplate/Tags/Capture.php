<?php
/**
 * Class for capture tag
 * Captures the string inside of the opening and closing tags and assigns it to a variable
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/variable/
 */
class Jaws_XTemplate_Tags_Capture extends Jaws_XTemplate_Tags_Segmental
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    private $to;

    /**
     * Constructor
     *
     * @param   array   $tokens
     * @param   string  $markup
     *
     * @throws  Exception
     */
    public function __construct(array &$tokens, $markup)
    {
        $syntaxRegexp = new Jaws_Regexp('/(\w+)/');

        if ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            parent::__construct($tokens, $markup);
        } else {
            throw new Exception("Syntax Error in 'capture' - Valid syntax: capture [var] [value]");
        }
    }

    /**
     * Renders the block
     *
     * @param Context $context
     *
     * @return string
     */
    public function render($context)
    {
        $output = parent::render($context);

        $context->set($this->to, $output, true);
        return '';
    }

}