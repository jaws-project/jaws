<?php
/**
 * LDAP class definition
 *
 * @category    LDAP
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2021-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_LDAP
{
    /**
     * Specifies the scope of the search
     *
     */
    public const SCOPE_BASE     = 0;
    public const SCOPE_ONELEVEL = 1;
    public const SCOPE_SUBTREE  = 2;

    /**
     * LDAP connection
     *
     * @var     resource|false
     * @access  private
     */
    private $ldapConnection;

    /**
     * LDAP server URI
     *
     * @var     string
     * @access  private
     */
    private $uri = 'ldap://localhost:389';

    /**
     * LDAP base DN
     *
     * @var     string
     * @access  private
     */
    private $baseDN = 'cn=admin,dc=foobar,dc=org';

    /**
     * LDAP base DN password
     *
     * @var     string
     * @access  private
     */
    private $passwd = '';

    /**
     * Jaws_LDAP instances
     *
     * @var     array
     * @access  private
     */
    private static $instances = array();

    /**
     * Constructor
     *
     * @access  private
     * @param   array   $options    LDAP connection options
     * @return  void
     */
    private function __construct($options = array())
    {
        $this->uri = isset($options['uri'])? $options['uri'] : 'ldap://localhost:389';
        $this->baseDN  = isset($options['baseDN'])? $options['baseDN'] : '';
        $this->passwd  = isset($options['password'])? $options['password'] : '';
    }

    /**
     * Get a Jaws_LDAP instance
     *
     * @access  public
     * @param   string $instance    Jaws_LDAP instance name
     * @param   array  $options     LDAP connection options
     * @return  object Jaws_LDAP instance
     */
    static function getInstance($options = array(), $instance = 'default')
    {
        if (!isset(self::$instances[$instance])) {
            self::$instances[$instance] = new Jaws_LDAP($options);
        }

        return self::$instances[$instance];
    }

    /**
     * Connect to an LDAP server
     *
     * @access  public
     * @param   string  $uri    (optional) The ldap server URI
     * @return  bool    True on success, otherwise false
     */
    function connect($uri = null)
    {
        if (isset($uri)) {
            $this->uri = $uri;
        }

        $this->ldapConnection = ldap_connect($this->uri);
        return $this->ldapConnection !== false;
    }

    /**
     * Bind to LDAP directory
     *
     * @access  public
     * @param   string  $bindDN     The distinguished name of an LDAP entity
     * @param   string  $bindPasswd Password
     * @return  bool    True on success, otherwise false
     */
    function bind($bindDN = null, $bindPasswd = null)
    {
        if (!isset($bindDN)) {
            $bindDN = $this->baseDN;;
        }

        if (!isset($bindPasswd)) {
            $bindPasswd = $this->passwd;
        }

        return @ldap_bind($this->ldapConnection, $bindDN, $bindPasswd);
    }

    /**
     * Search in the LDAP directory and return first entry founded 
     *
     * @see https://php.net/ldap_search
     *
     * @access  public
     * @param   string  $base       The base DN for the directory
     * @param   string  $filter     Search filter
     * @param   array   $attributes LDAP fields to retrieve
     * @return  mixed   Attributes array of first founded entry on success, otherwise Jaws_Error
     */
    function get($base, $filter, $attributes = array(), $scope = self::SCOPE_SUBTREE)
    {
        switch ($scope) {
            case self::SCOPE_BASE:
                $result = @ldap_read($this->ldapConnection, $base, $filter, array(), 0, 1);
                break;

            case self::SCOPE_ONELEVEL:
                $result = @ldap_list($this->ldapConnection, $base, $filter, array(), 0, 1);
                break;

            default:
                $result = @ldap_search($this->ldapConnection, $base, $filter, array(), 0, 1);
        }

        if ($result) {
            //ldap_count_entries
            $data = ldap_get_entries($this->ldapConnection, $result);
            return $data;
        }

        return Jaws_Error::raiseError(
            ldap_error($this->ldapConnection),
            ldap_errno($this->ldapConnection)
        );
    }

    /**
     * Search in the LDAP directory
     *
     * @see https://php.net/ldap_search
     *
     * @access  public
     * @param   string  $base       The base DN for the directory
     * @param   string  $filter     Search filter
     * @param   array   $attributes LDAP fields to retrieve
     * @param   int     $scope      Search scope (SCOPE_ SUBTREE, ONELEVEL or BASE)
     * @param   int     $limit
     * @param   int     $offset
     * @return  mixed   Founded entries on success, otherwise Jaws_Error
     */
    function list($base, $filter, $attributes = array(), $scope = self::SCOPE_SUBTREE, $limit = 0, $offset = null)
    {
        switch ($scope) {
            case self::SCOPE_BASE:
                $result = @ldap_read($this->ldapConnection, $base, $filter, array(), 0, $limit);
                break;

            case self::SCOPE_ONELEVEL:
                $result = @ldap_list($this->ldapConnection, $base, $filter, array(), 0, $limit);
                break;

            default:
                $result = @ldap_search($this->ldapConnection, $base, $filter, array(), 0, $limit);
        }

        if ($result) {
            //ldap_count_entries
            $data = ldap_get_entries($this->ldapConnection, $result);
            return $data;
        }

        return Jaws_Error::raiseError(
            ldap_error($this->ldapConnection),
            ldap_errno($this->ldapConnection)
        );
    }

    /**
     * Add new entry to LDAP directory
     *
     * @access  public
     * @param   string  $dn     The distinguished name of an LDAP entity
     * @param   array   $entry  An array that specifies the information about the entry
     * @return  bool    True on success, otherwise Jaws_Error
     */
    function add($dn, $entry)
    {
        if (ldap_add($this->ldapConnection, $dn, $entry)) {
            return true;
        }

        return Jaws_Error::raiseError(
            ldap_error($this->ldapConnection),
            ldap_errno($this->ldapConnection)
        );
    }

    /**
     * Update an existing entry in LDAP directory
     *
     * @access  public
     * @param   string  $dn     The distinguished name of an LDAP entity
     * @param   array   $entry  An array that specifies the information about the entry
     * @return  bool    True on success, otherwise Jaws_Error
     */
    function update($dn, $entry)
    {
        if (ldap_mod_replace($this->ldapConnection, $dn, $entry)) {
            return true;
        }

        return Jaws_Error::raiseError(
            ldap_error($this->ldapConnection),
            ldap_errno($this->ldapConnection)
        );
    }

    /**
     * delete an existing entry in LDAP directory
     *
     * @access  public
     * @param   string  $dn     The distinguished name of an LDAP entity
     * @return  bool    True on success, otherwise Jaws_Error
     */
    function delete($dn)
    {
        if (ldap_delete($this->ldapConnection, $dn)) {
            return true;
        }

        return Jaws_Error::raiseError(
            ldap_error($this->ldapConnection),
            ldap_errno($this->ldapConnection)
        );
    }

    /**
     * This function close the LDAP-connection
     *
     * @access  public
     * @return  bool    Returns true on success, false on failure
     */
    function disconnect()
    {
        if ($result = ldap_unbind($this->ldapConnection)) {
            unset($this->ldapConnection);
        }

        return $result;
    }

}