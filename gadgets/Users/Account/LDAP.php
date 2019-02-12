<?php
/**
 * LDAP account class
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_LDAP extends Jaws_Gadget_Action
{
    /**
     * LDAP connection
     *
     * @var     string
     * @access  protected
     */
    protected $_LdapConnection;

    /**
     * LDAP server
     *
     * @var     string
     * @access  protected
     */
    protected $_Server = 'localhost';

    /**
     * LDAP port
     *
     * @var     string
     * @access  protected
     */
    protected $_Port = '389';

    /**
     * LDAP domain name
     *
     * @var     string
     * @access  protected
     */
    protected $_DN = 'dc=foobar,dc=org';

}