<?php
/**
 * Get rid of register_globals things if active
 *
 * @author Richard Heyes
 * @author Stefan Esser
 * @url http://www.phpguru.org/article.php?ne_id=60
 */
$_SERVER['REQUEST_METHOD']  = array_key_exists('REQUEST_METHOD', $_SERVER)?
                                               strtoupper($_SERVER['REQUEST_METHOD']):
                                               'GET';
$_SERVER['CONTENT_TYPE']    = array_key_exists('CONTENT_TYPE', $_SERVER)?
                                               $_SERVER['CONTENT_TYPE']:
                                               '';
$_SERVER['HTTP_USER_AGENT'] = array_key_exists('HTTP_USER_AGENT', $_SERVER)?
                                               $_SERVER['HTTP_USER_AGENT']:
                                               '';
$_SERVER['HTTP_REFERER']    = array_key_exists('HTTP_REFERER', $_SERVER)?
                                               $_SERVER['HTTP_REFERER']:
                                               '';

// Prevent user interface redress attack(Clickjacking)
header('X-Frame-Options: SAMEORIGIN');

if (ini_get('register_globals')) {
    // Might want to change this perhaps to a nicer error
    if (isset($_REQUEST['GLOBALS'])) {
        Jaws_Error::Fatal('GLOBALS overwrite attempt detected');
    }

    // Variables that shouldn't be unset
    $noUnset = array('GLOBALS',  '_GET',
                     '_POST',    '_COOKIE',
                     '_REQUEST', '_SERVER',
                     '_ENV',     '_FILES');

    $input = array_merge($_GET,    $_POST,
                         $_COOKIE, $_SERVER,
                         $_ENV,    $_FILES,
                         isset($_SESSION) ? $_SESSION : array());

    foreach ($input as $k => $v) {
        if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
            unset($GLOBALS[$k]);
        }
    }
}

/**
 * We don't like magic_quotes, so we disable it ;-)
 *
 * Basis of the code were gotten from the book
 * php archs guid to PHP Security
 * @auhor Illia Alshanetsky <ilia@php.net>
 */
@set_magic_quotes_runtime(0);
if (get_magic_quotes_gpc()) {
    $input = array(&$_GET, &$_POST, &$_REQUEST, &$_COOKIE, &$_ENV, &$_SERVER);

    while (list($k, $v) = each($input)) {
        foreach ($v as $key => $val) {
            if (!is_array($val)) {
                $key = stripslashes($key);
                $input[$k][$key] = stripslashes($val);
                continue;
            }
            $input[] =& $input[$k][$key];
        }
    }
    unset($input);
}

