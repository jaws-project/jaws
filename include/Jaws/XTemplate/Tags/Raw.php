<?php
/**
 * Class for tag raw
 * temporarily disables tag processing
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/raw/
 */
class Jaws_XTemplate_Tags_Raw extends Jaws_XTemplate_TagSegmental
{
    /**
     * @param array $tokens
     */
    public function parse(array &$tokens)
    {
        $tagRegexp = new Jaws_Regexp(
            '/^' .
            Jaws_XTemplate_Parser::get('TAG_START') .
            '\s*(\w+)\s*(.*)?' . 
            Jaws_XTemplate_Parser::get('TAG_END') . '$/'
        );

        $this->nodelist = array();

        while (count($tokens)) {
            $token = array_shift($tokens);

            if ($tagRegexp->match($token)) {
                // If we found the proper block delimiter just end parsing here and let the outer block proceed
                if ($tagRegexp->matches[1] == $this->blockDelimiter()) {
                    break;
                }
            }

            $this->nodelist[] = $token;
        }
    }

}