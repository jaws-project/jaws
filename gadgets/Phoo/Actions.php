<?php
/**
 * Phoo Actions file
 *
 * @category    GadgetActions
 * @package     Phoo
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['PhotoblogPortrait'] = array(
    'normal' => true,
    'file' => 'Photoblog',
);
$actions['AlbumList'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Albums',
    'parametric' => true,
);
$actions['ViewAlbum'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['ViewAlbumPage'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['ViewImage'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['UploadPhotoUI'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['UploadPhoto'] = array(
    'standalone' => true,
    'file' => 'Photos',
);
$actions['ViewUserPhotos'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['Random'] = array(
    'layout' => true,
    'file' => 'Random',
    'parametric' => true,
);
$actions['Moblog'] = array(
    'layout' => true,
    'file' => 'Moblog',
    'parametric' => true,
);
$actions['Groups'] = array(
    'layout' => true,
    'file' => 'Groups',
);

/**
 * Admin actions
 */
$admin_actions['UploadPhotos'] = array(
    'normal' => true,
    'file' => 'Upload',
);
$admin_actions['UploadPhotosStep2'] = array(
    'normal' => true,
    'file' => 'Upload',
);
$admin_actions['EditPhoto'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$admin_actions['SaveEditPhoto'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$admin_actions['AdminPhotos'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$admin_actions['ManageAlbums'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$admin_actions['DeletePhoto'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$admin_actions['NewAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$admin_actions['SaveNewAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$admin_actions['EditAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$admin_actions['SaveEditAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$admin_actions['DeleteAlbum'] = array(
    'normal' => true,
    'file' => 'Albums',
);
$admin_actions['RotateLeft'] = array(
    'normal' => true,
    'file' => 'Rotate',
);
$admin_actions['RotateRight'] = array(
    'normal' => true,
    'file' => 'Rotate',
);
$admin_actions['ManageComments'] = array(
    'normal' => true,
    'file' => 'Comments',
);
$admin_actions['AdditionalSettings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$admin_actions['SaveAdditionalSettings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$admin_actions['Import'] = array(
    'normal' => true,
    'file' => 'Import',
);
$admin_actions['FinishImport'] = array(
    'normal' => true,
    'file' => 'Import',
);
$admin_actions['Groups'] = array(
    'normal' => true,
    'file' => 'Groups',
);
$admin_actions['Thumb'] = array(
    'standalone' => true,
    'file' => 'Thumb',
);
$admin_actions['BrowsePhoo'] = array(
    'standalone' => true,
    'file' => 'BrowsePhoo',
);
$admin_actions['SelectImage'] = array(
    'standalone' => true,
    'file' => 'SelectImage',
);
$admin_actions['ImportImage'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdatePhoto'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetAlbums'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
