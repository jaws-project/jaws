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
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['GetLogs'] = array(
    'standalone' => true,
    'file' => 'Logs',
);
$admin_actions['GetLogsCount'] = array(
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
$admin_actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['SaveSettings'] = array(
    'standalone' => true,
    'file' => 'Settings',
    'loglevel' => JAWS_WARNING,
);
