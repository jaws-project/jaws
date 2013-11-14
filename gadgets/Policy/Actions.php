<?php
/**
 * Policy Actions file
 *
 * @category    GadgetActions
 * @package     Policy
 * @author      Amir Mohammad Saied <amir@gluegadget.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['Captcha'] = array(
    'standalone' => true,
    'variable' => true,
    'file' => 'Captcha',
);

/**
 * Admin actions
 */
$admin_actions['IPBlocking'] = array(
    'normal' => true,
    'file' => 'IP',
);
$admin_actions['AgentBlocking'] = array(
    'normal' => true,
    'file' => 'Agent',
);
$admin_actions['Encryption'] = array(
    'normal' => true,
    'file' => 'Encryption',
);
$admin_actions['AntiSpam'] = array(
    'normal' => true,
    'file' => 'AntiSpam',
);
$admin_actions['AdvancedPolicies'] = array(
    'normal' => true,
    'file' => 'AdvancedPolicies',
);
