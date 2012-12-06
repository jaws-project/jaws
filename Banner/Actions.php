<?php
/**
 * Banner Actions file
 *
 * @category    GadgetActions
 * @package     Banner
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Display']      = array('LayoutAction',
                                 _t('BANNER_ACTION_DISPLAY_NAME'),
                                 _t('BANNER_ACTION_DISPLAY_DESCRIPTION'),
                                 true);

/* Normal actions*/
$actions['Click']        = array('NormalAction');

/* Standalone actions*/
$actions['BannerGroup']  = array('StandaloneAction');
