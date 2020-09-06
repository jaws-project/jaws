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
class Jaws_XTemplate_Filters_Default
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
     * Determine input is different than NULL
     *
     * @param   mixed   $input
     *
     * @return  bool
     */
    public static function isset($input)
    {
        return isset($input);
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
     * Get map version of url if found
     *
     * @param   string   $args      Map arguments(comma seprated)
     * @param   string   $gadget    Gadget name
     * @param   string   $action    Action name
     * @param   array    $params    Map parameters
     *
     * @return string
     */
    public static function urlmap($args, $gadget, $action, ...$params)
    {
        $args = array_filter(array_map('trim', explode(',', $args)));
        $params = array_combine($args, $params);

        return Jaws::getInstance()->map->GetMappedURL(
            $gadget,
            $action,
            $params
        );
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