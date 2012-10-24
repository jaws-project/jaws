<?php
/**
 * Policy Gadget
 *
 * @category   GadgetInfo
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class PolicyInfo extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '0.2.0';

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
        'IPBlocking',
        'ManageIPs',
        'AgentBlocking',
        'ManageAgents',
        'Encryption',
        'ManageEncryptionKey',
        'AntiSpam',
        'AdvancedPolicies',
    );

}