<?php
/**
 * Directory Actions file
 *
 * @category    GadgetActions
 * @package     Directory
 */

/**
 * Index actions
 */
$actions['Directory'] = array(
    'normal' => true,
    'layout' => true,
    'parametric' => true,
    'file' => 'Directory'
);
$actions['Download'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$actions['UploadFile'] = array(
    'standalone' => true,
    'file' => 'File'
);
$actions['SaveFile'] = array(
    'standalone' => true,
    'file' => 'File'
);
$actions['DeleteFile'] = array(
    'standalone' => true,
    'file' => 'File'
);
$actions['GetFile'] = array(
    'standalone' => true,
    'file' => 'File'
);

/**
 * Admin actions
 */
$admin_actions['Directory'] = array(
    'normal' => true,
    'file' => 'Directory'
);
$admin_actions['Statistics'] = array(
    'normal' => true,
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
$admin_actions['GetDownloadURL'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['PlayMedia'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$admin_actions['ManageComments'] = array(
    'normal' => true,
    'file' => 'Comments',
);
