<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * PHP implementation of Pingback.
 *
 * This file contains Services_Pingback that is able to be work as Pingback
 * User-Agent.
 *
 * PHP versions 4 and 5
 *
 * LICENSE:
 * Copyright (c) 2005-2007 Firman Wandayandi <firman@php.net>
 * All rights reserved.
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to pear-dev@lists.php.net so we can send you a copy immediately.
 *
 * @category Web Services
 * @package Services_Pingback
 * @author Firman Wandayandi <firman@php.net>
 * @copyright Copyright (c) 2005-2007 Firman Wandayandi <firman@php.net>
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD license
 * @version CVS: $Id: Pingback.php,v 1.5 2007/08/03 11:14:03 firman Exp $
 */

 // }}}
 // {{{ Dependencies

/**
 * Load PEAR::Net_URL for URL manipulation.
 */
require_once 'Net/URL.php';

/**
 * Load PEAR::HTTP_Request for HTTP request interface.
 */
require_once 'HTTP/Request.php';

/**
 * Load PEAR::XML_RPC for XML_RPC interface.
 */
require_once 'XML/RPC.php';

/**
 * Load PEAR::XML_RPC_Server for creating Pingback server.
 */
require_once 'XML/RPC/Server.php';

// }}}
// {{{ Constants

/**
 * A generic fault code.
 */
define('SERVICES_PINGBACK_ERROR',                      0);

/**
 * The source URI does not exist.
 */
define('SERVICES_PINGBACK_ERROR_SOURCE_NOT_EXISTS',   16);

/**
 * The source URI does not contain a link to the target URI, and so cannot be
 * used as a source.
 */
define('SERVICES_PINGBACK_ERROR_NO_TARGET_LINK',      17);

/**
 * The specified target URI does not exist.
 */
define('SERVICES_PINGBACK_ERROR_TARGET_NOT_EXISTS',   32);

/**
 * The specified target URI cannot be used as a target.
 */
define('SERVICES_PINGBACK_ERROR_TARGET_NOT_RESOURCE', 33);

/**
 * The pingback has already been registered.
 */
define('SERVICES_PINGBACK_ERROR_ALREADY_REGISTERED',  48);

/**
 * Access denied.
 */
define('SERVICES_PINGBACK_ERROR_ACCESS_DENIED',       49);

/**
 * The server could not communicate with an upstream server, or received an
 * error from an upstream server, and therefore could not complete the request.
 * This is similar to HTTP's 402 Bad Gateway error.
 */
define('SERVICES_PINGBACK_ERROR_UPSTREAM_ERROR',      50);

/**
 * XML-RPC string type.
 */
define('SERVICES_PINGBACK_XML_RPC_STRING', $GLOBALS['XML_RPC_String']);

// }}}
// {{{ Global Variables

/**
 * An instance of Services_Pingback object.
 *
 * This is the way to make the server working when XML-RPC ping method called.
 * This also meant that only one instance Pingback server can be run at one time.
 *
 * @global object $GLOBALS['_Services_Pingback_server']
 * @name $_Services_Pingback_server
 */
$GLOBALS['_Services_Pingback_server'] = null;

// }}}
// {{{ Class: Services_Pingback

/**
 * Pingback user-agent class.
 *
 * This class implemented of Pingback 1.0 specification, visit
 * http://www.hixie.ch/specs/pingback/pingback for more information.
 *
 * @category Web Services
 * @package Services_Pingback
 * @author Firman Wandayandi <firman@php.net>
 * @copyright Copyright (c) 2005-2007 Firman Wandayandi <firman@php.net>
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD license
 * @version Release: 0.2.2
 * @todo Enabled access denied and upstream error response.
 * @todo Extract more pretty context of source and flexible direction.
 * @todo Finished PHPUnit test classes.
 */
class Services_Pingback
{
    // {{{ Properties

    /**
     * Pingback data.
     *
     * @var array
     * @access private
     */
    var $_data = array(
        'sourceURI'     => '',
        'targetURI'     => '',
        'pingbackURI'   => ''
    );

    /**
     * Available options for Services_Pingback.
     *
     * @var array
     * @access private
     */
    var $_options = array(
        'timeout'           => 30,
        'allowRedirects'    => true,
        'maxRedirects'      => 2,
        'fetchsize'         => 255,
        'debug'             => false
    );

