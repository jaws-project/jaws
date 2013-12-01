<?php
/**
 * Logs Actions file
 *
 * @category   GadgetActions
 * @package    Logs
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Admin actions
 */
$admin_actions['Logs'] = array(
    'normal' => true,
    'file' => 'Logs',
    'priority' => 3,
);
$admin_actions['GetLogs'] = array(
    'standalone' => true,
    'file' => 'Logs',
    'priority' => 2,
);
$admin_actions['GetLogsCount'] = array(
    'standalone' => true,
    'file' => 'Logs',
    'priority' => 2,
);
$admin_actions['GetLog'] = array(
    'standalone' => true,
    'file' => 'Logs',
    'priority' => 2,
);
$admin_actions['DeleteLogs'] = array(
    'standalone' => true,
    'file' => 'Logs',
    'priority' => 5,
);
$admin_actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings',
    'priority' => 3,
);
$admin_actions['SaveSettings'] = array(
    'standalone' => true,
    'file' => 'Settings',
    'priority' => 5,
);
