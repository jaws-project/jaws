<?php
/**
 * LDAP authentication class
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2014 Jaws Development Group
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
     * @param   string  $user       User's name or email
     * @param   string  $password   User's password
     * @return  mixed   Array of user's information otherwise Jaws_Error
     */
    function Auth($user, $password)
    {
        if (!function_exists('ldap_connect')) {
            return Jaws_Error::raiseError(
                'Undefined function ldap_connect()',
                __FUNCTION__
            );
        }

        $this->_LdapConnection = @ldap_connect($this->_Server, $this->_Port);
        if ($this->_LdapConnection) {
            $rdn = "uid=" . $user . "," . $this->_DN;
            $bind = @ldap_bind($this->_LdapConnection, $rdn, $password);
            if ($bind) {
                $resulat = array();
                $result['id']         = strtolower('ldap:'.$user);
                $result['internal']   = false;
                $result['username']   = $user;
                $result['superadmin'] = false;
                $result['internal']   = false;
                $result['groups']     = array();
                $result['nickname']   = $user;
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