<?php
/**
 * Menu Actions file
 *
 * @category    GadgetActions
 * @package     Menu
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Menu'] = array(
    'layout' => true,
    'file' => 'Menu',
    'parametric' => true
);
$actions['LoadImage'] = array(
    'standalone' => true,
    'file' => 'Menu'
);

/**
 * Admin actions
 */
$admin_actions['Menu'] = array(
    'normal' => true,
    'file' => 'Menu'
);
$admin_actions['UploadImage'] = array(
    'standalone' => true,
    'file' => 'Menu'
);
$admin_actions['LoadImage'] = array(
    'standalone' => true,
    'file' => 'Menu'
);
