<?php
/**
 * Global functions
 *
 * @category    JawsType
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 *
 */
require_once ROOT_JAWS_PATH . 'include/Jaws.php';
spl_autoload_register('Jaws::loadClass');

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
 * Generate 64bit hash integer
 * @param   string  $str    The input string
 * @return  int     64bit integer
 */
function hash64($str)
{
    $u = unpack('N2', sha1($str, true));
    return abs(($u[1] << 32) | $u[2]);
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

/**
 * Find the last occurrence of a string
 *
 * @param   string  $haystack       The input string
 * @param   mixed   $needle         If needle is not a string, it is converted to ordinal value of a character
 * @param   bool    $before_needle  If TRUE, it returns the part of the haystack
                                    before the last occurrence of the needle(excluding needle)
 * @return  mixed   Returns the portion of string, or FALSE if needle is not found
 * @see     http://www.php.net/strstr
 */
if (!function_exists('strrstr')) {
    function strrstr($haystack, $needle, $before_needle = false)
    {
        if (false === $pos = strrpos($haystack, $needle)) {
            return false;
        }

        if ($before_needle) {
            $retval = substr($haystack, 0, $pos);
        } else {
            $retval = substr($haystack, $pos);
        }

        return $retval;
    }
}

/**
 * Case-insensitive strrstr
 *
 * @param   string  $haystack       The input string
 * @param   mixed   $needle         If needle is not a string, it is converted to ordinal value of a character
 * @param   bool    $before_needle  If TRUE, it returns the part of the haystack
                                    before the last occurrence of the needle(excluding needle)
 * @return  mixed   Returns the portion of string, or FALSE if needle is not found
 * @see     http://www.php.net/stristr
 */
if (!function_exists('strristr')) {
    function strristr($haystack, $needle, $before_needle = false)
    {
        if (false === $pos = strripos($haystack, $needle)) {
            return false;
        }

        if ($before_needle) {
            $retval = substr($haystack, 0, $pos);
        } else {
            $retval = substr($haystack, $pos);
        }

        return $retval;
    }
}

/**
 * Converts MIME-encoded text to UTF-8
 *
 * @param   string  $text   MIME encoded string
 * @return  string  Returns an UTF-8 encoded string
 * @see     http://php.net/imap-utf8
 */
function mime_decode($text) {
    if (function_exists('mb_detect_encoding')) {
        if (($src_enc = mb_detect_encoding($text)) && (strcasecmp($src_enc, 'ASCII') !== 0)) {
            return imap_utf8($text);
        }
    }

    $str = '';
    $parts = imap_mime_header_decode($text);
    foreach ($parts as $part) {
        $str.= imap_utf8($part->text);
    }

    return $str? $str : imap_utf8($text);
}

/**
 * Get or Set the HTTP response code
 * @see http://www.php.net/http_response_code
 */
if (!function_exists('http_response_code')) {
    function http_response_code($code = null)
    {
        static $http_status_code = 200;
        if (is_null($code)) {
            return $http_status_code;
        }

        $http_status_code = (int)$code;
        switch ($http_status_code) {
            case 100:
                $text = 'Continue';
                break;
            case 101:
                $text = 'Switching Protocols';
                break;
            case 200:
                $text = 'OK';
                break;
            case 201:
                $text = 'Created';
                break;
            case 202:
                $text = 'Accepted';
                break;
            case 203:
                $text = 'Non-Authoritative Information';
                break;
            case 204:
                $text = 'No Content';
                break;
            case 205:
                $text = 'Reset Content';
                break;
            case 206:
                $text = 'Partial Content';
                break;
            case 300:
                $text = 'Multiple Choices';
                break;
            case 301:
                $text = 'Moved Permanently';
                break;
            case 302:
                $text = 'Moved Temporarily';
                break;
            case 303:
                $text = 'See Other';
                break;
            case 304:
                $text = 'Not Modified';
                break;
            case 305:
                $text = 'Use Proxy';
                break;
            case 400:
                $text = 'Bad Request';
                break;
            case 401:
                $text = 'Unauthorized';
                break;
            case 402:
                $text = 'Payment Required';
                break;
            case 403:
                $text = 'Forbidden';
                break;
            case 404:
                $text = 'Not Found';
                break;
            case 405:
                $text = 'Method Not Allowed';
                break;
            case 406:
                $text = 'Not Acceptable';
                break;
            case 407:
                $text = 'Proxy Authentication Required';
                break;
            case 408:
                $text = 'Request Time-out';
                break;
            case 409:
                $text = 'Conflict';
                break;
            case 410:
                $text = 'Gone';
                break;
            case 411:
                $text = 'Length Required';
                break;
            case 412:
                $text = 'Precondition Failed';
                break;
            case 413:
                $text = 'Request Entity Too Large';
                break;
            case 414:
                $text = 'Request-URI Too Large';
                break;
            case 415:
                $text = 'Unsupported Media Type';
                break;
            case 500:
                $text = 'Internal Server Error';
                break;
            case 501:
                $text = 'Not Implemented';
                break;
            case 502:
                $text = 'Bad Gateway';
                break;
            case 503:
                $text = 'Service Unavailable';
                break;
            case 504:
                $text = 'Gateway Time-out';
                break;
            case 505:
                $text = 'HTTP Version not supported';
                break;
            default:
                $text = 'Unknown http status code';
            break;
        }

        header(Jaws_XSS::filter($_SERVER['SERVER_PROTOCOL']). " $http_status_code $text");
        return $http_status_code;
    }
}
/**
 * Build a URL
 * @see http://php.net/manual/fa/function.parse-url.php
 */
if (!function_exists('build_url'))
{
    /**
     * @param   array   $parts
     * @return  string
     */
    function build_url(array $parts)
    {
        $scheme   = isset($parts['scheme'])? ($parts['scheme'] . '://') : '';
        $host     = isset($parts['host'])? $parts['host'] : '';
        $port     = isset($parts['port'])? (':' . $parts['port']) : '';
        $user     = isset($parts['user'])? $parts['user'] : '';
        $pass     = isset($parts['pass'])? (':' . $parts['pass'])  : '';
        $pass     = ($user || $pass)? "$pass@" : '';
        $path     = isset($parts['path'])? $parts['path'] : '';        
        $query    = isset($parts['query'])? ('?' . $parts['query']) : '';        
        $fragment = isset($parts['fragment'])? ('#' . $parts['fragment']) : '';

        return implode('', [$scheme, $user, $pass, $host, $port, $path, $query, $fragment]);
    }
}

/**
 * Timing attack safe string comparison
 *
 * @param   string  $known_string   The string of known length to compare against 
 * @param   string  $user_string    The user-supplied string 
 * @return  bool    Returns TRUE when the two strings are equal, FALSE otherwise
 * @see     http://www.php.net/hash-equals
 */
if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string)
    {
        if (strlen($known_string) !== strlen($user_string)) {
            return false;
        }

        $ret = 0;
        $res = $known_string ^ $user_string;

        for ($i = strlen($res) - 1; $i >= 0; $i--) {
            $ret |= ord($res[$i]);
        }

        return !$ret;
    }
}

