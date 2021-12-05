<?php
/**
 * Users Core Gadget
 *
 * @category    GadgetInfo
 * @package     Users
 */
class Users_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '5.2.0';

    /**
     * Recommended gadgets
     *
     * @var     array
     * @access  public
     */
    var $recommended = array('Notification');

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Profile';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Users';

}