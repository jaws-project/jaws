<?php
require_once PEAR_PATH. 'HTTP/Request2.php';
/**
 * Class that deals like a wrapper between Jaws and pear/HTTP_Request
 *
 * @category    Application
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_HTTPRequest
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * @access  private
     * @var int $expires    Cache expires time(second)
     */
    private $expires = 0;

    /**
     * @access  private
     * @var int $refresh    Refresh/Update cache
     */
    private $refresh = false;

    /**
     * @access  private
     * @var int $request_cache_key  Cache key
     */
    private $request_cache_key = 0;

    /**
     * @access  private
     * @var array   $options    The request options
     */
    var $options = array();

    /**
     * @access  public
     * @const   string   JAWS_USER_AGENT   Jaws User Agent
     */
    const JAWS_USER_AGENT = 'Jaws HTTPRequest (http://jaws-project.com)';

    /**
     * @access  public
     * @var     integer $default_error_level    Default error logging level
     */
    var $default_error_level = JAWS_ERROR_ERROR;

    /**
     * @access  private
     * @var     object  $httpRequest    instance of PEAR HTTP_Request
     */
    var $httpRequest;

    /**
     * Constructor
     *
     * @access  protected
     * @param   array   $options    Associated request options
     * @return  void
     */
    function __construct($options = array())
    {
        $this->app = Jaws::getInstance();

        $this->options['timeout'] = (int)$this->app->registry->fetch('connection_timeout', 'Settings');
        if ($this->app->registry->fetch('proxy_enabled', 'Settings') == 'true') {
            $this->options['proxy_type'] = 'http';
            $this->options['proxy_auth_scheme'] = HTTP_Request2::AUTH_BASIC;
            if ($this->app->registry->fetch('proxy_auth', 'Settings') == 'true') {
                $this->options['proxy_user'] = $this->app->registry->fetch('proxy_user', 'Settings');
                $this->options['proxy_password'] = $this->app->registry->fetch('proxy_pass', 'Settings');
            }
            $this->options['proxy_host'] = $this->app->registry->fetch('proxy_host', 'Settings');
            $this->options['proxy_port'] = $this->app->registry->fetch('proxy_port', 'Settings');
        }

        // merge default and passed options
        $this->options = array_merge($this->options, $options);
        $this->httpRequest = new HTTP_Request2();
    }

    /**
     * Sets request header(s)
     *
     * @access  public
     * @param   string  $name       header name
     * @param   string  $value      value of header key, header will be removed if value is null
     * @param   bool    $replace    whether to replace previous header
     * @return  mixed   Jaws_HTTPRequest object
     */
    function setHeader($name, $value = null, $replace = true)
    {
        $this->httpRequest->setHeader($name, $value, $replace);
        return $this;
    }

    /**
     * Gets the URL content
     *
     * @access  public
     * @param   string  $url        URL address
     * @return  mixed   Response(status/header/body) on success, otherwise Jaws_Error
     */
    function get($url)
    {
        $this->request_cache_key = Jaws_Cache::key($url);
        if ($this->refresh ||
            false === $result = $this->app->cache->get($this->request_cache_key, true)
        ) {
            $headers = $this->httpRequest->getHeaders();
            // user agent
            if (!array_key_exists('user-agent', $headers)) {
                $this->httpRequest->setHeader('User-Agent', self::JAWS_USER_AGENT);
            }

            $this->httpRequest->setConfig($this->options)->setUrl($url);
            $this->httpRequest->setMethod(HTTP_Request2::METHOD_GET);
            try {
                $result = $this->httpRequest->send();
                $this->app->cache->set(
                    $this->request_cache_key,
                    $result = array(
                        'status' => $result->getStatus(),
                        'header' => $result->getHeader(),
                        'body'   => $result->getBody()
                    ),
                    true,
                    $this->expires
                );
            } catch (Exception $error) {
                return Jaws_Error::raiseError(
                    $error->getMessage(),
                    $error->getCode(),
                    $this->default_error_level,
                    1
                );
            }
        }

        return $result;
    }

    /**
     * Posts data to the URL
     *
     * @access  public
     * @param   string  $url    URL address
     * @param   array   $params Associated name/data values
     * @return  mixed   Response(status/header/body) on success, otherwise Jaws_Error
     */
    function post($url, $params = array())
    {
        $this->request_cache_key = Jaws_Cache::key($url, $params);
        if ($this->refresh ||
            false === $result = $this->app->cache->get($this->request_cache_key, true)
        ) {
            $headers = $this->httpRequest->getHeaders();
            // detect data need url-encoding
            if (!array_key_exists('content-type', $headers)) {
                $this->httpRequest->setHeader('content-type', 'application/x-www-form-urlencoded');
            }

            // user agent
            if (!array_key_exists('user-agent', $headers)) {
                $this->httpRequest->setHeader('User-Agent', self::JAWS_USER_AGENT);
            }

            $this->httpRequest->setConfig($this->options)->setUrl($url);
            $this->httpRequest->setMethod(HTTP_Request2::METHOD_POST);

            // add post data
            foreach($params as $name => $value) {
                $this->httpRequest->addPostParameter($name, $value);
            }

            try {
                $result = $this->httpRequest->send();
                $this->app->cache->set(
                    $this->request_cache_key,
                    $result = array(
                        'status' => $result->getStatus(),
                        'header' => $result->getHeader(),
                        'body'   => $result->getBody()
                    ),
                    true,
                    $this->expires
                );
            } catch (Exception $error) {
                return Jaws_Error::raiseError(
                    $error->getMessage(),
                    $error->getCode(),
                    $this->default_error_level,
                    1
                );
            }
        }

        return $result;
    }

    /**
     * Raw posts data to the URL
     *
     * @access  public
     * @param   string  $url    URL address
     * @param   string  $data   Raw data
     * @return  mixed   Response(status/header/body) on success, otherwise Jaws_Error
     */
    function rawPostData($url, $data = '')
    {
        $this->request_cache_key = Jaws_Cache::key($url, $data);
        if ($this->refresh ||
            false === $result = $this->app->cache->get($this->request_cache_key, true)
        ) {
            $headers = $this->httpRequest->getHeaders();
            // user agent
            if (!array_key_exists('user-agent', $headers)) {
                $this->httpRequest->setHeader('User-Agent', self::JAWS_USER_AGENT);
            }

            $this->httpRequest->setConfig($this->options)->setUrl($url);
            $this->httpRequest->setMethod(HTTP_Request2::METHOD_POST);
            // set post data
            $this->httpRequest->setBody($data);
            try {
                $result = $this->httpRequest->send();
                $this->app->cache->set(
                    $this->request_cache_key,
                    $result = array(
                        'status' => $result->getStatus(),
                        'header' => $result->getHeader(),
                        'body'   => $result->getBody()
                    ),
                    true,
                    $this->expires
                );
            } catch (Exception $error) {
                return Jaws_Error::raiseError(
                    $error->getMessage(),
                    $error->getCode(),
                    $this->default_error_level,
                    1
                );
            }
        }

        return $result;
    }

    /**
     * Delete HTTP method
     *
     * @access  public
     * @param   string  $url        URL address
     * @return  mixed   Response code on success, otherwise Jaws_Error
     */
    function delete($url)
    {
        $headers = $this->httpRequest->getHeaders();
        // user agent
        if (!array_key_exists('user-agent', $headers)) {
            $this->httpRequest->setHeader('User-Agent', self::JAWS_USER_AGENT);
        }

        $this->httpRequest->setConfig($this->options)->setUrl($url);
        $this->httpRequest->setMethod(HTTP_Request2::METHOD_DELETE);
        try {
            $result = $this->httpRequest->send();
            return array(
                'status' => $result->getStatus(),
                'header' => $result->getHeader(),
                'body'   => $result->getBody()
            );
        } catch (Exception $error) {
            return Jaws_Error::raiseError(
                $error->getMessage(),
                $error->getCode(),
                $this->default_error_level,
                1
            );
        }
    }

    /**
     * Set cache options
     *
     * @access  private
     * @param   int     $expires    Cache expires time(second)
     * @param   bool    $refresh    Refresh/Update cache
     * @return  void
     */
    function setCacheOptions($expires = 0, $refresh = false)
    {
        $this->expires = $expires;
        $this->refresh = $refresh;
    }

    /**
     * Delete cache
     *
     * @access  public
     * @return  mixed
     */
    function deleteCache()
    {
        return $this->app->cache->delete($this->request_cache_key);
    }

}