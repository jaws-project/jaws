<?php
/**
 * Phoo Actions file
 *
 * @category    GadgetActions
 * @package     Phoo
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Admin'] = array('AdminAction');
$actions['UploadPhotos'] = array('AdminAction');
$actions['UploadPhotosStep2'] = array('AdminAction');
$actions['EditPhoto'] = array('AdminAction');
$actions['SaveEditPhoto'] = array('AdminAction');
$actions['AdminPhotos'] = array('AdminAction');
$actions['ManageAlbums'] = array('AdminAction');
$actions['DeletePhoto'] = array('AdminAction');
$actions['NewAlbum'] = array('AdminAction');
$actions['SaveNewAlbum'] = array('AdminAction');
$actions['EditAlbum'] = array('AdminAction');
$actions['SaveEditAlbum'] = array('AdminAction');
$actions['DeleteAlbum'] = array('AdminAction');
$actions['RotateLeft'] = array('AdminAction');
$actions['RotateRight'] = array('AdminAction');
$actions['ManageComments'] = array('AdminAction');
$actions['EditComment'] = array('AdminAction');
$actions['SaveEditComment'] = array('AdminAction');
$actions['DeleteComment'] = array('AdminAction');
$actions['AdditionalSettings'] = array('AdminAction');
$actions['SaveAdditionalSettings'] = array('AdminAction');
$actions['Import'] = array('AdminAction');
$actions['FinishImport'] = array('AdminAction');
/* Standalone Admin Actions */
$actions['Thumb'] = array('StandaloneAdminAction:Thumb');
$actions['BrowsePhoo'] = array('StandaloneAdminAction:BrowsePhoo');
$actions['SelectImage'] = array('StandaloneAdminAction:SelectImage');
