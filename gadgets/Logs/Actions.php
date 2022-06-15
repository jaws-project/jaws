<?php
/**
 * Logs Actions file
 *
 * @category    GadgetActions
 * @package     Logs
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['LoginHistory'] = array(
    'normal' => true,
    'layout' => true,
    'parametric' => true,
    'file' => 'History',
);
/**
 * Admin actions
 */
$admin_actions['Logs'] = array(
    'normal' => true,
    'file' => 'Logs',
);
$admin_actions['GetLogs'] = array(
    'standalone' => true,
    'file' => 'Logs',
);
$admin_actions['GetLog'] = array(
    'standalone' => true,
    'file' => 'Logs',
);
$admin_actions['DeleteLogs'] = array(
    'standalone' => true,
    'file' => 'Logs',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['ExportLogs'] = array(
    'standalone' => true,
    'file' => 'Logs',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$admin_actions['SaveSettings'] = array(
    'standalone' => true,
    'file' => 'Settings',
    'loglevel' => JAWS_WARNING,
);
