<?php
/**
 * Phoo Actions file
 *
 * @category   GadgetActions
 * @package    Phoo
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();
/* Normal actions */
$actions['PhotoblogPortrait'] = array('NormalAction');
$actions['AlbumList']         = array('NormalAction,LayoutAction', _t('PHOO_ALBUMS'), _t('PHOO_ALBUMS_DESC'));
$actions['ViewAlbum']         = array('NormalAction');
$actions['ViewAlbumPage']     = array('NormalAction');
$actions['ViewImage']         = array('NormalAction');
$actions['Comment']           = array('NormalAction');
$actions['Reply']             = array('NormalAction');
$actions['Preview']           = array('NormalAction');
$actions['SaveComment']       = array('NormalAction');

/* LayoutActions */
$actions['Random']         = array('LayoutAction', _t('PHOO_RANDOM_IMAGE'), _t('PHOO_RANDOM_IMAGE_DESC'));
$actions['Moblog']         = array('LayoutAction', _t('PHOO_MOBLOG'), _t('PHOO_MOBLOG_DESC'));
$actions['RecentComments'] = array('LayoutAction', _t('PHOO_RECENT_COMMENTS'), _t('PHOO_RECENT_COMMENTS_DESC'));

/* Admin actions */
$actions['Admin']                  = array('AdminAction');
$actions['UploadPhotos']           = array('AdminAction');
$actions['UploadPhotosStep2']      = array('AdminAction');
$actions['EditPhoto']              = array('AdminAction');
$actions['SaveEditPhoto']          = array('AdminAction');
$actions['AdminPhotos']            = array('AdminAction');
$actions['ManageAlbums']           = array('AdminAction');
$actions['DeletePhoto']            = array('AdminAction');
$actions['NewAlbum']               = array('AdminAction');
$actions['SaveNewAlbum']           = array('AdminAction');
$actions['EditAlbum']              = array('AdminAction');
$actions['SaveEditAlbum']          = array('AdminAction');
$actions['DeleteAlbum']            = array('AdminAction');
$actions['RotateLeft']             = array('AdminAction');
$actions['RotateRight']            = array('AdminAction');
$actions['ManageComments']         = array('AdminAction');
$actions['EditComment']            = array('AdminAction');
$actions['SaveEditComment']        = array('AdminAction');
$actions['DeleteComment']          = array('AdminAction');
$actions['AdditionalSettings']     = array('AdminAction');
$actions['SaveAdditionalSettings'] = array('AdminAction');
$actions['Import']                 = array('AdminAction');
$actions['FinishImport']           = array('AdminAction');

/* Standalone Admin Actions */
$actions['Thumb']                  = array('StandaloneAdminAction:Thumb');
$actions['BrowsePhoo']             = array('StandaloneAdminAction:BrowsePhoo');
$actions['SelectImage']            = array('StandaloneAdminAction:SelectImage');
