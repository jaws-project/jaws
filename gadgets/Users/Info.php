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
    var $_Version = '0.8.9';

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
        'EditAccountPassword',
        'EditAccountInformation',
        'EditAccountProfile',
        'EditAccountPreferences',
        'ManageAuthenticationMethod',
    );

}