<?php
/**
 * SysInfo Actions
 *
 * @category    GadgetActions
 * @package     SysInfo
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['SysInfo'] = array(
    'normal' => true,
	'layout' => true,
    'file' => 'SysInfo',
);
$actions['PHPInfo'] = array(
    'normal' => true,
	'layout' => true,
    'file' => 'PHPInfo',
);
$actions['JawsInfo'] = array(
    'normal' => true,
	'layout' => true,
    'file' => 'JawsInfo',
);
$actions['DirInfo'] = array(
    'normal' => true,
	'layout' => true,
    'file' => 'DirInfo',
);

/**
 * Admin actions
 */
$admin_actions['Admin'] = array(
    'normal' => true,
    'file' => 'Default',
);
$admin_actions['SysInfo'] = array(
    'normal' => true,
    'file' => 'SysInfo',
);
$admin_actions['PHPInfo'] = array(
    'normal' => true,
    'file' => 'PHPInfo',
);
$admin_actions['JawsInfo'] = array(
    'normal' => true,
    'file' => 'JawsInfo',
);
$admin_actions['DirInfo'] = array(
    'normal' => true,
    'file' => 'DirInfo',
);
