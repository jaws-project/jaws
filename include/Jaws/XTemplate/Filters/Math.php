<?php
/**
 * Template engine Math registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_Math
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
    public static function divided_by($input, $operand)
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
     * @param float $input
     * @param float $operand
     *
     * @return float
     */
    public static function minus($input, $operand)
    {
        return (float)$input - (float)$operand;
    }

    /**
     * modulo
     *
     * @param float $input
     * @param float $operand
     *
     * @return float
     */
    public static function modulo($input, $operand)
    {
        return fmod((float)$input, (float)$operand);
    }

    /**
     * addition
     *
     * @param float $input
     * @param float $operand
     *
     * @return float
     */
    public static function plus($input, $operand)
    {
        return (float)$input + (float)$operand;
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
     * @param float $input
     * @param float $operand
     *
     * @return float
     */
    public static function times($input, $operand)
    {
        return (float)$input * (float)$operand;
    }

}