<?php
/**
 * Policy Actions file
 *
 * @category    GadgetActions
 * @package     Policy
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

$actions['IPBlocking'] = array(
    'normal' => true,
    'file' => 'IP',
);
$actions['AgentBlocking'] = array(
    'normal' => true,
    'file' => 'Agent',
);
$actions['Encryption'] = array(
    'normal' => true,
    'file' => 'Encryption',
);
$actions['AntiSpam'] = array(
    'normal' => true,
    'file' => 'AntiSpam',
);
$actions['AdvancedPolicies'] = array(
    'normal' => true,
    'file' => 'AdvancedPolicies',
);
