<?php
/**
 * Class for switch statement tag
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/control-flow/
 */
class Jaws_XTemplate_Tags_Case extends Jaws_XTemplate_TagConditional
{
    /**
     * Stack of nodelists
     *
     * @var array
     */
    public $nodelists;

    /**
     * The nodelist for the else (default) nodelist
     *
     * @var array
     */
    public $elseNodelist;

    /**
     * The left value to compare
     *
     * @var string
     */
    public $left;

    /**
     * The current right value to compare
     *
     * @var mixed
     */
    public $right;

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
        $this->nodelists = array();
        $this->elseNodelist = array();

        parent::__construct($tokens, $markup);

        $syntaxRegexp = new Jaws_Regexp('/' . Jaws_XTemplate_Parser::get('QUOTED_FRAGMENT') . '/');

        if ($syntaxRegexp->match($markup)) {
            $this->left = $syntaxRegexp->matches[0];
        } else {
            throw new Exception("Syntax Error in tag 'case' - Valid syntax: case [condition]"); // harry
        }
    }

    /**
     * Pushes the last nodelist onto the stack
     */
    public function endTag()
    {
        $this->pushNodelist();
    }

    /**
     * Unknown tag handler
     *
     * @param string $tag
     * @param string $params
     * @param array $tokens
     *
     * @throws  Exception
     */
    public function unknownTag($tag, $params, array $tokens)
    {
        $whenSyntaxRegexp = new Jaws_Regexp('/' . Jaws_XTemplate_Parser::get('QUOTED_FRAGMENT') . '/');

        switch ($tag) {
            case 'when':
                // push the current nodelist onto the stack and prepare for a new one
                if ($whenSyntaxRegexp->match($params)) {
                    $this->pushNodelist();
                    $this->right = $whenSyntaxRegexp->matches[0];
                    $this->nodelist = array();
                } else {
                    throw new Exception("Syntax Error in tag 'case' - Valid when condition: when [condition]"); // harry
                }
                break;

            case 'else':
                // push the last nodelist onto the stack and prepare to receive the else nodes
                $this->pushNodelist();
                $this->right = null;
                $this->elseNodelist = &$this->nodelist;
                $this->nodelist = array();
                break;

            default:
                parent::unknownTag($tag, $params, $tokens);
        }
    }

    /**
     * Pushes the current right value and nodelist into the nodelist stack
     */
    public function pushNodelist()
    {
        if (!is_null($this->right)) {
            $this->nodelists[] = array($this->right, $this->nodelist);
        }
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     */
    public function render($context)
    {
        $output = ''; // array();
        $runElseBlock = true;

        foreach ($this->nodelists as $data) {
            list($right, $nodelist) = $data;

            if ($this->equalVariables($this->left, $right, $context)) {
                $runElseBlock = false;

                $context->push();
                $output .= $this->renderAll($nodelist, $context);
                $context->pop();
            }
        }

        if ($runElseBlock) {
            $context->push();
            $output .= $this->renderAll($this->elseNodelist, $context);
            $context->pop();
        }

        return $output;
    }

}