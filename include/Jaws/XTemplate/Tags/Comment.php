<?php
/**
 * Class for tag comment
 * Creates a comment; everything inside will be ignored
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/comment/
 */
class Jaws_XTemplate_Tags_Comment extends Jaws_XTemplate_TagSegmental
{
    /**
     * Renders the block
     *
     * @param Context $context
     *
     * @return string empty string
     */
    public function render($context)
    {
        return '';
    }

}