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
class Jaws_ExTemplate_Tags_Include extends Jaws_ExTemplate_Tag
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
     * @var mixed The value to pass to the child template as the template name
     */
    private $variable;

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
            '/("[^"]+"|\'[^\']+\'|[^\'"\s]+)(\s+(with|for)\s+(' .
            Jaws_ExTemplate::get('QUOTED_FRAGMENT') .
            '+))?/');

        if (!$regex->match($markup)) {
            throw new Exception("Error in tag 'include' - Valid syntax: include '[template]' (with|for) [object|collection]");
        }

        $unquoted = (strpos($regex->matches[1], '"') === false && strpos($regex->matches[1], "'") === false);

        $start = 1;
        $len = strlen($regex->matches[1]) - 2;

        if ($unquoted) {
            $start = 0;
            $len = strlen($regex->matches[1]);
        }

        $this->templateName = substr($regex->matches[1], $start, $len);

        if (isset($regex->matches[1])) {
            $this->collection = (isset($regex->matches[3])) ? ($regex->matches[3] == "for") : null;
            $this->variable = (isset($regex->matches[4])) ? $regex->matches[4] : null;
        }

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
        $source = Jaws_ExTemplate::readTemplateFile($this->templateName, $this->rootPath);

        /*
        $this->hash = Jaws_Cache::key($source);
        $this->document = $this->app->cache->get($this->hash, true);
        */

        //if ($this->document == false || $this->document->hasIncludes() == true) {
            $templateTokens = Jaws_ExTemplate::tokenize($source);
            $this->document = new Jaws_ExTemplate_Tags_Document($templateTokens, $this->rootPath);
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

        $source = Jaws_ExTemplate::readTemplateFile($this->templateName, $this->rootPath);
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
     * @param Context $context
     *
     * @return string
     */
    public function render($context)
    {
        $result = '';
        $variable = $context->get($this->variable);

        $context->push();

        foreach ($this->attributes as $key => $value) {
            $context->set($key, $context->get($value));
        }

        if ($this->collection) {
            foreach ($variable as $item) {
                $context->set($this->templateName, $item);
                $result .= $this->document->render($context);
            }
        } else {
            if (!is_null($this->variable)) {
                $context->set($this->templateName, $variable);
            }

            $result .= $this->document->render($context);
        }

        $context->pop();

        return $result;
    }

}