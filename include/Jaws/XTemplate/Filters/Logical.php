<?php
/**
 * Template engine logical registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2023 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_Logical extends Jaws_XTemplate_Filters
{
    /**
     * and
     *
     * @param bool $input1
     * @param bool $input2
     *
     * @return bool
     */
    public static function and($input1, $input2)
    {
        return (bool)$input1 && (bool)$input2;
    }

    /**
     * or
     *
     * @param bool $input1
     * @param bool $input2
     *
     * @return bool
     */
    public static function or($input1, $input2)
    {
        return (bool)$input1 || (bool)$input2;
    }

    /**
     * xor
     *
     * @param bool $input1
     * @param bool $input2
     *
     * @return bool
     */
    public static function xor($input1, $input2)
    {
        return (bool)$input1 xor (bool)$input2;
    }

    /**
     * not
     *
     * @param bool $input
     *
     * @return bool
     */
    public static function not($input)
    {
        return !(bool)$input;
    }

}