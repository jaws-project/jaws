<?php
/**
 * Template engine default registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ExTemplate_Filters_Default
{
    /**
     * Formats a date using Jaws_Date::Format
     *
     * @param mixed $input
     * @param string $format
     *
     * @return string
     */
    public static function date($input, $format)
    {
        return Jaws_Date::getInstance()->Format($input, $format);
    }

    /**
     * Default
     *
     * @param   string    $input
     * @param   string    $default_value
     *
     * @return  string
     */
    public static function default($input, $default_value)
    {
        $isBlank = $input == '' || $input === false || $input === null;
        return $isBlank ? $default_value : $input;
    }

    /**
     * equal
     *
     * @param   mixed   $input1
     * @param   mixed   $input2
     * @param   mixed   $yesResult
     * @param   mixed   $noResult
     *
     * @return  mixed
     */
    public static function equal($input1, $input2, $yesResult, $noResult = null)
    {
        return $input1 == $input2 ? $yesResult : $noResult;
    }

    /**
     * Pseudo-filter: negates auto-added escape filter
     *
     * @param string $input
     *
     * @return string
     */
    public static function raw($input)
    {
        return $input;
    }

    /**
     * Return the size of an array or of an string
     *
     * @param mixed $input
     * @throws RenderException
     * @return int
     */
    public static function size($input)
    {
        if ($input instanceof \Iterator) {
            return iterator_count($input);
        }

        if (is_array($input)) {
            return count($input);
        }

        if (is_object($input)) {
            if (method_exists($input, 'size')) {
                return $input->size();
            }

            if (!method_exists($input, '__toString')) {
                $class = get_class($input);
                throw new Exception("Size of $class cannot be estimated: it has no method 'size' nor can be converted to a string");
            }
        }

        // only plain values and stringable objects left at this point
        return strlen($input);
    }

    /**
     * Convenience function to translate strings
     *
     * @param   string   $input
     *
     * @return string
     */
    public static function t($input)
    {
        $args = func_get_args();
        array_shift($args);

        return Jaws_Translate::getInstance()->Translate(
            null,
            strtoupper(str_replace(array(' ', '.'), '_', $input)),
            $args
        );
    }

}