<?php
/**
 * Phoo Actions file
 *
 * @category    GadgetActions
 * @package     Phoo
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['PhotoblogPortrait'] = array('NormalAction');
$actions['AlbumList']         = array(
    'NormalAction,LayoutAction',
    _t('PHOO_ALBUMS'),
    _t('PHOO_ALBUMS_DESC')
);
$actions['ViewAlbum']     = array('NormalAction');
$actions['ViewAlbumPage'] = array('NormalAction');
$actions['ViewImage']     = array('NormalAction');
$actions['Comment']       = array('NormalAction');
$actions['Reply']         = array('NormalAction');
$actions['Preview']       = array('NormalAction');
$actions['SaveComment']   = array('NormalAction');

/* LayoutActions */
$actions['Random'] = array(
    'LayoutAction',
    _t('PHOO_RANDOM_IMAGE'),
    _t('PHOO_RANDOM_IMAGE_DESC')
);
$actions['Moblog'] = array(
    'LayoutAction',
    _t('PHOO_MOBLOG'),
    _t('PHOO_MOBLOG_DESC')
);
$actions['RecentComments'] = array(
    'LayoutAction',
    _t('PHOO_RECENT_COMMENTS'),
    _t('PHOO_RECENT_COMMENTS_DESC')
);
