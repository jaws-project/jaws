<?php
/**
 * Class for tag include
 * Includes another, partial, template
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Tags_Include extends Jaws_XTemplate_Tag
{
    /**
     * @var string The name of the template
     */
    private $templateName;

    /**
     * @var bool True if the variable is a collection
     */
    private $collection;

    /**
     * @var mixed The variable name passed to the child template
     */
    private $variableName;

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
     * @param string $markup
     * @param array $tokens
     * @param   string  $rootPath
     *
     * @throws Exception
     */
    public function __construct($markup, array &$tokens, $rootPath = null)
    {
        $regex = new Jaws_Regexp(
            '/('.Jaws_XTemplate::get('QUOTED_FRAGMENT').'+)' .
            '(\s+(with|for)\s+(' .
            Jaws_XTemplate::get('QUOTED_FRAGMENT') .
            '+))?(\s+(?:as)\s+(' .
            Jaws_XTemplate::get('VARIABLE_NAME').
            '+))?/'
        );

        if (!$regex->match($markup)) {
            throw new Exception(
                "Error in tag 'include' - Valid syntax: include '[template]' (with|for) [object|collection]"
            );
        }

        $this->templateName = trim($regex->matches[1], '\'"');
        $this->variableName = isset($regex->matches[4])? $regex->matches[4] : null;
        $this->aliasName    = isset($regex->matches[6])? $regex->matches[6] : null;
        $this->collection   = isset($regex->matches[3])? ($regex->matches[3] == 'for') : null;

        $this->extractAttributes($markup);

        parent::__construct($markup, $tokens, $rootPath);
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
        // read the source of the template and create a new sub document
        $source = Jaws_XTemplate::readTemplateFile(
            basename($this->templateName),
            pathinfo($this->templateName, PATHINFO_DIRNAME)
        );

        /*
        $this->hash = Jaws_Cache::key($source);
        $this->document = $this->app->cache->get($this->hash, true);
        */

        //if ($this->document == false || $this->document->hasIncludes() == true) {
            $templateTokens = Jaws_XTemplate::tokenize($source);
            $this->document = new Jaws_XTemplate_Document($templateTokens, $this->rootPath);
            /*
            $this->app->cache->set(
                $this->hash,
                $this->document,
                true
            );
            */
        //}
    }

    /**
     * Check for cached includes; if there are - do not use cache
     *
     * @see Document::hasIncludes()
     * @return boolean
     */
    public function hasIncludes()
    {
        if ($this->document->hasIncludes() == true) {
            return true;
        }

        $source = Jaws_XTemplate::readTemplateFile(
            basename($this->templateName),
            pathinfo($this->templateName, PATHINFO_DIRNAME)
        );
        if ($this->app->cache->exists(Jaws_Cache::key($source)) &&
            $this->hash === Jaws_Cache::key($source)
        ) {
            return false;
        }

        return true;
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