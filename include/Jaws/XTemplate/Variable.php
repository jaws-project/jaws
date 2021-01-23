<?php
/**
 * Template engine variable class
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @see         https://github.com/harrydeluxe/php-liquid
 */
class Jaws_XTemplate_Variable
{
    /**
     * @var array The filters to execute on the variable
     */
    private $filters;

    /**
     * @var string The name of the variable
     */
    private $name;

    /**
     * @var string The markup of the variable
     */
    private $markup;

    /**
     * Constructor
     *
     * @param string $markup
     */
    public function __construct($markup)
    {
        $this->markup = $markup;

        $filterSep = new Jaws_Regexp('/' . Jaws_XTemplate::get('FILTER_SEPARATOR') . '\s*(.*)/m');
        $syntaxParser = new Jaws_Regexp('/(' . Jaws_XTemplate::get('QUOTED_FRAGMENT') . ')(.*)/m');
        $filterParser = new Jaws_Regexp(
            '/(?:\s+|' .
            Jaws_XTemplate::get('QUOTED_FRAGMENT') . '|' .
            Jaws_XTemplate::get('ARGUMENT_SEPARATOR') .
            ')+/'
        );
        $filterArgsRegex = new Jaws_Regexp(
            '/(?:' .
            Jaws_XTemplate::get('FILTER_ARGUMENT_SEPARATOR') . '|' .
            Jaws_XTemplate::get('ARGUMENT_SEPARATOR') .
            ')\s*((?:\w+\s*\:\s*)?' .
            Jaws_XTemplate::get('QUOTED_FRAGMENT') .
            ')/'
        );

        $this->filters = [];
        if ($syntaxParser->match($markup)) {
            $nameMarkup = $syntaxParser->matches[1];
            $this->name = $nameMarkup;
            $filterMarkup = $syntaxParser->matches[2];

            if ($filterSep->match($filterMarkup)) {
                $filterParser->matchAll($filterSep->matches[1]);

                foreach ($filterParser->matches[0] as $filter) {
                    $filter = trim($filter);
                    if (preg_match('/\w+/', $filter, $matches)) {
                        $filterName = $matches[0];
                        $filterArgsRegex->matchAll($filter);
                        $this->filters[] = $this->parseFilterExpressions($filterName, $filterArgsRegex->matches[1]);
                    }
                }
            }
        }

        if (Jaws_XTemplate::get('ESCAPE_BY_DEFAULT')) {
            // if auto_escape is enabled, and
            // - there's no raw filter, and
            // - no escape filter
            // - no other standard html-adding filter
            // then
            // - add a mandatory escape filter

            $addEscapeFilter = true;

            foreach ($this->filters as $filter) {
                // with empty filters set we would just move along
                if (in_array($filter[0], array('escape', 'escape_once', 'raw', 'newline_to_br'))) {
                    // if we have any raw-like filter, stop
                    $addEscapeFilter = false;
                    break;
                }
            }

            if ($addEscapeFilter) {
                $this->filters[] = array('escape', array());
            }
        }
    }

    /**
     * @param string $filterName
     * @param array $unparsedArgs
     * @return array
     */
    private static function parseFilterExpressions($filterName, array $unparsedArgs)
    {
        $filterArgs = array();
        $keywordArgs = array();

        $justTagAttributes = new Jaws_Regexp('/\A' . trim(Jaws_XTemplate::get('TAG_ATTRIBUTES'), '/') . '\z/');

        foreach ($unparsedArgs as $a) {
            if ($justTagAttributes->match($a)) {
                $keywordArgs[$justTagAttributes->matches[1]] = $justTagAttributes->matches[2];
            } else {
                $filterArgs[] = $a;
            }
        }

        if (count($keywordArgs)) {
            $filterArgs[] = $keywordArgs;
        }

        return array($filterName, $filterArgs);
    }

    /**
     * Renders the variable with the data in the context
     *
     * @param   object  $context
     *
     * @return  mixed|string
     */
    public function render($context)
    {
        $output = $context->get($this->name);
        foreach ($this->filters as $filter) {
            list($filtername, $filterArgKeys) = $filter;

            $filterArgValues = array();
            $keywordArgValues = array();

            foreach ($filterArgKeys as $arg_key) {
                if (is_array($arg_key)) {
                    foreach ($arg_key as $keywordArgName => $keywordArgKey) {
                        $keywordArgValues[$keywordArgName] = $context->get($keywordArgKey);
                    }

                    $filterArgValues[] = $keywordArgValues;
                } else {
                    $filterArgValues[] = $context->get($arg_key);
                }
            }

            $output = $context->invoke($filtername, $output, $filterArgValues);
        }

        return $output;
    }

}