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

/**
 * Index actions
 */
$actions['Display'] = array(
    'normal' => true,
    'file' => 'Files',
);
$actions['FileInfo'] = array(
    'normal' => true,
    'file' => 'Files',
);
$actions['Download'] = array(
    'standalone' => true,
    'file' => 'Files',
);
$actions['InitialFolder'] = array(
    'layout' => true,
    'file' => 'Directory',
);

/**
 * Admin actions
 */
$admin_actions['Files'] = array(
    'normal' => true,
    'file' => 'Files',
);
$admin_actions['UploadFile'] = array(
    'standalone' => true,
    'file' => 'Files',
);
$admin_actions['BrowseFile'] = array(
    'standalone' => true,
    'file' => 'Files',
);
$admin_actions['DeleteFile'] = array(
    'standalone' => true,
    'file' => 'Files',
);
$admin_actions['DeleteDir'] = array(
    'standalone' => true,
    'file' => 'Directory',
);
$admin_actions['DBFileInfo'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetDirContentsCount'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetLocation'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetDirectory'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateDBFileInfo'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateDBDirInfo'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteFile2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteDir2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
