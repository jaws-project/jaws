<?php
/**
 * Class for cycle tag
 * Cycles between a list of values; calls to the tag will return each value in turn
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/iteration/
 */
class Jaws_XTemplate_Tags_Cycle extends Jaws_XTemplate_Tag
{
    /**
     * @var string The name of the cycle; if none is given one is created using the value list
     */
    private $name;

    /**
     * @var Variable[] The variables to cycle between
     */
    private $variables = array();

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
        $simpleSyntax = new Jaws_Regexp("/" . Jaws_XTemplate::get('QUOTED_FRAGMENT') . "/");
        $namedSyntax = new Jaws_Regexp("/(" . Jaws_XTemplate::get('QUOTED_FRAGMENT') . ")\s*\:\s*(.*)/");

        if ($namedSyntax->match($markup)) {
            $this->variables = $this->variablesFromString($namedSyntax->matches[2]);
            $this->name = $namedSyntax->matches[1];
        } elseif ($simpleSyntax->match($markup)) {
            $this->variables = $this->variablesFromString($markup);
            $this->name = "'" . implode($this->variables) . "'";
        } else {
            throw new Exception("Syntax Error in 'cycle' - Valid syntax: cycle [name :] var [, var2, var3 ...]");
        }
    }

    /**
     * Renders the tag
     *
     * @var Context $context
     * @return string
     */
    public function render($context)
    {
        $context->push();

        $key = $context->get($this->name);

        if (isset($context->registers['cycle'][$key])) {
            $iteration = $context->registers['cycle'][$key];
        } else {
            $iteration = 0;
        }

        $result = $context->get($this->variables[$iteration]);

        $iteration += 1;

        if ($iteration >= count($this->variables)) {
            $iteration = 0;
        }

        $context->registers['cycle'][$key] = $iteration;

        $context->pop();

        return $result;
    }

    /**
     * Extract variables from a string of markup
     *
     * @param string $markup
     *
     * @return array;
     */
    private function variablesFromString($markup)
    {
        $regexp = new Jaws_Regexp('/\s*(' . Jaws_XTemplate::get('QUOTED_FRAGMENT') . ')\s*/');
        $parts = explode(',', $markup);
        $result = array();

        foreach ($parts as $part) {
            $regexp->match($part);

            if (!empty($regexp->matches[1])) {
                $result[] = $regexp->matches[1];
            }
        }

        return $result;
    }

}