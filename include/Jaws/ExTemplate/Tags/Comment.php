<?php
/**
 * Class for tag comment
 * Creates a comment; everything inside will be ignored
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ExTemplate_Tags_Comment extends Jaws_ExTemplate_Tags_Segmental
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