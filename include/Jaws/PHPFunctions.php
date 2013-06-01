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

/**
 * Returns the values from a single column of the input array
 * @see http://www.php.net/array_column
 */
if (!function_exists('array_column')) {
    function array_column($input, $columnKey, $indexKey = null)
    {
        if (!empty($input)) {
            array_unshift($input, null);
            $input = array_combine(array_keys($input[1]), call_user_func_array('array_map', $input));
            if (is_null($indexKey)) {
                return $input[$columnKey];
            } else {
                if (is_array($input[$indexKey])) {
                    return array_combine($input[$indexKey], $input[$columnKey]);
                } else {
                    return array($input[$indexKey] => $input[$columnKey]);
                }
            }
        }

        return $input;
    }
}
