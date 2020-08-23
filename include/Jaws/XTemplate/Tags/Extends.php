<?php
/**
 * Class for tag extends
 * Extends a template by another one.
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ExTemplate_Tags_Extends extends Jaws_ExTemplate_Tag
{
    /**
     * @var string The name of the template
     */
    private $templateName;

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
     * * @throws  Exception
     */
    public function __construct($markup, array &$tokens, $rootPath = null)
    {
        $regex = new Jaws_Regexp('/("[^"]+"|\'[^\']+\')?/');
        if ($regex->match($markup) && isset($regex->matches[1])) {
            $this->templateName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
        } else {
            throw new Exception(
                "Error in tag 'extends' - Valid syntax: extends '[template name]'"
            );
        }

        parent::__construct($markup, $tokens, $rootPath);
    }

    /**
     * @param array $tokens
     *
     * @return array
     */
    private function findBlocks(array $tokens)
    {
        $blockstartRegexp = new Jaws_Regexp('/^' . Jaws_ExTemplate::get('TAG_START') . '\s*block (\w+)\s*(.*)?' . Jaws_ExTemplate::get('TAG_END') . '$/');
        $blockendRegexp = new Jaws_Regexp('/^' . Jaws_ExTemplate::get('TAG_START') . '\s*endblock\s*?' . Jaws_ExTemplate::get('TAG_END') . '$/');

        $b = array();
        $name = null;

        foreach ($tokens as $token) {
            if ($blockstartRegexp->match($token)) {
                $name = $blockstartRegexp->matches[1];
                $b[$name] = array();
            } elseif ($blockendRegexp->match($token)) {
                $name = null;
            } else {
                if ($name !== null) {
                    array_push($b[$name], $token);
                }
            }
        }

        return $b;
    }

    /**
     * Parses the tokens
     *
     * @param array $tokens
     *
     * @throws  Exception
     */
    public function parse(array &$tokens)
    {
        // read the source of the template and create a new sub document
        $source = Jaws_ExTemplate::readTemplateFile($this->templateName, $this->rootPath);

        // tokens in this new document
        $maintokens = Jaws_ExTemplate::tokenize($source);

        $eRegexp = new Jaws_Regexp('/^' . Jaws_ExTemplate::get('TAG_START') . '\s*extends (.*)?' . Jaws_ExTemplate::get('TAG_END') . '$/');
        foreach ($maintokens as $maintoken) {
            if ($eRegexp->match($maintoken)) {
                $m = $eRegexp->matches[1];
                break;
            }
        }

        if (isset($m)) {
            $rest = array_merge($maintokens, $tokens);
        } else {
            $childtokens = $this->findBlocks($tokens);

            $blockstartRegexp = new Jaws_Regexp('/^' . Jaws_ExTemplate::get('TAG_START') . '\s*block (\w+)\s*(.*)?' . Jaws_ExTemplate::get('TAG_END') . '$/');
            $blockendRegexp = new Jaws_Regexp('/^' . Jaws_ExTemplate::get('TAG_START') . '\s*endblock\s*?' . Jaws_ExTemplate::get('TAG_END') . '$/');

            $name = null;

            $rest = array();
            $keep = false;

            for ($i = 0; $i < count($maintokens); $i++) {
                if ($blockstartRegexp->match($maintokens[$i])) {
                    $name = $blockstartRegexp->matches[1];

                    if (isset($childtokens[$name])) {
                        $keep = true;
                        array_push($rest, $maintokens[$i]);
                        foreach ($childtokens[$name] as $item) {
                            array_push($rest, $item);
                        }
                    }
                }
                if (!$keep) {
                    array_push($rest, $maintokens[$i]);
                }

                if ($blockendRegexp->match($maintokens[$i]) && $keep === true) {
                    $keep = false;
                    array_push($rest, $maintokens[$i]);
                }
            }
        }

        /*
        $this->hash = Jaws_Cache::key($source);
        $this->document = $this->app->cache->get($this->hash, true);
        */

        //if ($this->document == false || $this->document->hasIncludes() == true) {
            $this->document = new Jaws_ExTemplate_Document($rest, $this->rootPath);
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
        $context->push();
        $result = $this->document->render($context);
        $context->pop();
        return $result;
    }

}