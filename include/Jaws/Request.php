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
$_SERVER['HTTP_USER_AGENT'] = array_key_exists('HTTP_USER_AGENT', $_SERVER)?
                                               $_SERVER['HTTP_USER_AGENT']:
                                               '';
$_SERVER['HTTP_REFERER']    = array_key_exists('HTTP_REFERER', $_SERVER)?
                                               $_SERVER['HTTP_REFERER']:
                                               '';
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

    // between 5.0.0 and 5.1.0, array keys in the superglobals were escaped even with register_globals off
    $keybug = (version_compare(PHP_VERSION, '5.0.0', '>=') && version_compare(PHP_VERSION, '5.1.0', '<'));

    while (list($k, $v) = each($input)) {
        foreach ($v as $key => $val) {
            if (!is_array($val)) {
                $key = $keybug ? $key : stripslashes($key);
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
 * @copyright  2006 Helgi Þormar Þorbjörnsson
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Jaws_Request
{
    var $_filters;
    var $_params;
    var $_priority;
    var $_includes;
    var $_allowedTypes = array('get', 'post', 'cookie');

    /**
     * Constructor
     *
     * @return void
     * @access public
     */
    function Jaws_Request()
    {
        $this->_filters  = array();
        $this->_params   = array();
        $this->_priority = array();
        $this->_includes = array();
        $this->data['get']    = $_GET;
        $this->data['post']   = $_POST;
        $this->data['cookie'] = $_COOKIE;
        array_walk_recursive($this->data, array(&$this, 'nullstrip'));

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
     * Creates the Jaws_Request instance if it doesn't exist
     * else it returns the already created one.
     *
     * @return object returns the instance
     * @access public
     */
    function &getInstance()
    {
        static $instances;
        if (!isset($instances)) {
            $instances = array();
        }

        $signature = serialize(array('request'));
        if (!isset($instances[$signature])) {
            $instances[$signature] = new Jaws_Request;
        }

        return $instances[$signature];
    }

    function isTypeValid($type)
    {
        $type = strtolower($type);
        if (in_array($type, $this->_allowedTypes)) {
            return $type;
        }

        return false;
    }

    /**
     * Adds a filter that will be runned on all output beside _getRaw()
     *
     * @param string name of the filter
     * @param string the function that will be executed
     * @param string path of the included if it's needed for the function
     *
     * @return void
     * @access public
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
     * @param string    value
     * @return string   The striped data
     * @access public
     */
    function nullstrip(&$value)
    {
        $value = preg_replace(array('/\0+/', '/(\\\\0)+/'), '', $value);
    }

    /**
     * Strip ambiguous characters
     *
     * @param string    value
     * @return string   The striped data
     * @access public
     */
    function strip_ambiguous($value)
    {
        return preg_replace('/%00/', '', $value);
    }

    /**
     * Filter data with added filter functions
     *
     * @param string    value
     * @return string   The filtered data
     * @access public
     */
    function filter(&$value)
    {
        foreach ($this->_priority as $filter) {
            $function = $this->_filters[$filter];
            if (
                isset($this->_includes[$filter]) &&
                file_exists($this->_includes[$filter])
            ) {
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

    /**
     * Fetches the data with out filter, it's like using
     * the super globals straight.
     *
     * @param string the key being fetched
     * @param string which super global is being fetched from
     *
     * @access private
     */
    function _getRaw($key, $type = '')
    {
        if (empty($type)) {
            $type = strtolower($_SERVER['REQUEST_METHOD']);
        }

        $type = $this->isTypeValid($type);
        if (!$type) {
            return null;
        }

        if (is_array($key)) {
            $result = array();
            foreach ($key as $k) {
                $result[$k] = $this->_getRaw($k, $type);
            }

            return $result;
        }

        if (isset($this->data[$type][$key])) {
            return $this->data[$type][$key];
        }

        return null;
    }

    /**
     * Fetches the data with out filter, it's like using
     * the super globals straight.
     *
     * @param string which super global is being fetched from
     *
     * @access public
     */
    function getRawAll($type = '')
    {
        if (empty($type)) {
            $type = strtolower($_SERVER['REQUEST_METHOD']);
        }

        $type = $this->isTypeValid($type);
        if (!$type) {
            return null;
        }

        if (isset($this->data[$type])) {
            return $this->data[$type];
        }

        return null;
    }

    /**
     * Fetches the data, filters it and then it returns it.
     *
     * @param string|array the key being fetched, it can be an array
     *                     with multiple keys in it to fetch and then
     *                     an array will be returned accourdingly.
     *                     Works recursivly, ala $_GET['foo']['bar']['foobar']['helgi']
     * @param string|array which super global is being fetched from
     *
     * @return string|array The filtered data, string or an array depending on the key
     * @access public
     */
    function get($key, $types = '', $filter = true)
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
            if ($filter) {
                $result = $this->_get($key, $type);
            } else {
                $result = $this->_getRaw($key, $type);
            }

            if (!is_null($result)) {
                break;
            }
        }

        return $result;
    }

    /**
     * Does the recursion on the data being fetched
     *
     * @param array      Array filled with keys, recursion
     * @param string     Which super global is being fetched from
     * @param integer    The depth level
     * @param null|array The data that will be processed, if it's NULL
     *                   then it will populate it with the internal data
     *                   storage
     *
     * @return null|array null if there is no data else an array with the processed data
     * @access private
     */
    function _get($key, $type)
    {
        if (is_array($key)) {
            $result = array();
            foreach ($key as $k) {
                $result[$k] = $this->_get($k, $type);
            }

            return $result;
        }

        if (isset($this->data[$type][$key])) {
            $value = $this->data[$type][$key];

            if (is_array($value)) {
                array_walk_recursive($value, array(&$this, 'filter'));
            } else {
                $this->filter($value);
            }

            return $value;
        }

        return null;
    }

    /* Creates a new key or updates an old one, doesn't support recursive stuff atm. */
    /* One idea would be to have set('get', 'foo/bar/foobar', 'sm00ke') and resolve the path */
    /* another would be to allow arrays like crazy but still */
    function set($type, $key, $value)
    {
        $type = $this->isTypeValid($type);
        if (!$type) {
            return false;
        }

        $this->data[$type][$key] = $value;
        return true;
    }

    /**
     * Reset super global request variables
     *
     * @param string which super global is being reset,
     *               if passed value id empty reset all super global request vaiables
     *
     * @access public
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