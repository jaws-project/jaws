<?php
/**
 * PHP support for old versions
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Replace scandir()
 *
 * @category   PHP
 * @package    PHP_Compat
 * @link        http://php.net/function.scandir
 * @author     Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.18 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('scandir')) {
    function scandir($directory, $sorting_order = 0)
    {
        if (!is_string($directory)) {
            user_error('scandir() expects parameter 1 to be string, ' .
                gettype($directory) . ' given', E_USER_WARNING);
            return;
        }

        if (!is_int($sorting_order) && !is_bool($sorting_order)) {
            user_error('scandir() expects parameter 2 to be long, ' .
                gettype($sorting_order) . ' given', E_USER_WARNING);
            return;
        }

        if (!is_dir($directory) || (false === $fh = @opendir($directory))) {
            user_error('scandir() failed to open dir: Invalid argument', E_USER_WARNING);
            return false;
        }

        $files = array ();
        while (false !== ($filename = readdir($fh))) {
            $files[] = $filename;
        }

        closedir($fh);

        if ($sorting_order == 1) {
            rsort($files);
        } else {
            sort($files);
        }

        return $files;
    }
}

/**
 * Replace str_ireplace()
 *
 * @category   PHP
 * @package    PHP_Compat
 * @link        http://php.net/function.str_ireplace
 * @author     Aidan Lister <aidan@php.net>
 * @author     Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.21 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 * @note        count is returned by reference (required parameter)
 *              to disable, change '&$count' to '$count = null'
 */
if (!function_exists('str_ireplace')) {
    function str_ireplace($search, $replace, $subject, $count = null)
    {
        // Sanity check
        if (is_string($search) && is_array($replace)) {
            user_error('Array to string conversion', E_USER_NOTICE);
            $replace = (string) $replace;
        }

        // If search isn't an array, make it one
        if (!is_array($search)) {
            $search = array ($search);
        }
        $search = array_values($search);

        // If replace isn't an array, make it one, and pad it to the length of search
        if (!is_array($replace)) {
            $replace_string = $replace;

            $replace = array ();
            for ($i = 0, $c = count($search); $i < $c; $i++) {
                $replace[$i] = $replace_string;
            }
        }
        $replace = array_values($replace);

        // Check the replace array is padded to the correct length
        $length_replace = count($replace);
        $length_search = count($search);
        if ($length_replace < $length_search) {
            for ($i = $length_replace; $i < $length_search; $i++) {
                $replace[$i] = '';
            }
        }

        // If subject is not an array, make it one
        $was_array = false;
        if (!is_array($subject)) {
            $was_array = true;
            $subject = array ($subject);
        }

        // Prepare the search array
        foreach ($search as $search_key => $search_value) {
            $search[$search_key] = '/' . preg_quote($search_value, '/') . '/i';
        }

        // Prepare the replace array (escape backreferences)
        foreach ($replace as $k => $v) {
            $replace[$k] = str_replace(array(chr(92), '$'), array(chr(92) . chr(92), '\$'), $v);
        }

        // do the replacement
        $result = preg_replace($search, $replace, $subject, -1, $count);

        // Check if subject was initially a string and return it as a string
        if ($was_array === true) {
            return $result[0];
        }

        // Otherwise, just return the array
        return $result;
    }
}

/**
 * Replace array_walk_recursive()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_walk_recursive
 * @author      Tom Buskens <ortega@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 269597 $
 * @since       PHP 5
 * @require     PHP 4.0.6 (is_callable)
 */
if (!function_exists('array_walk_recursive')) {
    function array_walk_recursive(&$input, $funcname)
    {
        if (!is_callable($funcname)) {
            if (is_array($funcname)) {
                $funcname = $funcname[0] . '::' . $funcname[1];
            }
            user_error('array_walk_recursive() Not a valid callback ' . $funcname,
                E_USER_WARNING);
            return;
        }

        if (!is_array($input)) {
            user_error('array_walk_recursive() The argument should be an array',
                E_USER_WARNING);
            return;
        }

        $args = func_get_args();

        foreach ($input as $key => $item) {
            $callArgs = $args;
            if (is_array($item)) {
                $thisCall = 'array_walk_recursive';
                $callArgs[1] = $funcname;
            } else {
                $thisCall = $funcname;
                $callArgs[1] = $key;
            }
            $callArgs[0] = &$input[$key];
            call_user_func_array($thisCall, $callArgs);
        }    
    }
}

