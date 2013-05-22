<?php
/**
 * ControlPanel Actions
 *
 * @category    GadgetActions
 * @package     ControlPanel
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();
$actions['DefaultAction'] = array(
    'normal' => true,
    'file' => 'ControlPanel',
);
$actions['LoginBox'] = array(
    'standalone' => true,
    'file' => 'Login',
);
$actions['Logout'] = array(
    'standalone' => true,
    'file' => 'Login',
);
$actions['Backup'] = array(
    'standalone' => true,
    'file' => 'Backup',
);
$actions['JawsVersion'] = array(
    'standalone' => true,
    'file' => 'JawsVersion',
);
