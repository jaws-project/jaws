<?php
/**
 * Banner Actions file
 *
 * @category   GadgetActions
 * @package    Banner
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Admin']        = array('AdminAction');
$admin_actions['Groups']       = array('AdminAction');
$admin_actions['Reports']      = array('AdminAction');
$admin_actions['UploadBanner'] = array('AdminAction');

/* Layout actions */
$index_actions['Display']      = array('LayoutAction',
                                 _t('BANNER_ACTION_DISPLAY_NAME'),
                                 _t('BANNER_ACTION_DISPLAY_DESCRIPTION'),
                                 true);

/* Normal actions*/
$index_actions['Click']        = array('NormalAction');

/* Standalone actions*/
$index_actions['BannerGroup']  = array('StandaloneAction');
