<?php
/**
 * Class for tag for
 * Loops over an array, assigning the current value to a given variable
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ExTemplate_Tags_For extends Jaws_ExTemplate_Tags_Segmental
{
    /**
     * @var array The collection to loop over
     */
    private $collectionName;

    /**
     * @var string The variable name to assign collection elements to
     */
    private $variableName;

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
     * @param string $markup
     * @param array $tokens
     * @param   string  $rootPath
     *
     * @throws  Exception
     */
    public function __construct($markup, array &$tokens, $rootPath = null)
    {
        parent::__construct($markup, $tokens, $rootPath);

        $syntaxRegexp = new Jaws_Regexp('/(\w+)\s+in\s+(' . Jaws_ExTemplate::get('VARIABLE_NAME') . ')/');

        if ($syntaxRegexp->match($markup)) {
            $this->variableName = $syntaxRegexp->matches[1];
            $this->collectionName = $syntaxRegexp->matches[2];
            $this->name = $syntaxRegexp->matches[1] . '-' . $syntaxRegexp->matches[2];
            $this->extractAttributes($markup);
        } else {
            $syntaxRegexp = new Jaws_Regexp('/(\w+)\s+in\s+\((\d+|' . Jaws_ExTemplate::get('VARIABLE_NAME') . ')\s*\.\.\s*(\d+|' . Jaws_ExTemplate::get('VARIABLE_NAME') . ')\)/');
            if ($syntaxRegexp->match($markup)) {
                $this->type = 'digit';
                $this->variableName = $syntaxRegexp->matches[1];
                $this->start = $syntaxRegexp->matches[2];
                $this->collectionName = $syntaxRegexp->matches[3];
                $this->name = $syntaxRegexp->matches[1].'-digit';
                $this->extractAttributes($markup);
            } else {
                throw new Exception("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection]");
            }
        }
    }

    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return null|string
     */
    public function render($context)
    {
        if (!isset($context->registers['for'])) {
            $context->registers['for'] = array();
        }

        if ($this->type == 'digit') {
            return $this->renderDigit($context);
        }

        // that's the default
        return $this->renderCollection($context);
    }

    private function renderCollection($context)
    {
        $collection = $context->get($this->collectionName);

        if ($collection instanceof \Traversable) {
            $collection = iterator_to_array($collection);
        }

        if (is_null($collection) || !is_array($collection) || count($collection) == 0) {
            return '';
        }

        $range = array(0, count($collection));

        if (isset($this->attributes['limit']) || isset($this->attributes['offset'])) {
            $offset = 0;

            if (isset($this->attributes['offset'])) {
                $offset = ($this->attributes['offset'] == 'continue') ? $context->registers['for'][$this->name] : $context->get($this->attributes['offset']);
            }

            $limit = (isset($this->attributes['limit'])) ? $context->get($this->attributes['limit']) : null;
            $rangeEnd = $limit ? $limit : count($collection) - $offset;
            $range = array($offset, $rangeEnd);

            $context->registers['for'][$this->name] = $rangeEnd + $offset;
        }

        $result = '';
        $segment = array_slice($collection, $range[0], $range[1]);
        if (!count($segment)) {
            return null;
        }

        $context->push();
        $length = count($segment);

        $index = 0;
        foreach ($segment as $key => $item) {
            $value = is_numeric($key) ? $item : array($key, $item);
            $context->set($this->variableName, $value);
            $context->set('forloop', array(
                    'name' => $this->name,
                    'length' => $length,
                    'index' => $index + 1,
                    'index0' => $index,
                    'rindex' => $length - $index,
                    'rindex0' => $length - $index - 1,
                    'first' => (int)($index == 0),
                    'last' => (int)($index == $length - 1)
            ));

            $result .= $this->renderAll($this->nodelist, $context);

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

    private function renderDigit($context)
    {
        $start = $this->start;
        if (!is_integer($this->start)) {
            $start = $context->get($this->start);
        }

        $end = $this->collectionName;
        if (!is_integer($this->collectionName)) {
            $end = $context->get($this->collectionName);
        }

        $range = array($start, $end);

        $context->push();
        $result = '';
        $index = 0;
        $length = $range[1] - $range[0];
        for ($i = $range[0]; $i <= $range[1]; $i++) {
            $context->set($this->variableName, $i);
            $context->set('forloop', array(
                'name'        => $this->name,
                'length'    => $length,
                'index'        => $index + 1,
                'index0'    => $index,
                'rindex'    => $length - $index,
                'rindex0'    => $length - $index - 1,
                'first'        => (int)($index == 0),
                'last'        => (int)($index == $length - 1)
            ));

            $result .= $this->renderAll($this->nodelist, $context);

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