/**
 * Short description
 *
 * Long description
 *
 * @category   Jaws
 * @package    Jaws_Request
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Request
{
    /**
     * @var array
     */
    private $allowed_tags = '<a><img><ol><ul><li><blockquote><cite><code><div><p><pre><span><del><ins>
        <strong><b><mark><i><s><u><em>';

    /**
     * @var array
     */
    private $allowed_attributes = array('href', 'src', 'alt', 'title');

    /**
     * @var array
     */
    private $urlbased_attributes = array('href', 'src');

    /**
     *
     */
    private $allowed_url_pattern = "@(^[(http|https|ftp)://]?)(?!javascript:)([^\\\\[:space:]\"]+)$@iu";

    /**
     * @var array
     */
    var $_filters;

    /**
     * @var array
     */
    var $_params;

    /**
     * @var array
     */
    var $_priority;

    /**
     * @var array
     */
    var $_includes;

    /**
     * @var array
     */
    var $_allowedMethods = array('get', 'post', 'cookie');

    /**
     * @var array
     */
    var $func_type_check = array(
        '0'      => 'is_scalar',
        'int'    => 'is_numeric',
        'float'  => 'is_float',
        'string' => 'is_string',
        'array'  => 'is_array',
        'bool'   => 'is_bool',
    );

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function Jaws_Request()
    {
        $this->_filters  = array();
        $this->_params   = array();
        $this->_priority = array();
        $this->_includes = array();
        $this->data['get']    = $_GET;
        $this->data['cookie'] = $_COOKIE;
        // support json encoded posted data
        if (false !== strpos($_SERVER['CONTENT_TYPE'], 'application/json')) {
            $json = file_get_contents('php://input');
            $this->data['post'] = Jaws_UTF8::json_decode($json);
        } else {
            $this->data['post'] = $_POST;
        }

        array_walk_recursive($this->data, array(&$this, 'strip_null'));

        // Add request filters
        $this->addFilter('htmlclean', 'htmlspecialchars', array(ENT_QUOTES, 'UTF-8'));
        $this->addFilter('ambiguous', array($this, 'strip_ambiguous'));

        // Strict mode
        /*
        if (true) {
            unset($_GET);
            unset($_POST);
            unset($_REQUEST);
            unset($_COOKIE);
        }
        */
    }

    /**
     * Creates the Jaws_Request instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @return  object returns the instance
     */
    static function getInstance()
    {
        static $objRequest;
        if (!isset($objRequest)) {
            $objRequest = new Jaws_Request();
        }

        return $objRequest;
    }

    /**
     * @param   string  $type
     * @return  mixed
     */
    function isTypeValid($type)
    {
        $type = strtolower($type);
        if (in_array($type, $this->_allowedMethods)) {
            return $type;
        }

        return false;
    }

    /**
     * Adds a filter that will be runned on output requested data
     *
     * @access  public
     * @param   string  $name       Name of the filter
     * @param   string  $function   The function that will be executed
     * @param   string  $params     Path of the included if it's needed for the function
     * @param   string  $include    Filename that include the filter function
     * @return  void
     */
    function addFilter($name, $function, $params = null, $include = '')
    {
        $this->_filters[$name] = $function;
        $this->_params[$name]  = $params;
        $this->_priority[]     = $name;
        if ($include != '') {
            $this->_includes[$name] = $include;
        }
    }

    /**
     * Strip null character
     *
     * @access  public
     * @param   string  $value  Referenced value
     * @return  void
     */
    function strip_null(&$value)
    {
        if (is_string($value)) {
            $value = preg_replace(array('/\0+/', '/(\\\\0)+/'), '', $value);
        }
    }

    /**
     * Strip ambiguous characters
     *
     * @access  public
     * @param   string  $value
     * @return  string  The striped data
     */
    function strip_ambiguous($value)
    {
        if (is_string($value)) {
            return preg_replace('/%00/', '', $value);
        }
    }

    /**
     * Strip not allowed tags/attributes
     *
     * @access  public
     * @param   string  $text  Text
     * @return  string  stripped text 
     */
    function strip_tags_attributes($text)
    {
        $result = '';
        // strip not allowed tags
        $text = strip_tags($text, $this->allowed_tags);
        $hxml = simplexml_load_string(
            '<?xml version="1.0" encoding="UTF-8"?><html>'. $text .'</html>',
            'SimpleXMLElement',
            LIBXML_NOERROR
        );
        if ($hxml) {
            foreach ($hxml->xpath('descendant::*[@*]') as $tag) {
                $attributes = (array)$tag->attributes();
                foreach ($attributes['@attributes'] as $attrname => $attrvalue) {
                    // strip not allowed attributes
                    if (!in_array(strtolower($attrname), $this->allowed_attributes)) {
                        unset($tag->attributes()->{$attrname});
                        continue;
                    }
                    // url based attributes
                    if (in_array(strtolower($attrname), $this->urlbased_attributes)) {
                        if (!preg_match($this->allowed_url_pattern, $attrvalue)) {
                            unset($tag->attributes()->{$attrname});
                            continue;
                        }
                    }
                }
            }

            // remove xml/html tags
            $result = substr($hxml->asXML(), 45, -8);
        }

        return $result;
    }

    /**
     * Filter data with added filter functions
     *
     * @access  public
     * @param   string  $value Referenced value
     * @return  string  The filtered data
     */
    function filter(&$value)
    {
        if (is_string($value)) {
            foreach ($this->_priority as $filter) {
                $function = $this->_filters[$filter];
                if (isset($this->_includes[$filter]) && file_exists($this->_includes[$filter])) {
                    include_once $this->_includes[$filter];
                }

                $params = array();
                $params[] = $value;
                if (is_array($this->_params[$filter])) {
                    $params = array_merge($params, $this->_params[$filter]);
                } else {
                    $params[] = $this->_params[$filter];
                }

                $value = call_user_func_array($function, $params);
            }
        }
    }

    /**
     * Does the recursion on the data being fetched
     *
     * @access  private
     * @param   mixed   $keys           The key being fetched, it can be an array with multiple keys in it to fetch and
     *                                  then an array will be returned accourdingly.
     * @param   string  $method         Which super global is being fetched from
     * @param   bool    $filter         Returns filtered data or not
     * @param   bool    $xss_strip      Returns stripped html data tags/attributes
     * @param   bool    $json_decode    Decode JSON data or not
     * @return  mixed   Null if there is no data else an string|array with the processed data
     */
    private function _fetch($keys, $method = '', $filter = true, $xss_strip = false, $json_decode = false)
    {
        $method = empty($method)? strtolower($_SERVER['REQUEST_METHOD']) : $method;
        if (is_array($keys)) {
            $result = array();
            foreach ($keys as $key) {
                $k = strtok($key, ':');
                $result[$k] = $this->_fetch($key, $method, $filter, $xss_strip, $json_decode);
            }

            return $result;
        }

        $key  = strtok($keys, ':');
        $type = strtok(':');

        if (isset($this->data[$method][$key])) {
            $value = $json_decode? Jaws_UTF8::json_decode($this->data[$method][$key]) : $this->data[$method][$key];
            // try unserialize value
            if (false !== $tvalue = @unserialize($value)) {
                $value = $tvalue;
                unset($tvalue);
            }

            // filter not allowed html tags/attributes
            if ($xss_strip) {
                $value = $this->strip_tags_attributes($value);
            }

            if ($filter) {
                if (is_array($value)) {
                    array_walk_recursive($value, array(&$this, 'filter'));
                } else {
                    $this->filter($value);
                }
            }

            return $this->func_type_check[$type]($value)? $value : null;
        }

        return null;
    }

    /**
     * Fetches the data, filters it and then it returns it.
     *
     * @access  public
     * @param   mixed   $keys           The key(s) being fetched, it can be an array with multiple keys in it to fetch and then
     *                                  an array will be returned accourdingly.
     * @param   mixed   $types          Which super global is being fetched from, it can be an array
     * @param   bool    $filter         Returns filtered data or not
     * @param   bool    $xss_strip      Returns stripped html data tags/attributes
     * @param   bool    $json_decode    Decode JSON data or not
     * @return  mixed   Returns string or an array depending on the key, otherwise Null if key not exist
     */
    function fetch($keys, $types = '', $filter = true, $xss_strip = false, $json_decode = false)
    {
        $result = null;
        if (empty($types)) {
            switch (strtolower($_SERVER['REQUEST_METHOD'])) {
                case 'get':
                    $types = array('get', 'post');
                    break;

                case 'post':
                    $types = array('post', 'get');
                    break;

                default:
                    return null;
            }
        } elseif (!is_array($types)) {
            $types = array($types);
        }

        foreach ($types as $type) {
            $result = $this->_fetch($keys, $type, $filter, $xss_strip, $json_decode);
            if (!is_null($result)) {
                break;
            }
        }

        return $result;
    }

    /**
     * Fetches the filtered data with out filter, it's like using the super globals straight.
     *
     * @access  public
     * @param   string  $type       Which super global is being fetched from
     * @param   bool    $filter     Returns filtered data
     * @param   bool    $xss_strip  Returns stripped html data tags/attributes
     * @return  array   Filtered Data array
     */
    function fetchAll($type = '', $filter = true, $xss_strip = false)
    {
        $type = empty($type)? strtolower($_SERVER['REQUEST_METHOD']) : $type;
        if (!isset($this->data[$type]) || empty($this->data[$type])) {
            return array();
        }

        $keys = array_keys($this->data[$type]);
        $values = array_map(
            array($this, '_fetch'),
            $keys,
            array_fill(0, count($keys), ''),
            array_fill(0, count($keys), $filter),
            array_fill(0, count($keys), $xss_strip)
        );

        return array_combine($keys, $values);
    }

    /** Creates a new key or updates an old one
     *
     * @param   string  $key
     * @param   mixed   $value
     * @param   string  $type
     * @return  bool    True
     */
    function update($key, $value, $type = '')
    {
        $type = empty($type)? strtolower($_SERVER['REQUEST_METHOD']) : $type;
        $this->data[$type][$key] = $value;
        return true;
    }

    /**
     * Reset super global request variables
     *
     * @access  public
     * @param   string  $type   Which super global is being reset,
     *                          if no passed value reset all super global request vaiables
     * @return  bool    True
     */
    function reset($type = '')
    {
        $type = $this->isTypeValid($type);
        switch ($type) {
            case 'get':
                unset($_GET);
                $this->data['get'] = array();
                break;

            case 'post':
                unset($_POST);
                $this->data['post'] = array();
                break;

            case 'cookie':
                unset($_COOKIE);
                $this->data['cookie'] = array();
                break;

            default:
                unset($_GET);
                unset($_POST);
                unset($_REQUEST);
                unset($_COOKIE);
                $this->data = array();
        }

        return true;
    }

}