<?php
/**
 * Session ID name
 */
define('JAWS_SESSION_NAME', 'JAWSSESSID');

/**
 * Class to manage the session when user is running a web application
 *
 * @category   Session
 * @package    Core
 * @author     Ivan -sk8- Chavero <imcsk8@gluch.org.mx>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Session_Web extends Jaws_Session
{
    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function Jaws_Session_Web()
    {
        parent::Init();
    }

    /**
     * Initializes the Session
     *
     * @access  public
     * @return  void
     */
    function init()
    {
        $session = $this->GetCookie(JAWS_SESSION_NAME);
        if (empty($session) || !$this->Load($session)) {
            $this->_SessionExists = false;
            $this->Create();
        }
    }

    /**
     * @see Jaws_Session::Create
     *
     * @access  public
     * @param   array   $info       User attributes
     * @param   bool    $remember   Remember me
     * @return  void
     */
    function Create($info = array(), $remember = false)
    {
        parent::Create($info, $remember);
        // Create cookie
        $this->SetCookie(JAWS_SESSION_NAME,
                         $this->_SessionID.'-'.$this->GetAttribute('salt'),
                         $remember? 60*(int)$GLOBALS['app']->Registry->Get('session_remember_timeout', 'Policy', JAWS_COMPONENT_GADGET) : 0,
                         false);
    }

    /**
     * Logout from session
     *
     * @access  public
     * @return  void
     * @see Jaws_Session::Logout
     */
    function Logout()
    {
        parent::Logout();
        $this->SetCookie(JAWS_SESSION_NAME,
                         $this->_SessionID.'-'.$this->GetAttribute('salt'),
                         0,
                         false);
    }

    /**
     * Create a new cookie on client
     *
     * @access  public
     * @param   string  $name       Cookie name
     * @param   string  $value      Cookie value
     * @param   int     $minutes    The time the cookie expires
     * @param   bool    $httponly   If TRUE the cookie will be made accessible only through the HTTP protocol
     * @return  void
     */
    function SetCookie($name, $value, $minutes = 0, $httponly = false)
    {
        $version = $GLOBALS['app']->Registry->Get('cookie_version', 'Settings', JAWS_COMPONENT_GADGET);
        $expires = ($minutes == 0)? 0 : (time() + $minutes*60);
        $path    = $GLOBALS['app']->getSiteURL('/', true);
        $domain  = '';//$GLOBALS['app']->Registry->Get('cookie_domain', 'Settings', JAWS_COMPONENT_GADGET);
        $secure  = ($GLOBALS['app']->Registry->Get('cookie_secure', 'Settings', JAWS_COMPONENT_GADGET) == 'false') ? false : true;
        $domain .= $httponly? '; HttpOnly' : '';
        setcookie($name, $value, $expires, $path, $domain);
    }

    /**
     * Get a cookie
     *
     * @access  public
     * @param   string  $name   Cookie name
     * @return  string
     */
    function GetCookie($name)
    {
        $version = $GLOBALS['app']->Registry->Get('cookie_version', 'Settings', JAWS_COMPONENT_GADGET);
        $request =& Jaws_Request::getInstance();
        return $request->get($name, 'cookie');
    }

    /**
     * Destroy a cookie
     *
     * @access  public
     * @param   string  $name   Cookie name
     * @return  void
     */
    function DestroyCookie($name)
    {
        $this->SetCookie($name, false);
    }

    /**
     * Check permission on a given gadget/task
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   string  $task           Task(s) name
     * @param   bool    $together       And/Or tasks permission result, default true
     * @param   string  $errorMessage   Error message to return
     * @return  mixed   True if granted, else throws an Exception(Jaws_Error::Fatal)
     */
    function CheckPermission($gadget, $task, $together = true, $errorMessage = '')
    {
        if ($this->GetPermission($gadget, $task, $together)) {
            return true;
        }

        $GLOBALS['app']->InstanceLayout();
        $GLOBALS['app']->Layout->LoadControlPanelHead();
        $user = $GLOBALS['app']->LoadGadget('Users', 'HTML');
        echo $user->ShowNoPermission($this->GetAttribute('username'), $gadget, $task);
        exit;
    }

}