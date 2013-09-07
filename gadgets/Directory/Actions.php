<?php
/**
 * Contact Actions file
 *
 * @category    GadgetActions
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

/* Public Actions */
$actions['Directory'] = array(
    'normal' => true,
    'file' => 'Directory'
);
$actions['GetFiles'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$actions['GetFile'] = array(
    'standalone' => true,
    'file' => 'Directory'
);
$actions['GetPath'] = array(
    'standalone' => true,
    'file' => 'Directory'
);

/* Directory Actions */
$actions['DirectoryForm'] = array(
    'standalone' => true,
    'file' => 'Directories'
);
$actions['CreateDirectory'] = array(
    'standalone' => true,
    'file' => 'Directories'
);
$actions['UpdateDirectory'] = array(
    'standalone' => true,
    'file' => 'Directories'
);
$actions['DeleteDirectory'] = array(
    'standalone' => true,
    'file' => 'Directories'
);

/* File Actions */
$actions['FileForm'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$actions['CreateFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$actions['UpdateFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$actions['DeleteFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);
$actions['UploadFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);

/* Setting Actions */
$actions['GetShareForm'] = array(
    'standalone' => true,
    'file' => 'Share'
);
$actions['GetFileUsers'] = array(
    'standalone' => true,
    'file' => 'Share'
);
$actions['GetUsers'] = array(
    'standalone' => true,
    'file' => 'Share'
);

/* Setting Actions */
$actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings'
);
