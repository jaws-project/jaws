<?php
/**
 * Class that deals like a wrapper between Jaws and pear/HTTP_Request
 *
 * @category   Application
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_HTTPRequest
{
    /**
     * @access  private
     * @var array   $options    The request options
     */
    var $options = array();

    /**
     * Constructor
     *
     * @access  protected
     * @param   array   $options Assocated Request options
     * @return  object  instance of Jaws_HTTPRequest
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

}