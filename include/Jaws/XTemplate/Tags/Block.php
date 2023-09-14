<?php
/**
 * Class for tag block
 * Marks a section of a template as being reusable
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @see         https://github.com/harrydeluxe/php-liquid
 */
class Jaws_XTemplate_Tags_Block extends Jaws_XTemplate_TagSegmental
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    private $block;

    /**
     * Constructor
     *
     * @param   object  $tpl    Jaws_XTemplate object
     * @param   array   $tokens
     * @param   string  $markup
     *
     * @throws  Exception
     * @return  Jaws_XTemplate_Tags_Block
     */
    public function __construct(&$tpl, array &$tokens, $markup)
    {
        $syntaxRegexp = new Jaws_Regexp('/(\w+)/');

        if (!$syntaxRegexp->match($markup)) {
            throw new Exception("Syntax Error in 'block' - Valid syntax: block [name]");
        }

        $this->block = $syntaxRegexp->matches[1];
        parent::__construct($tpl, $tokens, $markup);
    }

}