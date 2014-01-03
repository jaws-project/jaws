<?php
/**
 * Class to provide HTTP authentication
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_HTTPAuth
{
    /**
     * Username
     *
     * @access  private
     * @var     string
     */
    var $username = '';

    /**
     * Password
     *
     * @access  private
     * @var     string
     */
    var $password = '';

    function AssignData()
    {
        if (!empty($_SERVER['PHP_AUTH_USER'])) {
            $this->username = Jaws_XSS::filter($_SERVER['PHP_AUTH_USER']);
        }

        if (!empty($_SERVER['PHP_AUTH_PW'])) {
            $this->password = Jaws_XSS::filter($_SERVER['PHP_AUTH_PW']);
        }

        //Try to get authentication information from IIS
        if (empty($this->username) && empty($this->password) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            list($this->username, $this->password) = explode(':', base64_decode(substr($this->server['HTTP_AUTHORIZATION'], 6)));
        }
    }

    function getUsername()
    {
        return $this->username;
    }

    function getPassword()
    {
        return $this->password;
    }

    function showLoginBox()
    {
        $realm = $GLOBALS['app']->Registry->fetch('realm', 'Settings');
        header('WWW-Authenticate: Basic realm="'.$realm.'"');
        header('HTTP/1.0 401 Unauthorized');            

        // This code is only executed if the user hits the cancel button
        // or in some browsers user enters wrong data 3 times.
        $data = _t('GLOBAL_ERROR_ACCESS_DENIED');
        terminate($data, 401);
    }
}
