<?php
/**
 * Policy Actions file
 *
 * @category   GadgetActions
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['IPBlocking']       = array('AdminAction');
$admin_actions['AgentBlocking']    = array('AdminAction');
$admin_actions['Encryption']       = array('AdminAction');
$admin_actions['AntiSpam']         = array('AdminAction');
$admin_actions['AdvancedPolicies'] = array('AdminAction');

/* Index actions */
$index_actions['Captcha']       = array('StandaloneAction');
