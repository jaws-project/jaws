<?php
/**
 * LDAP authentication class
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Auth_LDAP
{
    /**
     * LDAP connection
     * @access  private
     */
    var $_LdapConnection;

    /**
     * Authentication ID
     * @access  private
     */
    var $_AuthID = '';

    /**
     * username
     * @access  private
     */
    var $_User = '';

    /**
     * LDAP server
     * @access  private
     */
    var $_Server = 'localhost';

    /**
     * LDAP port
     * @access  private
     */
    var $_Port = '389';

    /**
     * LDAP domain name string
     * @access  private
     */
    var $_DN = 'dc=foobar,dc=org';

    /**
     * Constructor
     *
     * @access  public
     */
    function Jaws_Auth_LDAP()
    {
        $this->_Server = 'localhost';
        $this->_Port   = '389';
        $this->_DN     = 'dc=foobar,dc=org';
    }

    /**
     * Authenticate user/password
     *
     * @access  public
     */
    function Auth($user, $password)
    {
        if (!function_exists('ldap_connect')) {
            return Jaws_Error::raiseError('Undefined function ldap_connect()',
                                          __FUNCTION__);
        }

        $this->_LdapConnection = @ldap_connect($this->_Server, $this->_Port);
        if ($this->_LdapConnection) {
            $rdn = "uid=" . $user . "," . $this->_DN;
            $bind = @ldap_bind($this->_LdapConnection, $rdn, $password);
            if ($bind) {
                $this->_User   = $user;
                $this->_AuthID = strtolower('ldap:'.$user);
                return $this->_AuthID; 
            }
        }

        return Jaws_Error::raiseError(_t('GLOBAL_ERROR_LOGIN_WRONG'),
                                          __FUNCTION__);
    }

    /**
     * Attributes of logged user
     *
     * @access  public
     */
    function GetAttributes()
    {
        $info = array();
        $info['id']         = $this->_AuthID;
        $info['internal']   = false;
        $info['username']   = $this->_User;
        $info['superadmin'] = false;
        $info['internal']   = false;
        $info['groups']     = array();
        $info['nickname']   = $this->_User;
        $info['concurrent_logins'] = 0;
        $info['email']      = '';
        $info['url']        = '';
        $info['avatar']     = 'gadgets/Users/images/photo48px.png';
        $info['language']   = '';
        $info['theme']      = '';
        $info['editor']     = '';
        $info['timezone']   = null;
        return $info;
    }

}