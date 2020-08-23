<?php
/**
 * Class for tag tablerow
 * Generates an HTML table. Must be wrapped in opening <table> and closing </table> HTML tags.
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/iteration/
 */
class Jaws_XTemplate_Tags_Tablerow extends Jaws_XTemplate_TagSegmental
{
    /**
     * The variable name of the table tag
     *
     * @var string
     */
    public $variableName;

    /**
     * The collection name of the table tags
     *
     * @var string
     */
    public $collectionName;

    /**
     * Additional attributes
     *
     * @var array
     */
    public $attributes;

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
        parent::__construct($markup, $tokens, $rootPath);

        $syntax = new Jaws_Regexp('/(\w+)\s+in\s+(' . Jaws_XTemplate::get('VARIABLE_NAME') . ')/');

        if ($syntax->match($markup)) {
            $this->variableName = $syntax->matches[1];
            $this->collectionName = $syntax->matches[2];

            $this->extractAttributes($markup);
        } else {
            throw new Exception("Syntax Error in 'table_row loop' - Valid syntax: table_row [item] in [collection] cols:3");
        }
    }

    /**
     * Renders the current node
     *
     * @param   object  $context
     *
     * @throws  Exception
     * @return  string
     */
    public function render($context)
    {
        $collection = $context->get($this->collectionName);

        if ($collection instanceof \Traversable) {
            $collection = iterator_to_array($collection);
        }

        if (!is_array($collection)) {
            throw new Exception("Not an array");
        }

        // discard keys
        $collection = array_values($collection);

        if (isset($this->attributes['limit']) || isset($this->attributes['offset'])) {
            $limit = $context->get($this->attributes['limit']);
            $offset = $context->get($this->attributes['offset']);
            $collection = array_slice($collection, $offset, $limit);
        }

        $length = count($collection);

        $cols = isset($this->attributes['cols']) ? $context->get($this->attributes['cols']) : PHP_INT_MAX;

        $row = 1;
        $col = 0;

        $result = "<tr class=\"row1\">\n";

        $context->push();

        foreach ($collection as $index => $item) {
            $context->set($this->variableName, $item);
            $context->set('tablerowloop', array(
                'length' => $length,
                'index' => $index + 1,
                'index0' => $index,
                'rindex' => $length - $index,
                'rindex0' => $length - $index - 1,
                'first' => (int)($index == 0),
                'last' => (int)($index == $length - 1)
            ));

            $text = $this->renderAll($this->nodelist, $context);
            $break = isset($context->registers['break']);
            $continue = isset($context->registers['continue']);

            if ((!$break && !$continue) || strlen(trim($text)) > 0) {
                $result .= "<td class=\"col" . (++$col) . "\">$text</td>";
            }

            if ($col == $cols && !($index == $length - 1)) {
                $col = 0;
                $result .= "</tr>\n<tr class=\"row" . (++$row) . "\">\n";
            }

            if ($break) {
                unset($context->registers['break']);
                break;
            }
            if ($continue) {
                unset($context->registers['continue']);
            }
        }

        $context->pop();

        $result .= "</tr>\n";

        return $result;
    }

}