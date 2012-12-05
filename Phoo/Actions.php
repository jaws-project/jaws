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
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Admin']                  = array('AdminAction');
$admin_actions['UploadPhotos']           = array('AdminAction');
$admin_actions['UploadPhotosStep2']      = array('AdminAction');
$admin_actions['EditPhoto']              = array('AdminAction');
$admin_actions['SaveEditPhoto']          = array('AdminAction');
$admin_actions['AdminPhotos']            = array('AdminAction');
$admin_actions['ManageAlbums']           = array('AdminAction');
$admin_actions['DeletePhoto']            = array('AdminAction');
$admin_actions['NewAlbum']               = array('AdminAction');
$admin_actions['SaveNewAlbum']           = array('AdminAction');
$admin_actions['EditAlbum']              = array('AdminAction');
$admin_actions['SaveEditAlbum']          = array('AdminAction');
$admin_actions['DeleteAlbum']            = array('AdminAction');
$admin_actions['RotateLeft']             = array('AdminAction');
$admin_actions['RotateRight']            = array('AdminAction');
$admin_actions['ManageComments']         = array('AdminAction');
$admin_actions['EditComment']            = array('AdminAction');
$admin_actions['SaveEditComment']        = array('AdminAction');
$admin_actions['DeleteComment']          = array('AdminAction');
$admin_actions['AdditionalSettings']     = array('AdminAction');
$admin_actions['SaveAdditionalSettings'] = array('AdminAction');
$admin_actions['Import']                 = array('AdminAction');
$admin_actions['FinishImport']           = array('AdminAction');
/* Standalone Admin Actions */
$admin_actions['Thumb']                  = array('StandaloneAdminAction:Thumb');
$admin_actions['BrowsePhoo']             = array('StandaloneAdminAction:BrowsePhoo');
$admin_actions['SelectImage']            = array('StandaloneAdminAction:SelectImage');

/* Normal actions */
$index_actions['PhotoblogPortrait'] = array('NormalAction');
$index_actions['AlbumList']         = array(
    'NormalAction,LayoutAction',
    _t('PHOO_ALBUMS'),
    _t('PHOO_ALBUMS_DESC')
);
$index_actions['ViewAlbum']     = array('NormalAction');
$index_actions['ViewAlbumPage'] = array('NormalAction');
$index_actions['ViewImage']     = array('NormalAction');
$index_actions['Comment']       = array('NormalAction');
$index_actions['Reply']         = array('NormalAction');
$index_actions['Preview']       = array('NormalAction');
$index_actions['SaveComment']   = array('NormalAction');

/* LayoutActions */
$index_actions['Random'] = array(
    'LayoutAction',
    _t('PHOO_RANDOM_IMAGE'),
    _t('PHOO_RANDOM_IMAGE_DESC')
);
$index_actions['Moblog'] = array(
    'LayoutAction',
    _t('PHOO_MOBLOG'),
    _t('PHOO_MOBLOG_DESC')
);
$index_actions['RecentComments'] = array(
    'LayoutAction',
    _t('PHOO_RECENT_COMMENTS'),
    _t('PHOO_RECENT_COMMENTS_DESC')
);
