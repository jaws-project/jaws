<?php
/**
 * Directory Actions file
 *
 * @category    GadgetActions
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Directory'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Directory'
);
$actions['ListFiles'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Directory'
);
$actions['ViewFile'] = array(
    'normal' => true,
    'file' => 'Directory'
);
$actions['Download'] = array(
    'standalone' => true,
    'file' => 'Directory'
);

/**
 * Admin actions
 */
$admin_actions['Directory'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Directory'
);
$admin_actions['Statistics'] = array(
    'layout' => true,
    'file' => 'Statistics'
);
$admin_actions['GetFiles'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$admin_actions['GetFile'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$admin_actions['GetPath'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$admin_actions['GetTree'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$admin_actions['Move'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$admin_actions['Delete'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$admin_actions['Search'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$admin_actions['DirectoryForm'] = array(
    'standalone' => true,
    'file' => 'Directories'
);
$admin_actions['CreateDirectory'] = array(
    'standalone' => true,
    'file' => 'Directories'
);
$admin_actions['UpdateDirectory'] = array(
    'standalone' => true,
    'file' => 'Directories'
);
$admin_actions['FileForm'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['CreateFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['UpdateFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['DeleteFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['UploadFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['DownloadFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['OpenFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['GetDownloadURL'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['PlayMedia'] = array(
    'standalone' => true,
    'file' => 'Files'
);
