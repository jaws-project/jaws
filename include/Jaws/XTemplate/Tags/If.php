<?php
/**
 * Class for tag if
 * An if statement
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/control-flow/
 */
class Jaws_XTemplate_Tags_If extends Jaws_XTemplate_TagConditional
{
    /**
     * Array holding the nodes to render for each logical block
     *
     * @var array
     */
    private $nodelistHolders = array();

    /**
     * Array holding the block type, block markup (conditions) and block nodelist
     *
     * @var array
     */
    protected $blocks = array();

    /**
     * Constructor
     *
     * @param   string  $markup
     * @param   array   $tokens
     */
    public function __construct($markup, array &$tokens)
    {
        $this->nodelist = & $this->nodelistHolders[count($this->blocks)];

        array_push($this->blocks, array('if', $markup, &$this->nodelist));

        parent::__construct($markup, $tokens);
    }

    /**
     * Handler for unknown tags, handle else tags
     *
     * @param string $tag
     * @param array $params
     * @param array $tokens
     */
    public function unknownTag($tag, $params, array $tokens)
    {
        if ($tag == 'else' || $tag == 'elseif') {
            // Update reference to nodelistHolder for this block
            $this->nodelist = & $this->nodelistHolders[count($this->blocks) + 1];
            $this->nodelistHolders[count($this->blocks) + 1] = array();

            array_push($this->blocks, array($tag, $params, &$this->nodelist));
        } else {
            parent::unknownTag($tag, $params, $tokens);
        }
    }

    /**
     * Render the tag
     *
     * @param Context $context
     *
     * @throws  Exception
     * @return string
     */
    public function render($context)
    {
        $context->push();

        $logicalRegex = new Jaws_Regexp('/\s+(and|or)\s+/');
        $compareRegex = new Jaws_Regexp(
            '/\s*('. Jaws_XTemplate::get('COMPARISON_OPERATOR'). ')\s*/'
        );

        $result = '';
        foreach ($this->blocks as $block) {
            if ($block[0] == 'else') {
                $result = $this->renderAll($block[2], $context);

                break;
            }

            if ($block[0] == 'if' || $block[0] == 'elseif') {
                // Extract logical operators
                $logicalRegex->matchAll($block[1]);

                $logicalOperators = $logicalRegex->matches;
                $logicalOperators = $logicalOperators[1];
                // Extract individual conditions
                $temp = $logicalRegex->split($block[1]);

                $conditions = array();
                foreach ($temp as $condition) {
                    $parts = $compareRegex->split($condition, -1, PREG_SPLIT_DELIM_CAPTURE);
                    switch (count($parts)) {
                        case 1:
                            $left     = $parts[0];
                            $operator = null;
                            $right    = null;
                            break;

                        case 3:
                            $left     = $parts[0];
                            $operator = $parts[1];
                            $right    = $parts[2];
                            break;

                        default:
                            throw new Exception("Syntax Error in tag 'if' - Valid syntax: if [condition]");
                    }

                    array_push(
                        $conditions,
                        array(
                            'left' => $left,
                            'operator' => $operator,
                            'right' => $right
                        )
                    );
                }

                if (count($logicalOperators)) {
                    // If statement contains and/or
                    $display = $this->interpretCondition(
                        $conditions[0]['left'],
                        $conditions[0]['right'],
                        $conditions[0]['operator'],
                        $context
                    );
                    foreach ($logicalOperators as $k => $logicalOperator) {
                        if ($logicalOperator == 'and') {
                            $display = $display && $this->interpretCondition(
                                $conditions[$k + 1]['left'],
                                $conditions[$k + 1]['right'],
                                $conditions[$k + 1]['operator'],
                                $context
                            );
                        } else {
                            $display = $display || $this->interpretCondition(
                                $conditions[$k + 1]['left'],
                                $conditions[$k + 1]['right'],
                                $conditions[$k + 1]['operator'],
                                $context
                            );
                        }
                    }
                } else {
                    // If statement is a single condition
                    $display = $this->interpretCondition(
                        $conditions[0]['left'],
                        $conditions[0]['right'],
                        $conditions[0]['operator'],
                        $context
                    );
                }

                // hook for unless tag
                $display = $this->negateIfUnless($display);

                if ($display) {
                    $result = $this->renderAll($block[2], $context);
                    break;
                }
            }
        }

        $context->pop();

        return $result;
    }

    protected function negateIfUnless($display)
    {
        // no need to negate a condition in a regular `if` tag (will do that in `unless` tag)
        return $display;
    }

}