/**
 * Convenience function to translate strings.
 *
 * Passes it's arguments to Jaws_Translate::Translate to do the actual translation.
 *
 * @access  public
 * @param   string  string The string to translate.
 * @return  string
 */
function _t($string)
{
    $args = array();
    if (func_num_args() > 1) {
        $args = func_get_args();

        // Argument 1 is the string to be translated.
        array_shift($args);
    }

    return Jaws_Translate::getInstance()->Translate(null, $string, $args);
}

/**
 * Convenience function to translate strings.
 *
 * Passes it's arguments to Jaws_Translate::Translate to do the actual translation.
 *
 * @access  public
 * @param   string  lang The language.
 * @param   string  string The string to translate.
 * @return  string
 */
function _t_lang($lang, $string)
{
    $args = array();
    if (func_num_args() > 2) {
        $args = func_get_args();

        // Argument 1th for lang and argument 2th is the string to be translated.
        array_shift($args);
        array_shift($args);
    }

    return Jaws_Translate::getInstance()->Translate($lang, $string, $args);
}

/**
 * Terminate script
 *
 * @param   mixed   $data   Response data
 * @param   bool    $sync   Synchronize session
 * @return  void
 */
function terminate(&$data = null, $status_code = 0, $next_location = '', $sync = true)
{
    // Send content to client
    $resType = Jaws::getInstance()->request->fetch('restype');

    // Event logging
    if (Jaws::getInstance(false)) {
        $gadget = Jaws::getInstance()->mainGadget;
        $action = Jaws::getInstance()->mainAction;
        $sync = property_exists(Jaws::getInstance(), 'session')? $sync : false;

        $loglevel = 0;
        if (!empty($gadget) && !empty($action)) {
            $loglevel = @Jaws_Gadget::getInstance($gadget)->actions[JAWS_SCRIPT][$action]['loglevel'];
        }
        // shout log event
        if (property_exists(Jaws::getInstance(), 'session')) {
            Jaws::getInstance()->listener->Shout(
                'Action',
                'Log',
                array(
                    'gadget'   => $gadget,
                    'action'   => $action,
                    'priority' => $loglevel,
                    'status'   => http_response_code(),
                )
            );
        }
    } else {
        $gadget = '';
        $action = '';
        $sync = false;
    }

    // detect Ajax request
    $XMLHttpRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    if (!empty($next_location) && $XMLHttpRequest) {
        if (!empty($gadget)) {
            $data = Jaws_Gadget::getInstance($gadget)->session->pop($data);
        } else {
            $data = Jaws::getInstance()->session->popResponse($data);
        }
    }

    // Sync session
    if (property_exists(Jaws::getInstance(), 'session') && $sync) {
        Jaws::getInstance()->session->update();
    }

    if (!empty($next_location) && !$XMLHttpRequest) {
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Location: '.$next_location, true, $status_code);
    } else {
        // set response status code
        if (!empty($status_code) && !in_array($status_code, array(301, 302))) {
            http_response_code($status_code);
        }
    }

    // encode data based on response type
    $data = Jaws_Response::get($resType, $data);

    // return data
    echo $data;

    if (isset($GLOBALS['log'])) {
        $GLOBALS['log']->End();
    }

    exit;
}
