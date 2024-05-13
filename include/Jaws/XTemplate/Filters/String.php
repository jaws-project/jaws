<?php
/**
 * Template engine String registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_String extends Jaws_XTemplate_Filters
{
    /**
     * Add one string to another
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public static function append($input, $string)
    {
        return implode('', func_get_args());
    }

    /**
     * Capitalize words in the input sentence
     *
     * @param string $input
     *
     * @return string
     */
    public static function capitalize($input)
    {
        return preg_replace_callback("/(^|[^\p{L}'])([\p{Ll}])/u", function ($matches) {
            return $matches[1] . ucfirst($matches[2]);
        }, ucwords($input));
    }

    /**
     * Convert an input to lowercase
     *
     * @param string|array $input
     *
     * @return string|array
     */
    public static function downcase($input)
    {
        if ($input instanceof \Iterator) {
            $input = iterator_to_array($input);
        }
        if (is_array($input)) {
            return array_map('strtolower', $input);
        }

        return is_string($input) ? strtolower($input) : $input;
    }

    /**
     * Escape a string
     *
     * @param string $input
     *
     * @return string
     */
    public static function escape($input)
    {
        // Arrays are taken care down the stack with an error
        if (is_array($input)) {
            return $input;
        }

        return htmlentities($input, ENT_QUOTES);
    }

    /**
     * Escape a string once, keeping all previous HTML entities intact
     *
     * @param string $input
     *
     * @return string
     */
    public static function escape_once($input)
    {
        // Arrays are taken care down the stack with an error
        if (is_array($input)) {
            return $input;
        }

        return htmlentities($input, ENT_QUOTES, null, false);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public static function lstrip($input)
    {
        return ltrim($input);
    }

    /**
     * Replace each newline (\n) with html break
     *
     * @param string $input
     *
     * @return string
     */
    public static function newline_to_br($input)
    {
        return is_string($input)? Jaws_UTF8::str_replace("\n", '<br />', $input) : $input;
    }

    /**
     * Prepend a string to another
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public static function prepend($input, $string)
    {
        return implode('', array_reverse(func_get_args()));
    }

    /**
     * Remove a substring
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public static function remove($input, $string)
    {
        return str_replace($string, '', $input);
    }

    /**
     * Remove the first occurrences of a substring
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public static function remove_first($input, $string)
    {
        if (($pos = strpos($input, $string)) !== false) {
            $input = substr_replace($input, '', $pos, strlen($string));
        }

        return $input;
    }

    /**
     * Replace occurrences of a string with another
     *
     * @param string $input
     * @param string $string
     * @param string $replacement
     * @param bool $stripcslashes
     *
     * @return string
     */
    public static function replace($input, $string, $replacement = '', $stripcslashes = false)
    {
        if ($stripcslashes) {
            $string = stripcslashes($string);
            $replacement = stripcslashes($replacement);
        }

        return str_replace($string, $replacement, $input);
    }

    /**
     * Perform a regular expression search and replace
     *
     * @param string $input
     * @param string $pattern
     * @param string $replacement
     *
     * @return string
     */
    public static function replace_regex($input, $pattern, $replacement = array())
    {
        if (!$input || !$pattern) {
            return '';
        }

        $input = preg_replace_callback(
            $pattern, function ($matches) use ($replacement) {
                return isset($replacement[$matches[1]])? $replacement[$matches[1]] : '';
            },
            $input
        );

        return $input;
    }

    /**
     * Replace the first occurrences of a string with another
     *
     * @param string $input
     * @param string $string
     * @param string $replacement
     *
     * @return string
     */
    public static function replace_first($input, $string, $replacement = '')
    {
        if (($pos = strpos($input, $string)) !== false) {
            $input = substr_replace($input, $replacement, $pos, strlen($string));
        }

        return $input;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public static function rstrip($input)
    {
        return rtrim($input);
    }

    /**
     * @param array|\Iterator|string $input
     * @param int $offset
     * @param int $length
     *
     * @return array|\Iterator|string
     */
    public static function slice($input, $offset, $length = null)
    {
        if ($input instanceof \Iterator) {
            $input = iterator_to_array($input);
        }
        if (is_array($input)) {
            $input = array_slice($input, $offset, $length, true);
        } elseif (is_string($input)) {
            $input = substr($input, $offset, $length);
        }

        return $input;
    }

    /**
     * Explicit string conversion.
     *
     * @param mixed $input
     *
     * @return string
     */
    public static function string($input)
    {
        return strval($input);
    }

    /**
     * Split input string into an array of substrings separated by given pattern.
     *
     * @param string $input
     * @param string $pattern
     *
     * @return array
     */
    public static function split($input, $pattern = ',')
    {
        if (!isset($input) || $input === '') {
            return [];
        }

        return explode($pattern, $input);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public static function strip($input)
    {
        return trim($input);
    }

    /**
     * Removes html tags from text
     *
     * @param string $input
     *
     * @return string
     */
    public static function strip_html($input)
    {
        return is_string($input) ? strip_tags($input) : $input;
    }

    /**
     * Strip all newlines (\n, \r) from string
     *
     * @param string $input
     *
     * @return string
     */
    public static function strip_newlines($input)
    {
        return is_string($input) ? str_replace(array(
            "\n", "\r"
        ), '', $input) : $input;
    }

    /**
     * Truncate a string down to x characters
     *
     * @param string $input
     * @param int $characters
     * @param string $ending string to append if truncated
     *
     * @return string
     */
    public static function truncate($input, $characters = 100, $ending = '...')
    {
        if (is_string($input) || is_numeric($input)) {
            if (strlen($input) > $characters) {
                return substr($input, 0, $characters) . $ending;
            }
        }

        return $input;
    }

    /**
     * Truncate string down to x words
     *
     * @param string $input
     * @param int $words
     * @param string $ending string to append if truncated
     *
     * @return string
     */
    public static function truncatewords($input, $words = 3, $ending = '...')
    {
        if (is_string($input)) {
            $wordlist = explode(" ", $input);

            if (count($wordlist) > $words) {
                return implode(" ", array_slice($wordlist, 0, $words)) . $ending;
            }
        }

        return $input;
    }

    /**
     * Convert an input to uppercase
     *
     * @param string|array $input
     *
     * @return string|array
     */
    public static function upcase($input)
    {
        if ($input instanceof \Iterator) {
            $input = iterator_to_array($input);
        }
        if (is_array($input)) {
            return array_map('strtoupper', $input);
        }

        return is_string($input) ? strtoupper($input) : $input;
    }

    /**
     * Convert special characters to HTML entities
     *
     * @access  public
     * @param   string  $input      The string being converted
     * @param   bool    $noquotes   Will leave both double and single quotes unconverted
     * @return  string  The converted string
     */
    public static function ent_encode($input, $noquotes = false)
    {
        return htmlspecialchars(isset($input)? $input : '', $noquotes? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
    }

    /**
     * Convert special HTML entities back to characters
     *
     * @access  public
     * @param   string  $input      The string to decode
     * @param   bool    $noquotes   Will leave both double and single quotes unconverted
     * @return  string  Returns the decoded string
     */
    static function ent_decode($input, $noquotes = false)
    {
        return htmlspecialchars_decode($input, $noquotes? ENT_NOQUOTES : ENT_QUOTES);
    }

    /**
     * URL encodes a string
     *
     * @param string $input
     *
     * @return string
     */
    public static function url_encode($input)
    {
        return urlencode($input);
    }

    /**
     * Decodes a URL-encoded string
     *
     * @param string $input
     *
     * @return string
     */
    public static function url_decode($input)
    {
        return urldecode($input);
    }

    /**
     * Format a number with grouped thousands
     *
     * @param   string  $input
     * @param   array   $args   Variable-length argument lists
     *
     * @return  string
     */
    public static function formatNumber($input, ...$args)
    {
        array_unshift($args, (float)$input);
        return call_user_func_array(array('Jaws_Utils', 'formatNumber'), $args);
    }

    /**
     * Format a string
     *
     * @param   string  $input
     * @param   string  $format
     * @param   array   $args   Variable-length argument lists
     *
     * @return  string
     */
    public static function formatString($input, $format, ...$args)
    {
        if (!$input || !$format) {
            return '';
        }
        // inject $input as first element of arguments
        array_unshift($args, $input);
        $input = preg_replace_callback(
            '/\{(\d+)\}/', function ($matches) use ($args) {
                return isset($args[$matches[1]])? $args[$matches[1]] : '';
            },
            $format
        );

        return $input;
    }

    /**
     * Format a title by associated array
     *
     * @param   string  $input
     * @param   array   $args   arguments
     *
     * @return  string
     */
    public static function formatTitle($input, $args, $prefix = '', $delimiter = '_')
    {
        if (!$input) {
            return '';
        }
        $prefix = in_array($prefix, ['', null])? '' : ($prefix . $delimiter);

        $result = preg_replace_callback(
            '/\{(\w+)\}/', function ($matches) use ($args, $prefix) {
                return isset($args[$prefix . $matches[1]])? $args[$prefix . $matches[1]] : '';
            },
            $input
        );

        return $result;
    }

    /**
     * JSON representation of a value
     *
     * @param   mixed   $input  The value being encoded
     *
     * @return  string|bool Returns a JSON encoded string on success or false on failure
     */
    public static function json_encode($input)
    {
        return json_encode($input, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * Decodes a JSON string
     *
     * @param   string  $input The json string being decoded
     *
     * @return  mixed   Returns the value encoded in json 
     */
    public static function json_decode($input)
    {
        return json_decode($input, true);
    }

    /**
     * Encodes data with MIME base64
     *
     * @param   mixed   $input  The data to encode
     *
     * @return  string  The encoded data, as a string
     */
    public static function base64_encode($input)
    {
        return base64_encode($input);
    }

    /**
     * Decodes data encoded with MIME base64
     *
     * @param   string  $input The encoded data
     *
     * @return  string|bool Returns the decoded data or false on failure
     */
    public static function base64_decode($input)
    {
        return base64_decode($input, true);
    }

    /**
     * Convert binary data into hexadecimal representation
     *
     * @param   mixed   $input  A string
     *
     * @return  string  Returns the hexadecimal representation of the given string
     */
    public static function bin2hex($input)
    {
        return bin2hex($input);
    }

    /**
     * Decodes a hexadecimally encoded binary string
     *
     * @param   string  $input Hexadecimal representation of data
     *
     * @return  string|bool Returns the binary representation of the given data or false on failure
     */
    public static function hex2bin($input)
    {
        return hex2bin($input);
    }

    /**
     * Converts a string from ISO-8859-1 to UTF-8
     *
     * @param   mixed   $input  An ISO-8859-1 string
     *
     * @return  string  Returns the UTF-8 translation of string
     */
    public static function utf8_encode($input)
    {
        return mb_convert_encoding($input, 'UTF-8', 'ISO-8859-1');
    }

    /**
     * Converts a string from UTF-8 to ISO-8859-1
     *
     * @param   string  $input A UTF-8 encoded string
     *
     * @return  string  Returns the ISO-8859-1 translation of string
     */
    public static function utf8_decode($input)
    {
        return mb_convert_encoding($input, 'ISO-8859-1', 'UTF-8');
    }

}