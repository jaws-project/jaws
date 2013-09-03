<?php
/**
 * Contact Actions file
 *
 * @category    GadgetActions
 * @package     FileManager
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();
$actions['FileManager'] = array(
    'normal' => true,
    'file' => 'FileManager'
);

/* Dir Actions */
$actions['DirForm'] = array(
    'normal' => true,
    'file' => 'Dirs'
);
$actions['CreateDir'] = array(
    'normal' => true,
    'file' => 'Dirs'
);
$actions['UpdateDir'] = array(
    'normal' => true,
    'file' => 'Dirs'
);
$actions['DeleteDir'] = array(
    'normal' => true,
    'file' => 'Dirs'
);

/* File Actions */
$actions['FileForm'] = array(
    'normal' => true,
    'file' => 'Files'
);
$actions['GetFiles'] = array(
    'normal' => true,
    'file' => 'FileManager'
);
$actions['GetFile'] = array(
    'normal' => true,
    'file' => 'FileManager'
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
