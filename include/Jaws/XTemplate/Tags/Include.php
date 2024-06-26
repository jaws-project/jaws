<?php
/**
 * Class for tag include
 * Includes another, partial, template
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Tags_Include extends Jaws_XTemplate_Tag
{
    /**
     * @var string The name of the template
     */
    private $templateName;

    /**
     * @var string The base path of the template
     */
    private $templatePath;

    /**
     * @var mixed The variable name passed to the child template
     */
    private $variableName;

    /**
     * @var string The alias name of the variable
     */
    private $aliasName;

    /**
     * @var bool True if the variable is a collection
     */
    private $collection;

    /**
     * @var Document The Document that represents the included template
     */
    private $document;

    /**
     * @var string The Source Hash
     */
    protected $hash;

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
        $regex = new Jaws_Regexp(
            '/' .
            '(' . Jaws_XTemplate_Parser::get('QUOTED_FRAGMENT') . '+)' .
            '(?:\s+path\s+(' . Jaws_XTemplate_Parser::get('QUOTED_FRAGMENT') . '+))?'.
            '(?:\s+(with|for)\s+(' . Jaws_XTemplate_Parser::get('QUOTED_FRAGMENT') . '+))?'.
            '(?:\s+as\s+(' . Jaws_XTemplate_Parser::get('VARIABLE_NAME') . '+))?' .
            '/'
        );

        if (!$regex->match($markup)) {
            throw new Exception(
                "Error in tag 'include' - Valid syntax: include 'template' [path 'base path'] (with|for) [object|collection]"
            );
        }

        $this->templateName = trim($regex->matches[1], '\'"');
        if (isset($regex->matches[2]) && $regex->matches[2] !== '') {
            $this->templatePath = trim($regex->matches[2], '\'"');
        }
        if (isset($regex->matches[3]) && $regex->matches[3] == 'for') {
            $this->collection = true;
        }
        if (isset($regex->matches[4]) && $regex->matches[4] !== '') {
            $this->variableName = $regex->matches[4];
        }
        if (isset($regex->matches[5]) && $regex->matches[5] !== '') {
            $this->aliasName = $regex->matches[5];
        }

        $this->extractAttributes($markup);

        parent::__construct($tpl, $tokens, $markup);
    }

    /**
     * Parses the tokens
     *
     * @param   array $tokens
     *
     * @return  void
     */
    public function parse(array &$tokens)
    {
        $this->document = $this->tpl->parser->getDocument($this->templateName, $this->templatePath);
    }

    /**
     * Renders the node
     *
     * @param   object  $context
     *
     * @return string
     */
    public function render($context)
    {
        $result = '';

        if (!is_null($this->aliasName)) {
            $contextVarName = $this->aliasName;
        } elseif (!is_null($this->variableName)) {
            $contextVarName = $this->variableName;
        } else {
            $contextVarName = pathinfo($this->templateName, PATHINFO_FILENAME);
        }

        $variables = $context->get($this->variableName);
        $context->push();

        foreach ($this->attributes as $key => $value) {
            $context->set($key, $context->get($value));
        }

        if ($this->collection) {
            foreach ($variables as $item) {
                $context->set($contextVarName, $item);
                $result .= $this->document->render($context);
            }
        } else {
            if (!is_null($this->variableName)) {
                $context->set($contextVarName, $variables);
            }

            $result .= $this->document->render($context);
        }

        $context->pop();

        return $result;
    }

}