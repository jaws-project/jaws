<?php
/**
 * Class for tag for
 * Loops over an array, assigning the current value to a given variable
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/iteration/
 */
class Jaws_XTemplate_Tags_For extends Jaws_XTemplate_TagSegmental
{
    /**
     * Stack of nodelists
     *
     * @var array
     */
    public $nodelists;

    /**
     * The nodelist for the else nodelist
     *
     * @var array
     */
    private $elseNodelist;

    /**
     * @var array The collection to loop over
     */
    private $collection;

    /**
     * @var string The variable name to assign collection elements to
     */
    private $variableName;

    /**
     * @var string The start index of collection
     */
    private $start;

    /**
     * @var string The name of the loop, which is a compound of the collection and variable names
     */
    private $name;

    /**
     * @var string The type of the loop (collection or digit)
     */
    private $type = 'collection';

    /**
     * Constructor
     *
     * @param   object  $tpl    Jaws_XTemplate object
     * @param   array   $tokens
     * @param   string  $markup
     *
     * @throws  Exception
     */
    public function __construct(&$tpl, array &$tokens, $markup)
    {
        $this->nodelists[] = array('for', $markup, &$this->nodelist);

        $syntaxRegexp = new Jaws_Regexp(
            '/(\w+)\s+in\s+\((\d+|' .
            Jaws_XTemplate_Parser::get('VARIABLE_NAME') .
            ')\s*\.\.\s*(\d+|' .
            Jaws_XTemplate_Parser::get('VARIABLE_NAME') .
            ')\)/'
        );
        if ($syntaxRegexp->match($markup)) {
            $this->type = 'digit';
            $this->variableName = $syntaxRegexp->matches[1];
            $this->start = $syntaxRegexp->matches[2];
            $this->collection = $syntaxRegexp->matches[3];
            $this->name = $syntaxRegexp->matches[1].'-digit';
        } else {
            $syntaxRegexp = new Jaws_Regexp(
                '/(\w+)\s+in\s+(' . Jaws_XTemplate_Parser::get('QUOTED_FRAGMENT') . ')(.*)/m'
            );
            if ($syntaxRegexp->match($markup)) {
                $this->variableName = $syntaxRegexp->matches[1];
                $this->name = $syntaxRegexp->matches[1] . '-' . $syntaxRegexp->matches[2];
                $this->collection = new Jaws_XTemplate_Variable($syntaxRegexp->matches[2] . $syntaxRegexp->matches[3]);
            } else {
                throw new Exception("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection]");
            }
        }

        parent::__construct($tpl, $tokens, $markup);
    }

    /**
     * Handler for unknown tags, handle else tag
     *
     * @param string $tag
     * @param array $params
     * @param array $tokens
     */
    public function unknownTag($tag, $params, array $tokens)
    {
        if ($tag == 'else') {
            $this->nodelist = & $this->elseNodelist;
            $this->nodelists[] = array($tag, $params, &$this->elseNodelist);
        } else {
            parent::unknownTag($tag, $params, $tokens);
        }
    }

    /**
     * Renders the tag
     *
     * @param   object  $context
     *
     * @return  null|string
     */
    public function render($context)
    {
        if (!isset($context->registers['for'])) {
            $context->registers['for'] = array();
        }

        foreach ($this->nodelists as $nodelist) {
            if ($nodelist[0] == 'for') {
                if ($this->type == 'digit') {
                    $result = $this->renderDigit($nodelist[2], $context);
                } else {
                    // that's the default
                    $result = $this->renderCollection($nodelist[2], $context);
                }
            } else {
                if($result == null) {
                    $result = $this->renderAll($nodelist[2], $context);
                }
            }
        }

        return $result;
    }

