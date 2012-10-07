<?php
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
define('JAWS_SESSION_NAME', 'JAWSSESSID');

class Jaws_Session_Web extends Jaws_Session
{

    function Jaws_Session_Web()
    {
        parent::Init();
    }

    /**
     * Initializes the Session
     *
     * @access  public
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
     * @param   array  $info      User attributes
     * @param   boolean $remember Remember me
     * @return  boolean True if can create session.
     */
    function Create($info = array(), $remember = false)
    {
        parent::Create($info, $remember);
        // Create cookie
        $this->SetCookie(JAWS_SESSION_NAME,
                         $this->_SessionID.'-'.$this->GetAttribute('salt'),
                         $remember? 60*(int)$GLOBALS['app']->Registry->Get('/policy/session_remember_timeout') : 0,
                         false);
    }

    /**
     * @see Jaws_Session::Logout
     *
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
     * @param   string $name Cookie name
     * @param   string $value Cookie value
     * @param   string $expiration Cookie expiration minutes
     */
    function SetCookie($name, $value, $minutes = 0, $httponly = false)
    {
        $version = $GLOBALS['app']->Registry->Get('/config/cookie/version');
        $expires = ($minutes == 0)? 0 : (time() + $minutes*60);
        $path    = $GLOBALS['app']->getSiteURL('/', true);
        $domain  = '';//$GLOBALS['app']->Registry->Get('/config/cookie/domain');
        $secure  = ($GLOBALS['app']->Registry->Get('/config/cookie/secure') == 'false') ? false : true;
        $domain .= $httponly? '; HttpOnly' : '';
        setcookie($name, $value, $expires, $path, $domain);
    }

    /**
     * Get a cookie
     * @param   string $name Cookie name
     */
    function GetCookie($name)
    {
        $version = $GLOBALS['app']->Registry->Get('/config/cookie/version');
        $request =& Jaws_Request::getInstance();
        return $request->get($name, 'cookie');
    }

    /**
     * Destroy a cookie
     * @param   string $name Cookie name
     */
    function DestroyCookie($name)
    {
        $this->SetCookie($name, false);
    }

    /**
     * Check permission on a given gadget/task
     *
     * @param   string $gadget Gadget name
     * @param   string $task Task name
     * @param   string $errorMessage Error message to return
     * @return  boolean True if granted, else print HTML output telling the user he doesn't have permission
     */
    function CheckPermission($gadget, $task, $errorMessage = '')
    {
        if ($this->GetPermission($gadget, $task)) {
            return true;
        }

        $GLOBALS['app']->InstanceLayout();
        $GLOBALS['app']->Layout->LoadControlPanelHead();
        $user = $GLOBALS['app']->LoadGadget('Users', 'HTML');
        echo $user->ShowNoPermission($this->GetAttribute('username'), $gadget, $task);
        exit;
    }

}