<?php
/**
 * LDAP authentication class
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Auth_LDAP
{
    /**
     * LDAP connection
     *
     * @var     string
     * @access  private
     */
    private $_LdapConnection;

    /**
     * LDAP server
     *
     * @var     string
     * @access  private
     */
    private $_Server = 'localhost';

    /**
     * LDAP port
     *
     * @var     string
     * @access  private
     */
    private $_Port = '389';

    /**
     * LDAP domain name
     *
     * @var     string
     * @access  private
     */
    private $_DN = 'dc=foobar,dc=org';

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct()
    {
        $this->_Server = 'localhost';
        $this->_Port   = '389';
        $this->_DN     = 'dc=foobar,dc=org';
    }

    /**
     * Authenticate user/password
     *
     * @access  public
     * @param   array   $loginData  Login data(username, password, ...)
     * @return  mixed   Array of user's information otherwise Jaws_Error
     */
    function Auth($loginData)
    {
        if (!function_exists('ldap_connect')) {
            return Jaws_Error::raiseError(
                'Undefined function ldap_connect()',
                __FUNCTION__
            );
        }

        if ($loginData['usecrypt']) {
            $JCrypt = Jaws_Crypt::getInstance();
            if (!Jaws_Error::IsError($JCrypt)) {
                $loginData['password'] = $JCrypt->decrypt($loginData['password']);
            }
        } else {
            $loginData['password'] = Jaws_XSS::defilter($loginData['password']);
        }

        $this->_LdapConnection = @ldap_connect($this->_Server, $this->_Port);
        if ($this->_LdapConnection) {
            $rdn = "uid=" . $loginData['username'] . "," . $this->_DN;
            $bind = @ldap_bind($this->_LdapConnection, $rdn, $loginData['password']);
            if ($bind) {
                $resulat = array();
                $result['id']         = strtolower('ldap:'.$loginData['username']);
                $result['internal']   = false;
                $result['username']   = $loginData['username'];
                $result['superadmin'] = false;
                $result['internal']   = false;
                $result['groups']     = array();
                $result['nickname']   = $loginData['username'];
                $result['concurrents'] = 0;
                $result['email']      = '';
                $result['url']        = '';
                $result['avatar']     = 'gadgets/Users/Resources/images/photo48px.png';
                $result['language']   = '';
                $result['theme']      = '';
                $result['editor']     = '';
                $result['timezone']   = null;
                return $result;
            }
        }

        return Jaws_Error::raiseError(_t('GLOBAL_ERROR_LOGIN_WRONG'),
                                          __FUNCTION__);
    }

}