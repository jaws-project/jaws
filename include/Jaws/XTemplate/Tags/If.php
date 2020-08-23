<?php
/**
 * Class for tag if
 * An if statement
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
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
     * @param string $markup
     * @param array $tokens
     * @param   string  $rootPath
     */
    public function __construct($markup, array &$tokens, $rootPath = null)
    {
        $this->nodelist = & $this->nodelistHolders[count($this->blocks)];

        array_push($this->blocks, array('if', $markup, &$this->nodelist));

        parent::__construct($markup, $tokens, $rootPath);
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
        if ($tag == 'else' || $tag == 'elsif') {
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
     * * @throws  Exception
     * @return string
     */
    public function render($context)
    {
        $context->push();

        $logicalRegex = new Jaws_Regexp('/\s+(and|or)\s+/');
        $conditionalRegex = new Jaws_Regexp('/(' . Jaws_XTemplate::get('QUOTED_FRAGMENT') . ')\s*([=!<>a-z_]+)?\s*(' . Jaws_XTemplate::get('QUOTED_FRAGMENT') . ')?/');

        $result = '';
        foreach ($this->blocks as $block) {
            if ($block[0] == 'else') {
                $result = $this->renderAll($block[2], $context);

                break;
            }

            if ($block[0] == 'if' || $block[0] == 'elsif') {
                // Extract logical operators
                $logicalRegex->matchAll($block[1]);

                $logicalOperators = $logicalRegex->matches;
                $logicalOperators = $logicalOperators[1];
                // Extract individual conditions
                $temp = $logicalRegex->split($block[1]);

                $conditions = array();

                foreach ($temp as $condition) {
                    if ($conditionalRegex->match($condition)) {
                        $left = (isset($conditionalRegex->matches[1])) ? $conditionalRegex->matches[1] : null;
                        $operator = (isset($conditionalRegex->matches[2])) ? $conditionalRegex->matches[2] : null;
                        $right = (isset($conditionalRegex->matches[3])) ? $conditionalRegex->matches[3] : null;

                        array_push($conditions, array(
                            'left' => $left,
                            'operator' => $operator,
                            'right' => $right
                        ));
                    } else {
                        throw new Exception("Syntax Error in tag 'if' - Valid syntax: if [condition]");
                    }
                }
                if (count($logicalOperators)) {
                    // If statement contains and/or
                    $display = $this->interpretCondition($conditions[0]['left'], $conditions[0]['right'], $conditions[0]['operator'], $context);
                    foreach ($logicalOperators as $k => $logicalOperator) {
                        if ($logicalOperator == 'and') {
                            $display = ($display && $this->interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context));
                        } else {
                            $display = ($display || $this->interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context));
                        }
                    }
                } else {
                    // If statement is a single condition
                    $display = $this->interpretCondition($conditions[0]['left'], $conditions[0]['right'], $conditions[0]['operator'], $context);
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