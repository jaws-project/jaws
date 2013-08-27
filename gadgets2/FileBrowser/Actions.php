<?php
/**
 * FileBrowser Actions file
 *
 * @category    GadgetActions
 * @package     FileBrowser
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Display'] = array(
    'normal' => true,
    'file' => 'File',
);
$actions['FileInfo'] = array(
    'normal' => true,
    'file' => 'File',
);
$actions['Download'] = array(
    'standalone' => true,
    'file' => 'File',
);
$actions['InitialFolder'] = array(
    'layout' => true,
    'file' => 'Directory',
);