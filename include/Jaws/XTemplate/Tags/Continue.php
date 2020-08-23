<?php
/**
 * Class for continue tag
 * Skips the current iteration of the current loop
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/iteration/
 */
class Jaws_ExTemplate_Tags_Continue extends Jaws_ExTemplate_Tag
{
    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return string|void
     */
    public function render($context)
    {
        $context->registers['continue'] = true;
    }

}