    private function renderCollection($nodelist, $context)
    {
        $collection = $this->collection->render($context);
        if ($collection instanceof \Traversable) {
            $collection = iterator_to_array($collection);
        }

        if (is_null($collection) || !is_array($collection) || count($collection) == 0) {
            return null;
        }

        $index = 0;
        $result = '';
        $length = count($collection);
        $context->push();

        foreach ($collection as $key => $item) {
            $context->set($this->variableName, $item);
            $forloop = array(
                'key'     => $key,
                'name'    => $this->name,
                'length'  => $length,
                'index'   => $index + 1,
                'index0'  => $index,
                'rindex'  => $length - $index,
                'rindex0' => $length - $index - 1,
                'first'   => (int)($index == 0),
                'last'    => (int)($index == $length - 1),
                'parent'  => $context->parentContext()
            );
            // previous
            if (false !== prev($collection)) {
                $forloop['prev'] = array(
                    'key'     => key($collection),
                    'name'    => $this->name,
                    'length'  => $length,
                    'index'   => $index,
                    'index0'  => $index - 1,
                    'rindex'  => $length - $index - 1,
                    'rindex0' => $length - $index - 2,
                    'first'   => (int)(($index - 1) == 0),
                    'last'    => (int)($index == $length),
                    'parent'  => &$forloop['parent']
                );
                next($collection);
            } else {
                $forloop['prev'] = null;
                reset($collection);
            }
            // next
            if (false !== next($collection)) {
                $forloop['next'] = array(
                    'key'     => key($collection),
                    'name'    => $this->name,
                    'length'  => $length,
                    'index'   => $index + 2,
                    'index0'  => $index + 1,
                    'rindex'  => $length - $index + 1,
                    'rindex0' => $length - $index,
                    'first'   => (int)(($index + 1) == 0),
                    'last'    => (int)(($index + 1) == $length - 1),
                    'parent'  => &$forloop['parent']
                );
                prev($collection);
            } else {
                $forloop['next'] = null;
                end($collection);
            }
            // forloop
            $context->set('forloop', $forloop);

            $result .= $this->renderAll($nodelist, $context);

            $index++;
            next($collection);

            if (isset($context->registers['break'])) {
                unset($context->registers['break']);
                break;
            }
            if (isset($context->registers['continue'])) {
                unset($context->registers['continue']);
            }
        }

        $context->pop();

        return $result;
    }

    private function renderDigit($nodelist, $context)
    {
        $start = $this->start;
        if (!is_integer($this->start)) {
            $start = $context->get($this->start);
        }

        $end = $this->collection;
        if (!is_integer($this->collection)) {
            $end = $context->get($this->collection);
        }

        $range = array($start, $end);

        $index = 0;
        $result = null;
        $length = $range[1] - $range[0];
        $context->push();

        for ($i = $range[0]; $i <= $range[1]; $i++) {
            $context->set($this->variableName, $i);
            $forloop = array(
                'key'     => $index,
                'name'    => $this->name,
                'length'  => $length,
                'index'   => $index + 1,
                'index0'  => $index,
                'rindex'  => $length - $index,
                'rindex0' => $length - $index - 1,
                'first'   => (int)($index == 0),
                'last'    => (int)($index == $length - 1),
                'parent'  => $context->parentContext()
            );
            // previous
            if (($i - 1) >= $range[0]) {
                $forloop['prev'] = array(
                    'key'     => $index - 1,
                    'name'    => $this->name,
                    'length'  => $length,
                    'index'   => $index,
                    'index0'  => $index - 1,
                    'rindex'  => $length - $index - 1,
                    'rindex0' => $length - $index - 2,
                    'first'   => (int)(($index - 1) == 0),
                    'last'    => (int)($index == $length),
                    'parent'  => &$forloop['parent']
                );
            } else {
                $forloop['prev'] = null;
            }
            // next
            if (($i + 1) > $range[1]) {
                $forloop['next'] = array(
                    'key'     => $index + 1,
                    'name'    => $this->name,
                    'length'  => $length,
                    'index'   => $index + 2,
                    'index0'  => $index + 1,
                    'rindex'  => $length - $index + 1,
                    'rindex0' => $length - $index,
                    'first'   => (int)(($index + 1) == 0),
                    'last'    => (int)(($index + 1) == $length - 1),
                    'parent'  => &$forloop['parent']
                );
            } else {
                $forloop['next'] = null;
            }
            // forloop
            $context->set('forloop', $forloop);

            $result .= $this->renderAll($nodelist, $context);

            $index++;

            if (isset($context->registers['break'])) {
                unset($context->registers['break']);
                break;
            }
            if (isset($context->registers['continue'])) {
                unset($context->registers['continue']);
            }
        }

        $context->pop();

        return $result;
    }

}