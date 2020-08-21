<?php
/**
 * Template engine custom registerd filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ExTemplate_Filters_Custom
{
    /**
     * Sort an array by key.
     *
     * @param array $input
     *
     * @return array
     */
    public static function sort_key(array $input)
    {
        ksort($input);
        return $input;
    }

}