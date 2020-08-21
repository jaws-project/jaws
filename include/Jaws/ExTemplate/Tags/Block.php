<?php
/**
 * Class for tag block
 * Marks a section of a template as being reusable
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @see         https://github.com/harrydeluxe/php-liquid
 */
class Jaws_ExTemplate_Tags_Block extends Jaws_ExTemplate_Tags_Segmental
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
     * @param string $markup
     * @param array $tokens
     * @param   string  $rootPath
     *
     * * @throws  Exception
     * @return \Liquid\Tag\TagBlock
     */
    public function __construct($markup, array &$tokens, $rootPath = null)
    {
        $syntaxRegexp = new Jaws_Regexp('/(\w+)/');

        if ($syntaxRegexp->match($markup)) {
            $this->block = $syntaxRegexp->matches[1];
            parent::__construct($markup, $tokens, $rootPath);
        } else {
            throw new Exception("Syntax Error in 'block' - Valid syntax: block [name]");
        }
    }

}