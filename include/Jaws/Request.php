<?php
/**
 * Short description
 *
 * Long description
 *
 * @category   Jaws
 * @package    Jaws_Request
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Request
{
    /**
     * Allowed HTML tags
     *
     * @var     array
     * @access  private
     */
    private $allowed_tags = '<a><img><ol><ul><li><blockquote><cite><code><div><p><pre><span><del><ins>
        <strong><b><mark><i><s><u><em>';

    /**
     * Allowed HTML tag attributes
     *
     * @var array
     * @access  private
     */
    private $allowed_attributes = array('href', 'src', 'alt', 'title');

    /**
     *  URL based HTML tag attributes
     *
     * @var     array
     * @access  private
     */
    private $urlbased_attributes = array('href', 'src');

    /**
     * Allowed URL pattern
     *
     * @var     string
     * @access  private
     */
    private $allowed_url_pattern = "@(^[(http|https|ftp)://]?)(?!javascript:)([^\\\\[:space:]\"]+)$@iu";

    /**
     * Request filters
     *
     * @var     array
     * @access  private
     */
    private $_filters;

    /**
     * Request filters parameters
     *
     * @var     array
     * @access  private
     */
    private $_params;

    /**
     * Request filters priority
     *
     * @var array
     * @access  private
     */
    private $_filtersPriority;

    /**
     * Request filters include files
     *
     * @var     array
     * @access  private
     */
    private $_includes;

    /**
     * Allowed request types
     *
     * @var     array
     * @access  private
     */
    private $_allowedMethods = array('get', 'post', 'cookie');

    /**
     * variable type check functions
     *
     * @var     array
     * @access  private
     */
    private $func_type_check = array(
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
        // initialize some server options
        $_SERVER['REQUEST_METHOD'] =
            array_key_exists('REQUEST_METHOD', $_SERVER)? strtoupper($_SERVER['REQUEST_METHOD']): 'GET';
        $_SERVER['CONTENT_TYPE'] =
            array_key_exists('CONTENT_TYPE', $_SERVER)? $_SERVER['CONTENT_TYPE']: '';
        $_SERVER['HTTP_USER_AGENT'] =
            array_key_exists('HTTP_USER_AGENT', $_SERVER)? $_SERVER['HTTP_USER_AGENT']: '';
        $_SERVER['HTTP_REFERER'] =
            array_key_exists('HTTP_REFERER', $_SERVER)? $_SERVER['HTTP_REFERER']: '';

        if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'iis')) {
            $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];
        }

        // Prevent user interface redress attack(Click-jacking)
        header('X-Frame-Options: SAMEORIGIN');

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

        // Add request filters
        $this->addFilter('strip_null', array($this, 'strip_null'));
        $this->addFilter('htmlclean',  'htmlspecialchars', array(ENT_QUOTES, 'UTF-8'));
        $this->addFilter('ambiguous',  array($this, 'strip_ambiguous'));
        $this->addFilter('strip_crlf', array($this, 'strip_crlf'));

        // Strict mode
        unset($_GET);
        unset($_POST);
        unset($_REQUEST);
        unset($_COOKIE);
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
     * Adds a filter that will be run on output requested data
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
        $this->_filtersPriority[] = $name;
        if ($include != '') {
            $this->_includes[$name] = $include;
        }
    }

    /**
     * Strip null character
     *
     * @access  public
     * @param   string  $value
     * @return  string  The striped data
     */
    function strip_null($value)
    {
        return preg_replace(array('/\0+/', '/(\\\\0)+/'), '', $value);
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
        return preg_replace('/%00/', '', $value);
    }

    /**
     * Strip CRLF/CR/0x00A0 by replace LF/LF/0x20
     *
     * @access  public
     * @param   string  $value
     * @return  string  The striped data
     */
    function strip_crlf($value)
    {
        return preg_replace(array("@\r\n@smu", "@\r@smu", "@\x{00a0}@smu"), array("\n", "\n", ' '), $value);
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
     * @param   string  $value      Referenced value
     * @param   mixed   $key        Reserved for array item key
     * @param   mixed   $filters    Filter(s) name
     * @return  string  The filtered data
     */
    function filter(&$value, $key, $filters)
    {
        if (is_string($value)) {
            foreach ($filters as $filter) {
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
    private function _fetch($keys, $method = '', $filters = true, $xss_strip = false, $json_decode = false)
    {
        $method = empty($method)? strtolower($_SERVER['REQUEST_METHOD']) : $method;
        if (is_array($keys)) {
            $result = array();
            foreach ($keys as $key) {
                $k = strtok($key, ':');
                $result[$k] = $this->_fetch($key, $method, $filters, $xss_strip, $json_decode);
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

            if ($filters === true) {
                $filters = $this->_filtersPriority;
            } elseif (!empty($filters)) {
                $filters = array('strip_null', $filters);
            } else {
                $filters = array('strip_null');
            }

            if (is_array($value)) {
                array_walk_recursive($value, array(&$this, 'filter'), $filters);
            } else {
                $this->filter($value, $key, $filters);
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
     *                                  an array will be returned accordingly.
     * @param   mixed   $methods        Which request type is being fetched from, it can be an array
     * @param   bool    $filter         Returns filtered data or not
     * @param   bool    $xss_strip      Returns stripped html data tags/attributes
     * @param   bool    $json_decode    Decode JSON data or not
     * @return  mixed   Returns string or an array depending on the key, otherwise Null if key not exist
     */
    function fetch($keys, $methods = '', $filter = true, $xss_strip = false, $json_decode = false)
    {
        $result = null;
        if (empty($methods)) {
            switch (strtolower($_SERVER['REQUEST_METHOD'])) {
                case 'get':
                    $methods = array('get', 'post');
                    break;

                case 'post':
                    $methods = array('post', 'get');
                    break;

                default:
                    return null;
            }
        } elseif (!is_array($methods)) {
            $methods = array($methods);
        }

        foreach ($methods as $method) {
            $result = $this->_fetch($keys, $method, $filter, $xss_strip, $json_decode);
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
     * @param   string  $method     Request method type
     * @param   bool    $filter     Returns filtered data
     * @param   bool    $xss_strip  Returns stripped html data tags/attributes
     * @return  array   Filtered Data array
     */
    function fetchAll($method = '', $filter = true, $xss_strip = false)
    {
        $method = empty($method)? strtolower($_SERVER['REQUEST_METHOD']) : $method;
        if (!isset($this->data[$method]) || empty($this->data[$method])) {
            return array();
        }

        $keys = array_keys($this->data[$method]);
        $keys = preg_replace('/[^[:alnum:]_\.-]/', '', $keys);

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
     * @param   string  $key        Key name
     * @param   mixed   $value      Key value
     * @param   string  $method     Request method
     * @return  bool    True
     */
    function update($key, $value, $method = '')
    {
        $method = empty($method)? strtolower($_SERVER['REQUEST_METHOD']) : $method;
        $this->data[$method][$key] = $value;
        return true;
    }

    /**
     * Reset super global request variables
     *
     * @access  public
     * @param   string  $method Which method is being reset, if no passed value reset all method variables
     * @return  bool    True
     */
    function reset($method = '')
    {
        switch ($method) {
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