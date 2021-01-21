<?php
/**
 * Template engine base class of segmental tags
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @see         https://github.com/harrydeluxe/php-liquid
 */
class Jaws_XTemplate_TagSegmental extends Jaws_XTemplate_Tag
{
    /**
     * @var AbstractTag[]|Variable[]|string[]
     */
    protected $nodelist = array();

    /**
     * Whenever next token should be ltrimmed.
     *
     * @var bool
     */
    protected static $trimWhitespace = false;

    /**
     * @return array
     */
    public function getNodelist()
    {
        return $this->nodelist;
    }

    /**
     * Parses the given tokens
     *
     * @param array $tokens
     *
     * @throws Exception
     * @return void
     */
    public function parse(array &$tokens)
    {
        $startRegexp = new Jaws_Regexp('/^' . Jaws_XTemplate::get('TAG_START') . '/');
        $tagRegexp = new Jaws_Regexp('/^' . Jaws_XTemplate::get('TAG_START') . Jaws_XTemplate::get('WHITESPACE_CONTROL') . '?\s*(\w+)\s*(.*?)' . Jaws_XTemplate::get('WHITESPACE_CONTROL') . '?' . Jaws_XTemplate::get('TAG_END') . '$/');
        $variableStartRegexp = new Jaws_Regexp('/^' . Jaws_XTemplate::get('VARIABLE_START') . '/');

        $this->nodelist = array();

        while (count($tokens)) {
            $token = array_shift($tokens);

            if ($startRegexp->match($token)) {
                $this->whitespaceHandler($token);
                if ($tagRegexp->match($token)) {
                    // If we found the proper block delimiter just end parsing here and let the outer block proceed
                    if ($tagRegexp->matches[1] == $this->blockDelimiter()) {
                        $this->endTag();
                        return;
                    }

                    $tagName = 'Jaws_XTemplate_Tags_' . ucwords($tagRegexp->matches[1]);
                    $tagName = (class_exists($tagName) === true) ? $tagName : null;
                    if ($tagName !== null) {
                        $this->nodelist[] = new $tagName($tagRegexp->matches[2], $tokens);
                        if ($tagRegexp->matches[1] == 'extends') {
                            return;
                        }
                    } else {
                        $this->unknownTag($tagRegexp->matches[1], $tagRegexp->matches[2], $tokens);
                    }
                } else {
                    throw new Exception("Tag $token was not properly terminated (won't match $tagRegexp)");
                }
            } elseif ($variableStartRegexp->match($token)) {
                $this->whitespaceHandler($token);
                $this->nodelist[] = $this->createVariable($token);
            } else {
                // This is neither a tag or a variable, proceed with an ltrim
                if (self::$trimWhitespace) {
                    $token = ltrim($token);
                }

                self::$trimWhitespace = false;
                $this->nodelist[] = $token;
            }
        }

        $this->assertMissingDelimitation();
    }

    /**
     * Handle the whitespace.
     *
     * @param string $token
     */
    protected function whitespaceHandler($token)
    {
        /*
         * This assumes that TAG_START is always '{%', and a whitespace control indicator
         * is exactly one character long, on a third position.
         */
        if (Jaws_UTF8::substr($token, 2, 1) === Jaws_XTemplate::get('WHITESPACE_CONTROL')) {
            $previousToken = end($this->nodelist);
            if (is_string($previousToken)) { // this can also be a tag or a variable
                $this->nodelist[key($this->nodelist)] = rtrim($previousToken);
            }
        }

        /*
         * This assumes that TAG_END is always '%}', and a whitespace control indicator
         * is exactly one character long, on a third position from the end.
         */
        self::$trimWhitespace = Jaws_UTF8::substr($token, -3, 1) === Jaws_XTemplate::get('WHITESPACE_CONTROL');
    }

    /**
     * Render the block.
     *
     * @param Context $context
     *
     * @return string
     */
    public function render($context)
    {
        return $this->renderAll($this->nodelist, $context);
    }

    /**
     * Renders all the given nodelist's nodes
     *
     * @param array $list
     * @param Context $context
     *
     * @return string
     */
    protected function renderAll(array $list, $context)
    {
        $result = '';

        foreach ($list as $token) {
            if (is_object($token) && method_exists($token, 'render')) {
                $value = $token->render($context);
            } else {
                $value = $token;
            }

            if (is_array($value)) {
                throw new Exception("Implicit rendering of arrays not supported. Use index operator.");
            }

            $result .= $value;

            if (isset($context->registers['break'])) {
                break;
            }
            if (isset($context->registers['continue'])) {
                break;
            }

        }

        return $result;
    }

    /**
     * An action to execute when the end tag is reached
     */
    protected function endTag()
    {
        // Do nothing by default
    }

    /**
     * Handler for unknown tags
     *
     * @param   string  $tag
     * @param   string  $params
     * @param   array   $tokens
     *
     * @throws  Exception
     */
    protected function unknownTag($tag, $params, array $tokens)
    {
        switch ($tag) {
            case 'else':
                throw new Exception($this->blockName() . " does not expect else tag");
            case 'end':
                throw new Exception("'end' is not a valid delimiter for " . $this->blockName() . " tags. Use " . $this->blockDelimiter());
            default:
                throw new Exception("Unknown tag $tag");
        }
    }

    /**
     * This method is called at the end of parsing, and will throw an error unless
     * this method is subclassed, like it is for Document
     *
     * @throws  Exception
     * @return  bool
     */
    protected function assertMissingDelimitation()
    {
        throw new Exception($this->blockName() . " tag was never closed");
    }

    /**
     * Returns the string that delimits the end of the block
     *
     * @return string
     */
    protected function blockDelimiter()
    {
        return "end" . $this->blockName();
    }

    /**
     * Returns the name of the block
     *
     * @return string
     */
    private function blockName()
    {
        return str_replace('jaws_xtemplate_tags_', '', strtolower(get_class($this)));
    }

    /**
     * Create a variable for the given token
     *
     * @param   string  $token
     *
     * @throws  Exception
     * @return  Jaws_XTemplate_Variable
     */
    private function createVariable($token)
    {
        $variableRegexp = new Jaws_Regexp(
            '/^' .
            Jaws_XTemplate::get('VARIABLE_START') .
            Jaws_XTemplate::get('WHITESPACE_CONTROL') . '?(.*?)' .
            Jaws_XTemplate::get('WHITESPACE_CONTROL') . '?' .
            Jaws_XTemplate::get('VARIABLE_END') .
            '$/');
        if ($variableRegexp->match($token)) {
            return new Jaws_XTemplate_Variable($variableRegexp->matches[1]);
        }

        throw new Exception("Variable $token was not properly terminated");
    }

}