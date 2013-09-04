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
$actions['Directory'] = array(
    'normal' => true,
    'file' => 'Directory'
);

/* Dir Actions */
$actions['DirectoryForm'] = array(
    'normal' => true,
    'file' => 'Directories'
);
$actions['CreateDirectory'] = array(
    'normal' => true,
    'file' => 'Directories'
);
$actions['UpdateDirectory'] = array(
    'normal' => true,
    'file' => 'Directories'
);
$actions['DeleteDir'] = array(
    'normal' => true,
    'file' => 'Directories'
);

/* File Actions */
$actions['FileForm'] = array(
    'normal' => true,
    'file' => 'Files'
);
$actions['GetFiles'] = array(
    'normal' => true,
    'file' => 'Directory'
);
$actions['GetFile'] = array(
    'normal' => true,
    'file' => 'Directory'
);
$actions['CreateFile'] = array(
    'normal' => true,
    'file' => 'Files'
);
$actions['UpdateFile'] = array(
    'normal' => true,
    'file' => 'Files'
);
$actions['DeleteFile'] = array(
    'normal' => true,
    'file' => 'Files'
);
$actions['UploadFile'] = array(
    'standalone' => true,
    'file' => 'Files'
);

/* Setting Actions */
$actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings'
);
