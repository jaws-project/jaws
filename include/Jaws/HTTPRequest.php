<?php
require_once PEAR_PATH. 'HTTP/Request.php';
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
    var $user_agent = 'Jaws HTTPRequest class (http://jaws-project.com)';

    /**
     * @access  public
     * @var     integer $default_error_level    Default error logging level
     */
    var $default_error_level = JAWS_ERROR_ERROR;

    /**
     * Constructor
     *
     * @access  protected
     * @param   array   $options    Associated request options
     * @return  void
     */
    function Jaws_HTTPRequest($options = array())
    {
        $this->options['timeout'] = (int)$GLOBALS['app']->Registry->fetch('connection_timeout', 'Settings');
        if ($GLOBALS['app']->Registry->fetch('proxy_enabled', 'Settings') == 'true') {
            if ($GLOBALS['app']->Registry->fetch('proxy_auth', 'Settings') == 'true') {
                $this->options['proxy_user'] = $GLOBALS['app']->Registry->fetch('proxy_user', 'Settings');
                $this->options['proxy_pass'] = $GLOBALS['app']->Registry->fetch('proxy_pass', 'Settings');
            }
            $this->options['proxy_host'] = $GLOBALS['app']->Registry->fetch('proxy_host', 'Settings');
            $this->options['proxy_port'] = $GLOBALS['app']->Registry->fetch('proxy_port', 'Settings');
        }

        // merge default and passed options
        $this->options = array_merge($this->options, $options);
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
        $httpRequest = new HTTP_Request($url, $this->options);
        $httpRequest->addHeader('User-Agent', $this->user_agent);
        $httpRequest->setMethod(HTTP_REQUEST_METHOD_GET);
        $result = $httpRequest->sendRequest();
        if (PEAR::isError($result)) {
            return Jaws_Error::raiseError(
                $result->getMessage(),
                $result->getCode(),
                $this->default_error_level,
                1
            );
        }

        $response = $httpRequest->getResponseBody();
        return $httpRequest->getResponseCode();
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
        $httpRequest = new HTTP_Request($url, $this->options);
        $httpRequest->addHeader('User-Agent', $this->user_agent);
        $httpRequest->setMethod(HTTP_REQUEST_METHOD_POST);
        // add post data
        foreach($params as $key => $data) {
            $httpRequest->addPostData($key, urlencode($data));
        }

        $result = $httpRequest->sendRequest();
        if (PEAR::isError($result)) {
            return Jaws_Error::raiseError(
                $result->getMessage(),
                $result->getCode(),
                $this->default_error_level,
                1
            );
        }

        $response = $httpRequest->getResponseBody();
        return $httpRequest->getResponseCode();
    }

}