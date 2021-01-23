<?php
/**
 * Class for tag unless
 * The opposite of if – executes a block of code only if a certain condition is not met
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @doc         https://shopify.github.io/liquid/tags/control-flow/
 */
class Jaws_XTemplate_Tags_Unless extends Jaws_XTemplate_Tags_If
{
    protected function negateIfUnless($display)
    {
        return !$display;
    }

}