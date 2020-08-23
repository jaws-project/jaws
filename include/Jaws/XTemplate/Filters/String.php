<?php
/**
 * Template engine String registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_String
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
        return $input . $string;
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
     * @param string $input
     *
     * @return string
     */
    public static function downcase($input)
    {
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
        return is_string($input) ? nl2br($input) : $input;
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
        return $string . $input;
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
     *
     * @return string
     */
    public static function replace($input, $string, $replacement = '')
    {
        return str_replace($string, $replacement, $input);
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
            $input = array_slice($input, $offset, $length);
        } elseif (is_string($input)) {
            $input = $length === null
                ? substr($input, $offset)
                : substr($input, $offset, $length);
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
    public static function split($input, $pattern)
    {
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
     * @param string $input
     *
     * @return string
     */
    public static function upcase($input)
    {
        return is_string($input) ? strtoupper($input) : $input;
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

}