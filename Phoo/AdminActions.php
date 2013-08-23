<?php
/**
 * Phoo Actions file
 *
 * @category    GadgetActions
 * @package     Phoo
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['UploadPhotos'] = array(
    'normal' => true,
    'file' => 'Upload',
);
$actions['UploadPhotosStep2'] = array(
    'normal' => true,
    'file' => 'Upload',
);
$actions['EditPhoto'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['SaveEditPhoto'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['AdminPhotos'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['ManageAlbums'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$actions['DeletePhoto'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['NewAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$actions['SaveNewAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$actions['EditAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$actions['SaveEditAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$actions['DeleteAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$actions['RotateLeft'] = array(
    'normal' => true,
    'file' => 'Rotate',
);
$actions['RotateRight'] = array(
    'normal' => true,
    'file' => 'Rotate',
);
$actions['ManageComments'] = array(
    'normal' => true,
    'file' => 'Comments',
);
$actions['AdditionalSettings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$actions['SaveAdditionalSettings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$actions['Import'] = array(
    'normal' => true,
    'file' => 'Import',
);
$actions['FinishImport'] = array(
    'normal' => true,
    'file' => 'Import',
);

/* Standalone Admin Actions */
$actions['Thumb'] = array(
    'standalone' => true,
    'file' => 'Thumb',
);
$actions['BrowsePhoo'] = array(
    'standalone' => true,
    'file' => 'BrowsePhoo',
);
$actions['SelectImage'] = array(
    'standalone' => true,
    'file' => 'SelectImage',
);