/**
 * Replace array_combine()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_combine
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.21 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('array_combine')) {
    function array_combine($keys, $values)
    {
        if (!is_array($keys)) {
            user_error('array_combine() expects parameter 1 to be array, ' .
                gettype($keys) . ' given', E_USER_WARNING);
            return;
        }

        if (!is_array($values)) {
            user_error('array_combine() expects parameter 2 to be array, ' .
                gettype($values) . ' given', E_USER_WARNING);
            return;
        }

        $key_count = count($keys);
        $value_count = count($values);
        if ($key_count !== $value_count) {
            user_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
            return false;
        }

        if ($key_count === 0 || $value_count === 0) {
            user_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
            return false;
        }

        $keys    = array_values($keys);
        $values  = array_values($values);

        $combined = array();
        for ($i = 0; $i < $key_count; $i++) {
            $combined[$keys[$i]] = $values[$i];
        }

        return $combined;
    }
}

if (!defined('FILE_USE_INCLUDE_PATH')) {
    define('FILE_USE_INCLUDE_PATH', 1);
}

if (!defined('FILE_APPEND')) {
    define('FILE_APPEND', 8);
}


/**
 * Replace file_put_contents()
 *
 * @category   PHP
 * @package    PHP_Compat
 * @link        http://php.net/function.file_put_contents
 * @author     Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.26 $
 * @internal    resource_context is not supported
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $content, $flags = null, $resource_context = null)
    {
        // If $content is an array, convert it to a string
        if (is_array($content)) {
            $content = implode('', $content);
        }

        // If we don't have a string, throw an error
        if (!is_scalar($content)) {
            user_error('file_put_contents() The 2nd parameter should be either a string or an array',
                E_USER_WARNING);
            return false;
        }

        // Get the length of data to write
        $length = strlen($content);

        // Check what mode we are using
        $mode = ($flags & FILE_APPEND) ?
                    'a' :
                    'wb';

        // Check if we're using the include path
        $use_inc_path = ($flags & FILE_USE_INCLUDE_PATH) ?
                    true :
                    false;

        // Open the file for writing
        if (($fh = @fopen($filename, $mode, $use_inc_path)) === false) {
            user_error('file_put_contents() failed to open stream: Permission denied',
                E_USER_WARNING);
            return false;
        }

        // Attempt to get an exclusive lock
        $use_lock = ($flags & LOCK_EX) ? true : false ;
        if ($use_lock === true) {
            if (!flock($fh, LOCK_EX)) {
                return false;
            }
        }

        // Write to the file
        $bytes = 0;
        if (($bytes = @fwrite($fh, $content)) === false) {
            $errormsg = sprintf('file_put_contents() Failed to write %d bytes to %s',
                            $length,
                            $filename);
            user_error($errormsg, E_USER_WARNING);
            return false;
        }

        // Close the handle
        @fclose($fh);

        // Check all the data was written
        if ($bytes != $length) {
            $errormsg = sprintf('file_put_contents() Only %d of %d bytes written, possibly out of free disk space.',
                            $bytes,
                            $length);
            user_error($errormsg, E_USER_WARNING);
            return false;
        }

        // Return length
        return $bytes;
    }
}

/**
 * Replace stripos()
 *
 * @category   PHP
 * @package    PHP_Compat
 * @link        http://php.net/function.stripos
 * @author     Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.13 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('stripos')) {
    function stripos($haystack, $needle, $offset = null)
    {
        if (!is_scalar($haystack)) {
            user_error('stripos() expects parameter 1 to be string, ' .
                gettype($haystack) . ' given', E_USER_WARNING);
            return false;
        }

        if (!is_scalar($needle)) {
            user_error('stripos() needle is not a string or an integer.', E_USER_WARNING);
            return false;
        }

        if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
            user_error('stripos() expects parameter 3 to be long, ' .
                gettype($offset) . ' given', E_USER_WARNING);
            return false;
        }

        // Manipulate the string if there is an offset
        $fix = 0;
        if (!is_null($offset)) {
            if ($offset > 0) {
                $haystack = substr($haystack, $offset, strlen($haystack) - $offset);
                $fix = $offset;
            }
        }

        $segments = explode(strtolower($needle), strtolower($haystack), 2);

        // Check there was a match
        if (count($segments) === 1) {
            return false;
        }

        $position = strlen($segments[0]) + $fix;
        return $position;
    }
}

/**
 * Replace str_split()
 *
 * @category   PHP
 * @package    PHP_Compat
 * @link        http://php.net/function.str_split
 * @author     Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.16 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
 if (!function_exists('str_split')) {
    function str_split($string, $split_length = 1)
    {
        if (!is_scalar($split_length)) {
            user_error('str_split() expects parameter 2 to be long, ' .
                gettype($split_length) . ' given', E_USER_WARNING);
            return false;
        }

        $split_length = (int) $split_length;
        if ($split_length < 1) {
            user_error('str_split() The length of each segment must be greater than zero', E_USER_WARNING);
            return false;
        }

        // Select split method
        if ($split_length < 65536) {
            // Faster, but only works for less than 2^16
            preg_match_all('/.{1,' . $split_length . '}/s', $string, $matches);
            return $matches[0];
        } else {
            // Required due to preg limitations
            $arr = array();
            $idx = 0;
            $pos = 0;
            $len = strlen($string);

            while ($len > 0) {
                $blk = ($len < $split_length) ? $len : $split_length;
                $arr[$idx++] = substr($string, $pos, $blk);
                $pos += $blk;
                $len -= $blk;
            }

            return $arr;
        }
    }
}

/**
 * Replace strripos()
 *
 * @category   PHP
 * @package    PHP_Compat
 * @link        http://php.net/function.strripos
 * @author     Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.25 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('strripos')) {
    function strripos($haystack, $needle, $offset = null)
    {
        // Sanity check
        if (!is_scalar($haystack)) {
            user_error('strripos() expects parameter 1 to be scalar, ' .
                gettype($haystack) . ' given', E_USER_WARNING);
            return false;
        }

        if (!is_scalar($needle)) {
            user_error('strripos() expects parameter 2 to be scalar, ' .
                gettype($needle) . ' given', E_USER_WARNING);
            return false;
        }

        if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
            user_error('strripos() expects parameter 3 to be long, ' .
                gettype($offset) . ' given', E_USER_WARNING);
            return false;
        }

        // Initialise variables
        $needle         = strtolower($needle);
        $haystack       = strtolower($haystack);
        $needle_fc      = $needle{0};
        $needle_len     = strlen($needle);
        $haystack_len   = strlen($haystack);
        $offset         = (int) $offset;
        $leftlimit      = ($offset >= 0) ? $offset : 0;
        $p              = ($offset >= 0) ?
                                $haystack_len :
                                $haystack_len + $offset + 1;

        // Reverse iterate haystack
        while (--$p >= $leftlimit) {
            if ($needle_fc === $haystack{$p} &&
                substr($haystack, $p, $needle_len) === $needle) {
                return $p;
            }
        }

        return false;
    }
}

/**
 * Convert special HTML entities back to characters
 * @see http://www.php.net/htmlspecialchars_decode
 */