    /**
     * Pingback XML-RPC dispatches method.
     *
     * @var array
     * @access private
     */
    var $_dispatches = array(
        'pingback.ping' => array(
            'function'  => array('Services_Pingback', 'ping'),
            'signature' => array(
                array(
                    SERVICES_PINGBACK_XML_RPC_STRING,
                    SERVICES_PINGBACK_XML_RPC_STRING,
                    SERVICES_PINGBACK_XML_RPC_STRING
                )
            ),
            'docstring' => 'Register your comment via Pingback sytem'
        )
    );

    /**
     * Registered pingback URIs.
     *
     * @var array
     * @access private
     */
    var $_registeredSources = array();

    /**
     * Entities translation table.
     *
     * @var array
     * @access private
     */
    var $_transEntities = array(
        '&amp;' => '&',
        '&lt;'  => '<',
        '&gt;'  => '>',
        '&quot' => '"'
    );

    /**
     * Fetched source context.
     *
     * @var string
     * @access private
     */
    var $_sourceContext = '';

    /**
     * XML_RPC error object if available.
     *
     * @var object
     * @access private
     */
    var $_XML_RPC_Error = null;

    // }}}
    // {{{ Constructor

    /**
     * Constructor.
     *
     * @param array $data The pingback data, are:
     * <pre>
     *      sourceURI                   The absolute URI of the post on the source page containing the link to the target site.
     *      targetURI                   The absolute URI of the target of the link, as given on the source page.
     *      pingbackURI     (optional)  Manually sets a Pingback URI.
     * </pre>
     *
     * @param array $options Option for pingback. Valid options are:
     * <pre>
     *      timeout         (optional)  Connection timeout in seconds (float), default 30 seconds.
     *      allowRedirects  (optional)  Whether follow redirect or not (bool), default TRUE.
     *      maxRedirects    (optional)  Max Number of redirects (int), default 2.
     *      fetchsize       (optional)  A size of string to fetch in bytes (int), default 255.
     *      debug           (optional)  Whether print out debug messages or not (bool), defaul FALSE.
     * </pre>
     *
     * @access public
     */
    function __construct($data = null, $options = null)
    {
        if (is_array($data)) {
            $this->setFromArray($data);
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    // }}}
    // {{{ setOptions()

    /**
     * Set the options of the Services_Pingback object.
     *
     * @param array $options An associative array contains options.
     *
     * @see create()
     * @access public
     */
    function setOptions($options)
    {
        if (!is_array($options)) {
            return PEAR::raiseError('Type mistmatch, only array accepted.');
        }

        foreach ($options as $option => $value) {
            if (!isset($this->_options[$option])) {
                return PEAR::raiseError('Unknown option "' . $option . '".');
            }

            switch ($option) {
                case 'timeout':
                    if (!is_float($value) || $value < 0) {
                        return PEAR::raiseError('Invalid value for options "' . $option . '".');
                    }
                    break;
                case 'allowRedirects':
                    if (!is_bool($value)) {
                        return PEAR::raiseError('Invalid value for option "'.$option.'".');
                    }
                    break;
                case 'maxRedirects':
                    if (!is_int($value) || ($value < 0)) {
                        return PEAR::raiseError('Invalid value for option "'.$option.'".');
                    }
                    break;
                case 'fetchsize':
                    if (!is_int($value) || $value <= 0) {
                        return PEAR::raiseError('Invalid value for option "' . $option . '".');
                    }
                    break;
                case 'debug':
                    if (!is_bool($value)) {
                        return PEAR::raiseError('Invalid value for option "' . $option . '".');
                    }
                    break;
            }

            $this->_options[$option] = $value;
        }
        return true;
    }

    // }}}
    // {{{ getOptions()

    /**
     * Get current options.
     *
     * @return array An associative array of options.
     * @access public
     */
    function getOptions()
    {
        return $this->_options;
    }

    // }}}
    // {{{ set()

    /**
     * Set a pingback data.
     *
     * @param string $key Data key name.
     * @param string $value Data value.
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error on failure.
     * @access public
     */
    function set($key, $value)
    {
        if (!isset($this->_data[$key])) {
            return PEAR::raiseError('Unknown data key "' . $key . '".');
        }

        $this->_data[$key] = $value;
        return true;
    }

    // }}}
    // {{{ get()

    /**
     * Get a pingback data.
     *
     * @param string $key Data key name.
     *
     * @return mixed Data value or NULL if data not found.
     * @access public
     */
    function get($key)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return null;
    }

    // }}}
    // {{{ setFromArray()

    /**
     * Set the pingback data from an associative array.
     *
     * @param array $data An associative array of data.
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error on failure.
     * @access public
     * @see set()
     */
    function setFromArray($data)
    {
        if (!is_array($data)) {
            return PEAR::raiseError('Type mistmatch, only array accepted.');
        }

        foreach ($data as $key => $value) {
            $res = $this->set($key, $value);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        return true;
    }

    // }}}
    // {{{ setRegisteredSources()

    /**
     * Set registered pingback source URIs.
     *
     * This method will override current registered sources data. For avoid it
     * use addRegisteredSource().
     *
     * @param array $data An array contains sources.
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error on failure.
     * @access public
     * @see addRegisteredSource()
     * @see isPingbackRegistered()
     */
    function setRegisteredSources($data)
    {
        if (!is_array($data)) {
            return PEAR::raiseError('Type mistmatch, only array accepted.');
        }

        $this->_registeredSources = $data;
        return true;
    }

    // }}}
    // {{{ addRegisteredSource()

    /**
     * Add registered pingback source(s).
     *
     * @param mixed $data A string URI or An array contains URIs.
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error on failure.
     * @access public
     * @see setRegisteredSources()
     * @see isPingbackRegistered()
     */
    function addRegisteredSource($data)
    {
        if (is_array($data)) {
            foreach ($data as $value) {
                $this->addRegisteredSource($value);
            }
        } else if (is_string($data) && !empty($data)) {
            $this->_registeredSources[] = $data;
        } else {
            return PEAR::raiseError('Invalid value');
        }
        return true;
    }

    // }}}
    // {{{ getRegisteredSources()

    /**
     * Get registered pingback source URIs.
     *
     * @return array List of registered source URIs.
     * @access public
     */
    function getRegisteredSources()
    {
        return $this->_registeredSources();
    }

    // }}}
    // {{{ autodiscover()

    /**
     * Implemented of Pingback autodiscovery algorithm.
     *
     * This method send a request to given URI, then see if the header contain
     * Pingback header, if not attempt to find a Pingback link on the body contents.
     * This algorithm is one of requirement of the Pingback clients specification.
     *
     * @param string $uri URI to discover.
     *
     * @return string|PEAR_Error A Pingback URI on success or PEAR_Error on failure.
     * @access public
     * @see sendHTTPRequest()
     * @see expandEntities()
     */
    function autodiscover($uri)
    {
        $res = $this->sendHTTPRequest($uri);
        if (PEAR::isError($res)) {
            return $res;
        }

        $pingbackURI = '';
        if (isset($res['header']['x-pingback']) && !empty($res['header']['x-pingback'])) {
            $pingbackURI = $res['header']['x-pingback'];
        } else if (preg_match('@\<link rel="pingback" href="([^"]+)" ?\/?\>@i',
                            $res['body'], $matches)) {
            $pingbackURI = $matches[1];
        }

        if (!empty($pingbackURI)) {
            $pingbackURI = $this->expandEntities($pingbackURI);
        }

        return $pingbackURI;
    }

    // }}}
    // {{{ send()

    /**
     * Send a pingback.
     *
     * @param array $data (optional) An array of pingback data.
     *
     * @access public
     * @see autodiscover()
     */
    function send($data = null)
    {
        if ($data !== null) {
            $res = $this->setFromArray($data);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        // Find the whether if source and target is equal or not.
        if (strstr($this->_data['sourceURI'], $this->_data['targetURI'])) {
            return PEAR::raiseError('Target URI is equal with source URI');
        }

        // pingback URI set
        if (empty($this->_data['pingbackURI'])) {
            $res = Services_Pingback::autodiscover($this->_data['targetURI']);
            if (PEAR::isError($res)) {
                return $res;
            } else if (empty($res)) {
                return PEAR::raiseError('Target URI is not a pingback-enabled resource');
            }

            $this->_data['pingbackURI'] = $res;
        }

        // Prepare an XML-RPC Message.
        $eArgs = array(
            XML_RPC_encode($this->_data['sourceURI']),
            XML_RPC_encode($this->_data['targetURI'])
        );
        $msg = new XML_RPC_Message('pingback.ping', $eArgs);

        // Prepare full path of URI to conforms XML_RPC_Client parameter.
        $url = new Net_URL($this->_data['pingbackURI']);
        $path = $url->path;
        $querystring = $url->getQueryString();
        if (!empty($querystring)) {
            $path .= '?' . $querystring;
        }

        $cli = new XML_RPC_Client($path, $url->protocol . '://' . $url->host, $url->port);
        $cli->setDebug((int) $this->_options['debug']);

        // save the current error handling in buffer for restore.
        $default_error_mode = $GLOBALS['_PEAR_default_error_mode'];
        $default_error_options = $GLOBALS['_PEAR_default_error_options'];

        // Set error mode to callback, since XML_RPC doesn't return error object on failure.
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'XML_RPC_ErrorCallback'));

        $res = $cli->send($msg, (int) $this->_options['timeout']);

        // Cacth the error if any.
        if ($this->_isXML_RPC_Error()) {
            return $this->_XML_RPC_Error;
        }

        $val = $res->value();
        if (!is_object($val) || !is_a($val, 'XML_RPC_value')) {
            return PEAR::raiseError('Response Error: ' . $res->faultString());
        }

        // restore the current error handling.
        PEAR::setErrorHandling($default_error_mode, $default_error_options);

        return XML_RPC_decode($val);
    }

    // }}}
    // {{{ receive()

    /**
     * Receive pingback call.
     *
     * @access public
     */
    function receive()
    {
        $GLOBALS['_Services_Pingback_server'] =& $this;
        new XML_RPC_Server($this->_dispatches, 1,
                           $this->_options['debug']);

    }

    // }}}
    // {{{ errorMessage()

    /**
     * Get error message.
     *
     * @param int $value Error code.
     *
     * @return string Error message.
     * @access public
     */
    function errorMessage($value)
    {
        $errorMessages = array(
            SERVICES_PINGBACK_ERROR                     => 'Unknown error',
            SERVICES_PINGBACK_ERROR_SOURCE_NOT_EXISTS   => 'Source URI doesn\'t exist',
            SERVICES_PINGBACK_ERROR_NO_TARGET_LINK      => 'Source URI doesn\'t contain a link to the target URI',
            SERVICES_PINGBACK_ERROR_TARGET_NOT_EXISTS   => 'Target URI doesn\'t exist',
            SERVICES_PINGBACK_ERROR_TARGET_NOT_RESOURCE => 'Target URI is not a pingback-enabled resource',
            SERVICES_PINGBACK_ERROR_ALREADY_REGISTERED  => 'Pingback has already been registered',
            SERVICES_PINGBACK_ERROR_ACCESS_DENIED       => 'Access denied',
            SERVICES_PINGBACK_ERROR_UPSTREAM_ERROR      => 'Upstream server error'
        );

        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[SERVICES_PINGBACK_ERROR];
    }

    // }}}
    // {{{ sendHTTPRequest()

    /**
     * Send a HTTP 1/1 request.
     *
     * @param string $uri URI.
     *
     * @return array|PEAR_Error An associative array of result containing keys
     *                          'code', 'header' and 'body' on success or
     *                          PEAR_Error on failure.
     * @access public
     */
    function sendHTTPRequest($uri)
    {
        $params = array(
            'timeout'           => $this->_options['timeout'],
            'allowRedirects'    => $this->_options['allowRedirects'],
            'maxRedirects'      => $this->_options['maxRedirects']
        );

        $req = new HTTP_Request($uri, $params);

        $req->sendRequest();
        if (PEAR::isError($req)) {
            return $req;
        }

        if ($req->getResponseCode() != 200) {
            return PEAR::raiseError('Host returned error: ' . $req->getResponseCode());
        }

        return array(
            'code'      => $req->getResponseCode(),
            'header'    => $req->getResponseHeader(),
            'body'      => $req->getResponseBody()
        );
    }

    // }}}
    // {{{ expandEntities()

    /**
     * Expand the entities.
     *
     * There are four allowed entities (&amp; for &, &lt; for <, &gt; for > and
     * &quot; for ").
     *
     * @param string $str A string to expand, normally is a uri.
     *
     * @return string A result.
     * @access public
     */
    function expandEntities($str)
    {
        return strtr($str, $this->_transEntities);
    }

    // }}}
    // {{{ sendPingbackHeader()

    /**
     * Send a Pingback header to tell this is a pingback-enable resource.
     *
     * @param string $pingbackURI (optional) Pingback URI, if not given URI is
     *                                       the current URI.
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error on failure.
     * @access public
     * @static
     */
    function sendPingbackHeader($pingbackURI = null)
    {
        if (headers_sent()) {
            return PEAR::raiseError('Header already sent, cannot sent Pingback header');
        }

        if ($pingbackURI === null) {
            $url = new Net_URL($url);
            $pingbackURI = $url->getURL();
        }

        header('X-Pingback: ' . $pingbackURI);
        return true;
    }

    // }}}
    // {{{ isURIExist()

    /**
     * Find out the whether the URI is exist, done with fopen().
     *
     * @param string $uri URI to verify.
     *
     * @return bool TRUE if exist, otherwise FALSE.
     * @access public
     * @static
     * @author Pablo Fischer <pfischer@php.net>
     */
    function isURIExist($uri)
    {
        $params = array(
            'timeout'           => 3,
            'allowRedirects'    => true,
            'maxRedirects'      => 2,
        );

        $req = new HTTP_Request($uri, $params);

        $req->sendRequest();
        if (PEAR::isError($req)) {
            return false;
        }

        if ($req->getResponseCode() == 200) {
            return true;
        }

        return false;
    }

    // }}}
    // {{{ isSourceLinked()

    /**
     * Find the a link to URI on the contents body of given URI.
     *
     * @param string $uri URI to scans.
     * @param string $linkURI URI to be found on the link.
     *
     * @return bool TRUE if a link found, otherwise FALSE.
     * @access public
     * @see sendHTTPRequest()
     */
    function isSourceLinked()
    {
        $res = $this->sendHTTPRequest($this->_data['sourceURI']);
        if (PEAR::isError($res)) {
            return $res;
        }

        if (preg_match('@\<a.+href="'. preg_quote($this->_data['targetURI']) . '".*\>.+\<\/a\>@',
                       $res['body'])) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ isPingbackRegistered()

    /**
     * Find out the whether if pingback from URI has already registered.
     *
     * @param string $sourceURI Source URI.
     *
     * @return bool TRUE if has already registered, otherwise FALSE.
     * @access public
     */
    function isPingbackRegistered()
    {
        if (in_array($this->_data['sourceURI'], $this->_registeredSources)) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ isPingbackEnabled()

    /**
     * Find the whether a resouce is a pingback-enabled or not.
     *
     * @param string $uri URI to scans.
     *
     * @return bool|PEAR_Error TRUE if it's pingback-enabled, otherwise FALSE or
     *                         PEAR_Error on failure.
     * @access public
     */
    function isTargetPingbackEnabled()
    {
        $res = $this->autodiscover($this->_data['targetURI']);
        if (PEAR::isError($res)) {
            return $res;
        }

        if (!empty($res)) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ fetchSourceContext()

    /**
     * Fetch page context for current call.
     *
     * @return array|PEAR_Error An array of context on success or PEAR_Error on failure.
     * @access public
     */
    function fetchSourceContext()
    {
        $res = $this->sendHTTPRequest($this->_data['sourceURI']);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->_sourceContext= array(
            'title'     => 'Untitled',
            'content'   => '',
            'url'       => $this->_data['sourceURI']
        );

        $str = $res['body'];

        // Extract the page title first.
        if (preg_match('@<title>([^<]*?)</title>@is', $str, $matches)) {
            $this->_sourceContext['title'] = $matches[1];
        }

        // Extract the page body.
        if (preg_match('@<body[^>]*>(.*)<\/body>@is', $str, $matches)) {
            $str = $matches[1];
        }

        // Remove unecessary elements.
        $removeElems = array(
            'style',
            'script',
            'meta',
        );

        foreach ($removeElems as $elem) {
            $str = preg_replace('@<' . $elem . '[^>]*\/>@is', '', $str);
            $str = preg_replace('@<' . $elem . '[^>]*>.*<\/' . $elem . '>@is', '', $str);
        }

        // Strips all tags except <a>.
        $str = strip_tags($str, '<a>');

        // Strips all links except target URI.
        if (preg_match_all('@<a.+href="(.+)".*>.+?<\/a>@i', $str, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $res) {
                if (!strstr($res[1], $this->_data['targetURI'])) {
                    $str = str_replace($res[0], $res[1], $str);
                }
            }
        }

        // Extract the context, from exact target link position.
        if (preg_match('@<a.+href="'. preg_quote($this->_data['targetURI']) . '".*>.+<\/a>@i', $str, $matches)) {
            $linkText = $matches[0];
            $linkPos = strpos($str, $linkText);
            $linkLen = strlen($linkText);
            $sideSize = (int)(($this->_options['fetchsize'] - $linkLen) / 2);

            if ($sideSize > $linkPos) {
                $leftText = '';
                $leftStart = 0;
            } else {
                $leftText = '... ';
                $leftStart = $linkPos - ($sideSize - 4);
            }

            $leftText .= substr($str, $leftStart, $linkPos - $leftStart);
            $rightText = substr($str, $linkPos + $linkLen);

            if (strlen($rightText) > $sideSize) {
                $rightText = substr($rightText, 0, $sideSize - 4) . ' ...';
            }

            $this->_sourceContext['content'] = trim($leftText . $linkText . $rightText);
        }

        return true;
    }

    // }}}
    // {{{ getSourceContext()

    /**
     * Get fetched source context.
     *
     * @return string Fetched source context.
     * @access public
     */
    function getSourceContext()
    {
        return $this->_sourceContext;
    }

    // }}}
    // {{{ ping()

    /**
     * A callback of Pingback XML-RPC pingback.ping method.
     *
     * @param object $msg An instance of XML_RPC_Message object.
     *
     * @return XML_RPC_Response A result of current call.
     * @access public
     * @static
     * @see isURIExist()
     * @see isSourceLinked()
     * @see isTargetPingbackEnabled()
     * @see isPingbackRegistered()
     * @see fetchSourceContext()
     * @see getSourceContext()
     */
    function ping($msg)
    {
        $Services_Pingback_server =& $GLOBALS['_Services_Pingback_server'];

        $sourceURI = $msg->params[0]->getval();
        $targetURI = $msg->params[1]->getval();

        $Services_Pingback_server->set('sourceURI', $sourceURI);
        $Services_Pingback_server->set('targetURI', $targetURI);

        if (!Services_Pingback::isURIExist($sourceURI)) {
            return new XML_RPC_Response(0,
                                        SERVICES_PINGBACK_ERROR_SOURCE_NOT_EXISTS,
                                        Services_Pingback::errorMessage(
                                            SERVICES_PINGBACK_ERROR_SOURCE_NOT_EXISTS
                                        )
            );
        }

        if (!$Services_Pingback_server->isSourceLinked()) {
            return new XML_RPC_Response(0,
                                        SERVICES_PINGBACK_ERROR_NO_TARGET_LINK,
                                        Services_Pingback::errorMessage(
                                            SERVICES_PINGBACK_ERROR_NO_TARGET_LINK
                                        )
            );
        }

        if (!Services_Pingback::isURIExist($targetURI)) {
            return new XML_RPC_Response(0,
                                        SERVICES_PINGBACK_ERROR_TARGET_NOT_EXISTS,
                                        Services_Pingback::errorMessage(
                                            SERVICES_PINGBACK_ERROR_TARGET_NOT_EXISTS
                                        )
            );
        }

        if (!$Services_Pingback_server->isTargetPingbackEnabled()) {
            return new XML_RPC_Response(0,
                                        SERVICES_PINGBACK_ERROR_TARGET_NOT_RESOURCE,
                                        Services_Pingback::errorMessage(
                                            SERVICES_PINGBACK_ERROR_TARGET_NOT_RESOURCE
                                        )
            );
        }

        if ($Services_Pingback_server->isPingbackRegistered()) {
            return new XML_RPC_Response(0,
                                        SERVICES_PINGBACK_ERROR_ALREADY_REGISTERED,
                                        Services_Pingback::errorMessage(
                                            SERVICES_PINGBACK_ERROR_ALREADY_REGISTERED
                                        )
            );
        }

        $Services_Pingback_server->fetchSourceContext();

        return new XML_RPC_Response(new XML_RPC_Value('Pingback from ' . $sourceURI . ' success'));
    }

    // }}}
    // {{{ XML_RPC_ErrorCallback()

    /**
     * A callback to grabs the XML_RPC raised error, assign to PEAR error mode
     * PEAR_ERROR_CALLBACK.
     *
     * @param object $error PEAR_Error object.
     *
     * @access public
     * @internal
     */
    function XML_RPC_ErrorCallback($error)
    {
        $this->_XML_RPC_Error = $error;
    }

    // }}}
    // {{{ isXML_PRC_Error()

    /**
     * Find the whether if XML_RPC have an error or not.
     *
     * @return bool
     * @access private
     */
    function _isXML_RPC_Error()
    {
        if (PEAR::isError($this->_XML_RPC_Error)) {
            return true;
        }
        return false;
    }

    // }}}
}

// }}}

/*
 * Local variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>