<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetInfo
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UsersInfo extends Jaws_GadgetInfo
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '1.0.0';

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'ManageUsers',
        'ManageGroups',
        'ManageProperties',
        'ManageUserACLs',
        'ManageGroupACLs',
        'EditUserName',
        'EditUserNickname',
        'EditUserEmail',
        'EditUserPassword',
        'EditUserPersonal',
        'EditUserContact',
        'EditUserPreferences',
        'ManageAuthenticationMethod',
    );

}