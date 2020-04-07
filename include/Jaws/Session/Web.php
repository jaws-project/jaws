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
 * @copyright  2005-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Session_Web extends Jaws_Session
{
    /**
     * Initializes the Session
     *
     * @access  public
     * @return  void
     */
    function init()
    {
        parent::init();
        $reqParts = Jaws_Utils::parseRequestURL();
        // concat port to cookie name because cookie not support port
        $session = $this->getCookie(JAWS_SESSION_NAME . $reqParts['port']);
        if (empty($session) || !$this->load($session)) {
            $this->create();
        }
    }

    /**
     * Create a new session for a given data
     *
     * @access  public
     * @param   array   $info       User attributes
     * @param   bool    $remember   Remember me
     * @return  void
     * @see     Jaws_Session::Create
     */
    function create($info = array(), $remember = false)
    {
        parent::create($info, $remember);
        // create cookie
        $this->setCookie(
            JAWS_SESSION_NAME,
            $this->session['id'] . '-' . $this->session['salt'],
            $remember? 60*(int)$this->app->registry->fetch('session_remember_timeout', 'Policy') : 0
        );
    }

    /**
     * Logout from session
     *
     * @access  public
     * @return  void
     * @see Jaws_Session::Logout
     */
    function logout()
    {
        parent::Logout();
        $this->setCookie(
            JAWS_SESSION_NAME,
            $this->session['id'] . '-' . $this->session['salt'],
            0
        );
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
    function setCookie($name, $value, $minutes = 0, $httponly = null)
    {
        if (defined('SESSION_INVALID')) {
            return false;
        }

        $version = $this->app->registry->fetch('cookie_version', 'Settings');
        $expires = ($minutes == 0)? 0 : (time() + $minutes*60);
        $path    = $this->app->getSiteURL('/', true);

        //$this->app->registry->fetch('cookie_domain', 'Settings');
        $domain = '';

        // secure
        $secure = $this->app->registry->fetch('cookie_secure', 'Settings') == 'true';
        if (empty($_SERVER['HTTPS'])) {
            if (empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ||
                (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) != 'https')
            ) {
                $secure = false;
            }
        }

        // http only
        if (is_null($httponly)) {
            $httponly = $this->app->registry->fetch('cookie_httponly', 'Settings') == 'true';
        }

        $reqParts = Jaws_Utils::parseRequestURL();
        // concat port to cookie name because cookie not support port
        setcookie($name . $reqParts['port'], $value, $expires, $path, $domain, $secure, $httponly);
    }

    /**
     * Get a cookie
     *
     * @access  public
     * @param   string  $name   Cookie name
     * @return  string
     */
    function getCookie($name)
    {
        $version = $this->app->registry->fetch('cookie_version', 'Settings');
        return $this->app->request->fetch($name, 'cookie');
    }

    /**
     * Destroy a cookie
     *
     * @access  public
     * @param   string  $name   Cookie name
     * @return  void
     */
    function destroyCookie($name)
    {
        if (defined('SESSION_INVALID')) {
            return false;
        }

        $this->setCookie($name, false);
    }

    /**
     * Check permission on a given gadget/task
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   string  $key            ACL key(s) name
     * @param   string  $subkey         ACL subkey name
     * @param   bool    $together       And/Or tasks permission result, default true
     * @param   string  $errorMessage   Error message to return
     * @return  mixed   True if granted, else throws an Exception(Jaws_Error::Fatal)
     */
    function checkPermission($gadget, $key, $subkey = '', $together = true, $errorMessage = '')
    {
        if ($this->getPermission($gadget, $key, $subkey, $together)) {
            return true;
        }

        $user = Jaws_Gadget::getInstance('Users')->action->load('Default');
        $result = $user->ShowNoPermission($this->getAttribute('username'), $gadget, $key);
        $result = Jaws_HTTPError::Get(403, '', $result);

        terminate($result, 403);
    }

}