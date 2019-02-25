<?php
/**
 * IMAP account class
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_IMAP extends Jaws_Gadget_Action
{
    /**
     * IMAP server
     *
     * @var     string
     * @access  protected
     */
    protected $_Server = 'localhost';

    /**
     * IMAP port
     *
     * @var     string
     * @access  protected
     */
    protected $_Port = '143';

    /**
     * Using SSL
     *
     * @var     bool
     * @access  protected
     */
    protected $_SSL = false;

}