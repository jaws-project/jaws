<?php
/**
 * Short description
 *
 * Long description
 *
 * @category    Jaws
 * @package     Jaws_Request
 * @author      Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Request
{
    /**
     * Request data
     *
     * @var     array
     * @access  private
     */
    private $data;

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
     * Regular expressions object instance
     *
     * @var     array
     * @access  private
     */
    private $regexp;

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
        '0'       => 'is_scalar',
        'int'     => 'is_numeric',
        'integer' => 'is_numeric',
        'float'   => 'is_float',
        'string'  => 'is_string',
        'array'   => 'is_array',
        'bool'    => 'is_bool',
        'boolean' => 'is_bool',
    );

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct()
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
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 
            array_key_exists('HTTP_ACCEPT_ENCODING', $_SERVER)? strtolower($_SERVER['HTTP_ACCEPT_ENCODING']) : '';

        if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'iis') && isset($_SERVER['UNENCODED_URL'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];
        }

        // Prevent user interface redress attack(Click-jacking)
        header('X-Frame-Options: SAMEORIGIN');

        $this->_filters  = array();
        $this->_params   = array();
        $this->_includes = array();
        $this->regexp = new Jaws_Regexp('/^(\w+)(?>\:(\w+))?(?>\|(\w+))?/');

        $this->data['get']    = $_GET;
        $this->data['cookie'] = $_COOKIE;
        // backup raw posted data
        $this->data['input'] = @file_get_contents('php://input');
        if (false !== strpos($_SERVER['CONTENT_TYPE'], 'application/json')) {
            // support json encoded posted data
            $this->data['post'] = json_decode($this->data['input'], true);
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
        return Jaws_XSS::getInstance()->strip($text);
    }

    /**
     * Gets request method type
     *
     * @access  public
     * @return  string  Returns request method type
     */
    function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
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
     * @param   bool    $type_validate  Data type check
     * @return  mixed   Null if there is no data else an string|array with the processed data
     */
    private function _fetch($keys, $method = '', $filters = true,
        $xss_strip = false, $json_decode = false, $type_validate = true
    ) {
        $method = empty($method)? strtolower($_SERVER['REQUEST_METHOD']) : $method;
        if (is_array($keys)) {
            $result = array();
            foreach ($keys as $key) {
                if (false === $this->regexp->match($key)) {
                    continue;
                }
                @list($all, $key, $valid_type, $cast_type) = $this->regexp->matches;
                $result[$key] = $this->_fetch($all, $method, $filters, $xss_strip, $json_decode, $type_validate);
            }

            return $result;
        }

        if (false === $this->regexp->match($keys)) {
            return null;
        }
        @list($all, $key, $valid_type, $cast_type) = $this->regexp->matches;

        // if key not exists
        if (!isset($this->data[$method][$key])) {
            $value = null;
            if ($cast_type) {
                // type cast
                settype($value, $cast_type);
            }
            return $value;
        }

        $value = $json_decode? json_decode($this->data[$method][$key]) : $this->data[$method][$key];
        // try unserialize value
        if (is_string($value) && false !== $tvalue = @unserialize($value)) {
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

        // type check
        if ($type_validate && $valid_type) {
            $value = $this->func_type_check[$valid_type]($value)? $value : null;
        }
        // type cast
        if (!is_null($value) && $cast_type) {
            settype($value, $cast_type);
        }

        return $value;
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
    function fetchAll($method = '', $filter = true, $xss_strip = false, $type_validate = true)
    {
        $method = empty($method)? strtolower($_SERVER['REQUEST_METHOD']) : $method;
        if (!isset($this->data[$method]) || empty($this->data[$method])) {
            return array();
        }

        $keys = array_keys($this->data[$method]);
        $keys = preg_replace('/[^[:alnum:]_\.\-]/', '', $keys);

        $values = array_map(
            array($this, '_fetch'),
            $keys,
            array_fill(0, count($keys), $method),
            array_fill(0, count($keys), $filter),
            array_fill(0, count($keys), $xss_strip),
            array_fill(0, count($keys), false),
            array_fill(0, count($keys), $type_validate)
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

    /** Delete input data by key
     *
     * @param   string  $key        Key name
     * @param   string  $method     Request method
     * @return  bool    True
     */
    function delete($key, $method = '')
    {
        $method = empty($method)? strtolower($_SERVER['REQUEST_METHOD']) : $method;
        unset($this->data[$method][$key]);
        return true;
    }

    /**
     * Get raw/untouched part of input data 
     *
     * @access  public
     * @param   string  $part   Part of request data(get|post|cookie|input)
     * @return  mixed   input data
     */
    function rawData($part = '')
    {
        return empty($part)? $this->data : $this->data[$part];
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