<?php
/**
 * Template engine Math registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_Math extends Jaws_XTemplate_Filters
{
    /**
     * @param mixed $input number
     *
     * @return int
     */
    public static function ceil($input)
    {
        return (int) ceil((float)$input);
    }

    /**
     * division
     *
     * @param float $input
     * @param float $operand
     *
     * @return float
     */
    public static function div($input, $operand)
    {
        return ($operand == 0)? (float)$input : ((float)$input / (float)$operand);
    }

    /**
     * @param mixed $input number
     *
     * @return mixed
     */
    public static function abs($input)
    {
        return is_numeric($input)? abs($input) : $input;
    }

    /**
     * @param mixed $input number
     *
     * @return int
     */
    public static function floor($input)
    {
        return (int) floor((float)$input);
    }

    /**
     * subtraction
     *
     * @param float $input1
     * @param float $input2
     *
     * @return float
     */
    public static function sub($input1, $input2)
    {
        return (float)$input1 - (float)$input2;
    }

    /**
     * modulo
     *
     * @param float $input1
     * @param float $input2
     *
     * @return float
     */
    public static function mod($input1, $input2)
    {
        return fmod((float)$input1, (float)$input2);
    }

    /**
     * addition
     *
     * @param float $input1
     * @param float $input2
     *
     * @return float
     */
    public static function add($input1, $input2)
    {
        return (float)$input1 + (float)$input2;
    }

    /**
     * Round a number
     *
     * @param float $input
     * @param int $n precision
     *
     * @return float
     */
    public static function round($input, $n = 0)
    {
        return round((float)$input, (int)$n);
    }

    /**
     * multiplication
     *
     * @param float $input1
     * @param float $input2
     *
     * @return float
     */
    public static function mul($input1, $input2)
    {
        return (float)$input1 * (float)$input2;
    }

    /**
     * greater than or equal
     *
     * @param float $input1
     * @param float $input2
     *
     * @return float
     */
    public static function gt($input1, $input2)
    {
        return (float)$input1 >= (float)$input2;
    }

    /**
     * less than or equal
     *
     * @param float $input1
     * @param float $input2
     *
     * @return float
     */
    public static function le($input1, $input2)
    {
        return (float)$input1 <= (float)$input2;
    }

}