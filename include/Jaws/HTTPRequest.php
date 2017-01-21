<?php
require_once PEAR_PATH. 'HTTP/Request2.php';
/**
 * Class that deals like a wrapper between Jaws and pear/HTTP_Request
 *
 * @category    Application
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_HTTPRequest
{
    /**
     * @access  private
     * @var array   $options    The request options
     */
    var $options = array();

    /**
     * @access  public
     * @var     string   $user_agent    User Agent
     */
    var $user_agent = 'Jaws HTTPRequest (http://jaws-project.com)';

    /**
     * @access  public
     * @var     string   $content_type  Request content-type
     */
    var $content_type = '';

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
        $this->options['timeout'] = (int)$GLOBALS['app']->Registry->fetch('connection_timeout', 'Settings');
        if ($GLOBALS['app']->Registry->fetch('proxy_enabled', 'Settings') == 'true') {
            $this->options['proxy_type'] = 'http';
            $this->options['proxy_auth_scheme'] = HTTP_Request2::AUTH_BASIC;
            if ($GLOBALS['app']->Registry->fetch('proxy_auth', 'Settings') == 'true') {
                $this->options['proxy_user'] = $GLOBALS['app']->Registry->fetch('proxy_user', 'Settings');
                $this->options['proxy_password'] = $GLOBALS['app']->Registry->fetch('proxy_pass', 'Settings');
            }
            $this->options['proxy_host'] = $GLOBALS['app']->Registry->fetch('proxy_host', 'Settings');
            $this->options['proxy_port'] = $GLOBALS['app']->Registry->fetch('proxy_port', 'Settings');
        }

        // merge default and passed options
        $this->options = array_merge($this->options, $options);
        $this->httpRequest = new HTTP_Request2();
    }

    /**
     * Gets the URL content
     *
     * @access  public
     * @param   string  $url        URL address
     * @param   string  $response   Response body
     * @return  mixed   Response code on success, otherwise Jaws_Error
     */
    function get($url, &$response)
    {
        $this->httpRequest->setConfig($this->options)->setUrl($url);
        $this->httpRequest->setHeader('User-Agent', $this->user_agent);
        $this->httpRequest->setMethod(HTTP_Request2::METHOD_GET);
        try {
            $result = $this->httpRequest->send();
            $response = $result->getBody();
            return $result->getStatus();
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
     * Posts data to the URL
     *
     * @access  public
     * @param   string  $url        URL address
     * @param   array   $params     Associated name/data values
     * @param   string  $response   Response body
     * @return  mixed   Response code on success, otherwise Jaws_Error
     */
    function post($url, $params = array(), &$response)
    {
        $this->httpRequest->setConfig($this->options)->setUrl($url);
        $this->httpRequest->setHeader('User-Agent', $this->user_agent);
        $this->httpRequest->setHeader('Content-Type', $this->content_type);
        $this->httpRequest->setMethod(HTTP_Request2::METHOD_POST);
        // add post data
        foreach($params as $key => $data) {
            $this->httpRequest->addPostParameter($key, urlencode($data));
        }

        try {
            $result = $this->httpRequest->send();
            $response = $result->getBody();
            return $result->getStatus();
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
     * Raw posts data to the URL
     *
     * @access  public
     * @param   string  $url        URL address
     * @param   string  $data       Raw data
     * @param   string  $response   Response body
     * @return  mixed   Response code on success, otherwise Jaws_Error
     */
    function rawPostData($url, $data = '', &$response)
    {
        $this->httpRequest->setConfig($this->options)->setUrl($url);
        $this->httpRequest->setHeader('User-Agent', $this->user_agent);
        $this->httpRequest->setHeader('Content-Type', $this->content_type);
        $this->httpRequest->setMethod(HTTP_Request2::METHOD_POST);
        // set post data
        $this->httpRequest->setBody($data);
        try {
            $result = $this->httpRequest->send();
            $response = $result->getBody();
            return $result->getStatus();
        } catch (Exception $error) {
            return Jaws_Error::raiseError(
                $error->getMessage(),
                $error->getCode(),
                $this->default_error_level,
                1
            );
        }
    }

}