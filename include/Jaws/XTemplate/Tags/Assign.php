<?php
/**
 * Performs an assignment of one variable to another
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/variable/
 */
class Jaws_XTemplate_Tags_Assign extends Jaws_XTemplate_Tag
{
    /**
     * @var string The variable to assign from
     */
    private $from;

    /**
     * @var string The variable to assign to
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
        $syntaxRegexp = new Jaws_Regexp('/(\w+)\s*=\s*(.*)\s*/');

        if ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            $this->from = new Jaws_XTemplate_Variable($syntaxRegexp->matches[2]);
        } else {
            throw new Exception("Syntax Error in 'assign' - Valid syntax: assign [var] = [source]");
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
        $output = $this->from->render($context);

        $context->set($this->to, $output, true);
    }

}