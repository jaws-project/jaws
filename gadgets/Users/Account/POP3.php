<?php
/**
 * POP3 account class
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_POP3 extends Jaws_Gadget_Action
{
    /**
     * POP3 server
     *
     * @var     string
     * @access  protected
     */
    protected $_Server = 'localhost';

    /**
     * POP3 port
     *
     * @var     string
     * @access  protected
     */
    protected $_Port = '110';

    /**
     * Using SSL
     *
     * @var     bool
     * @access  protected
     */
    protected $_SSL = false;

}