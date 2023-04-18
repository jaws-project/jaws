<?php
/**
 * Template engine logical/bitwise operators registered filters
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
     * @param bool|int  $input1
     * @param bool|int  $input2
     *
     * @return bool|int
     */
    public static function and($input1, $input2)
    {
        if (is_numeric($input1)) {
            return $input1 & $input2;
        } else {
            return (bool)$input1 && (bool)$input2;
        }
    }

    /**
     * or
     *
     * @param bool|int  $input1
     * @param bool|int  $input2
     *
     * @return bool|int
     */
    public static function or($input1, $input2)
    {
        if (is_numeric($input1)) {
            return $input1 | $input2;
        } else {
            return (bool)$input1 || (bool)$input2;
        }
    }

    /**
     * xor
     *
     * @param bool|int  $input1
     * @param bool|int  $input2
     *
     * @return bool|int
     */
    public static function xor($input1, $input2)
    {
        if (is_numeric($input1)) {
            return $input1 ^ $input2;
        } else {
            return (bool)$input1 xor (bool)$input2;
        }
    }

    /**
     * not
     *
     * @param bool|int  $input
     *
     * @return bool|int
     */
    public static function not($input)
    {
        if (is_numeric($input)) {
            return ~$input;
        } else {
            return !(bool)$input;
        }
    }

}