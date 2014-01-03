<?php
/**
 * ControlPanel Actions
 *
 * @category    GadgetActions
 * @package     ControlPanel
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Admin actions
 */
$admin_actions['DefaultAction'] = array(
    'normal' => true,
    'file' => 'ControlPanel',
);
$admin_actions['LoginBox'] = array(
    'standalone' => true,
    'file' => 'Login',
);
$admin_actions['Backup'] = array(
    'standalone' => true,
    'file' => 'Backup',
);
$admin_actions['JawsVersion'] = array(
    'standalone' => true,
    'file' => 'JawsVersion',
);