if (!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT)
    {
        return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
    }
}

/**
 * Returns directory path used for temporary files
 * @see http://www.php.net/sys_get_temp_dir
 */
if (!function_exists('sys_get_temp_dir')) {
    function sys_get_temp_dir()
    {
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }
        
        if (!empty($_ENV['TMPDIR'])) {
            return realpath( $_ENV['TMPDIR']);
        }
        
        if (!empty($_ENV['TEMP'])) {
            return realpath( $_ENV['TEMP']);
        }
        
        $tempfile = tempnam(uniqid(rand(),TRUE),'');
        if (file_exists($tempfile)) {
            @unlink($tempfile);
            return realpath(dirname($tempfile));
        }

        return false;
    }
}

/**
 * Converts the hex representation of data to binary
 * @see http://www.php.net/hex2bin
 */
if (!function_exists('hex2bin')) {
    function hex2bin($data)
    {
        return pack("H*", $data);
    }
}

/**
 * Get GMT/UTC date/time information
 * @see http://www.php.net/getdate
 */
function gmgetdate($ts = null)
{
    $k = array('seconds','minutes','hours','mday', 'wday','mon','year','yday','weekday','month', 0);
    return array_combine($k, explode(':', gmdate('s:i:G:j:w:n:Y:z:l:F:U', is_null($ts)? time() : $ts)));
}

/**
 * Parse about any English textual datetime description into a GMT/UTC Unix timestamp
 * @see http://www.php.net/strtotime
 */
function gmstrtotime($time)
{
    return(strtotime($time. ' UTC'));
}
