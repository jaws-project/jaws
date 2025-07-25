<?php
/**
 * Template engine default registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_Default extends Jaws_XTemplate_Filters
{
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
        return empty($input)? $default_value : $input;
    }

    /**
     * Determine input is different than NULL
     *
     * @param   mixed   $input
     * @param   mixed   $trueResult     Is set result
     * @param   mixed   $falseResult    Is not set result
     *
     * @return  mixed
     */
    public static function isset($input, $trueResult = null, $falseResult = null)
    {
        $trueResult = isset($trueResult)? $trueResult : true;
        $falseResult = isset($falseResult)? $falseResult : false;

        return isset($input)? $trueResult : $falseResult;
    }

    /**
     * Determine input is empty(equal PHP empty function)
     *
     * @param   mixed   $input
     * @param   mixed   $trueResult     Is empty result
     * @param   mixed   $falseResult    Is not empty result
     *
     * @return  mixed
     */
    public static function empty($input, $trueResult = null, $falseResult = null)
    {
        $trueResult = isset($trueResult)? $trueResult : true;
        $falseResult = isset($falseResult)? $falseResult : false;

        return empty($input)? $trueResult : $falseResult;
    }

    /**
     * check statement result is true?
     *
     * @param   mixed   $input
     * @param   mixed   $trueResult     If true result
     * @param   mixed   $falseResult    If false result
     *
     * @return  bool
     */
    public static function true($input, $trueResult = null, $falseResult = null)
    {
        $trueResult = isset($trueResult)? $trueResult : true;
        $falseResult = isset($falseResult)? $falseResult : false;

        return (bool)$input? $trueResult : $falseResult;
    }

    /**
     * check statement result is false?
     *
     * @param   mixed   $input
     * @param   mixed   $trueResult     If true result
     * @param   mixed   $falseResult    If false result
     *
     * @return  bool
     */
    public static function false($input, $trueResult = null, $falseResult = null)
    {
        $trueResult = isset($trueResult)? $trueResult : true;
        $falseResult = isset($falseResult)? $falseResult : false;

        return !(bool)$input? $trueResult : $falseResult;
    }

    /**
     * equal
     *
     * @param   mixed   $input1
     * @param   mixed   $input2
     * @param   mixed   $trueResult
     * @param   mixed   $falseResult
     *
     * @return  mixed
     */
    public static function equal($input1, $input2, $trueResult = null, $falseResult = null)
    {
        $trueResult = isset($trueResult)? $trueResult : true;
        $falseResult = isset($falseResult)? $falseResult : false;

        return ($input1 == $input2)? $trueResult : $falseResult;
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
     * @param   mixed   $input
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

        // only plain values and string-able objects left at this point
        return is_null($input)? 0 : strlen($input);
    }

    /**
     * Return a PHP value from a stored representation
     *
     * @param   mixed   $input  The value to be serialized
     * @return  string
     */
    public static function serialize($input)
    {
        return serialize($input);
    }

    /**
     * Return a PHP value from a stored representation
     *
     * @param   string  $input  The serialized string
     * @return  mixed
     */
    public static function unserialize($input)
    {
        return @unserialize($input);
    }

    /**
     * Checks if input statement contains given value
     *
     * @param   mixed   $input      The array|string to search in
     * @param   mixed   $needle     The searched value
     * @param   mixed   $column_key The column of values to search(int|string|null)
     * @return  bool    Returns true if needle is found, false otherwise
     */
    public static function contains($input, $needle, $column_key = null)
    {
        if (!isset($input)) {
            return false;
        }
        $needles = is_array($needle)? $needle : [$needle];

        if (is_array($input)) {
            $input = is_null($column_key)? $input : array_column($input, $column_key);
            foreach ($needles as $needle) {
                if (in_array($needle, $input)) {
                    return true;
                }
            }
        } else {
            foreach ($needles as $needle) {
                if (strpos($input, $needle) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if input statement contains given needle
     *
     * @param   mixed   $needle     The searched value
     * @param   mixed   $input      The array|string to search in
     * @param   mixed   $column_key The column of values to search(int|string|null)
     * @return  bool    Returns true if needle is found, false otherwise
     */
    public static function in($needle, $input, $column_key = null)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }

        if (is_array($input)) {
            return in_array($needle, is_null($column_key)? $input : array_column($input, $column_key));
        } else {
            return strpos((string)$input, $needle) !== false;
        }
    }

    /**
     * Get the type of a variable
     *
     * @param   mixed       $input
     * @param   null|string $type
     *
     * @return mixed
     */
    public static function type($input, $type = null)
    {
        if (empty($type)) {
            return gettype($input);
        } else {
            settype($input, $type);
            return $input;
        }
    }

    /**
     * Generate meta url
     *
     * @param   string  $string
     *
     * @return  string  return UTF-8 encoded safe url 
     */
    public static function metaURL($string)
    {
        return Jaws_UTF8::trim(
            preg_replace(
                array('#[^\p{L}[:digit:]_\.\-\s\x{200C}]#u', '#[\s_\-\x{200C}]#u', '#[\-|\+]+#u'),
                array('', '-', '-'),
                Jaws_UTF8::strtolower($string)
            ),
            '-'
        );
    }

}