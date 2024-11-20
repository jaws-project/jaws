<?php
/**
 * Class for indent tag
 * Indent the string inside of the opening and closing tags
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/variable/
 */
class Jaws_XTemplate_Tags_Indent extends Jaws_XTemplate_TagSegmental
{
    /**
     * The variable to assign indents
     *
     * @var string
     */
    private $indents;

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
        $syntaxRegexp = new Jaws_Regexp('/\s*(.*)\s*/');

        if (!$syntaxRegexp->match($markup)) {
            throw new Exception("Syntax Error in 'indent' - Valid syntax: indent [count]");
        }

        $this->indents = new Jaws_XTemplate_Variable($syntaxRegexp->matches[1]);
        parent::__construct($tpl, $tokens, $markup);
    }

    /**
     * Renders the block
     *
     * @param Context $context
     *
     * @return string
     */
    public function render($context)
    {
        $output = parent::render($context);
        $indents = $this->indents->render($context);
        if ($indents < 0) {
            $indents = abs($indents);
            return preg_replace(
                array("/\r\n/", "/\r/", "/^[ ]{1,$indents}/m"),
                array("\n", "\n", ''),
                $output
            );
        } else {
            $output = Jaws_UTF8::str_replace(
                array("\r\n", "\r", "\n"),
                array("\n", "\n", str_pad("\n", $indents+1, ' ', STR_PAD_RIGHT)),
                $output
            );
            return preg_replace(
                '/\n\s+\n/',
                "\n\n",
                $output
            );
        }
    }

}