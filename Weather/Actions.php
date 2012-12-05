<?php
/**
 * Weather Actions file
 *
 * @category   GadgetActions
 * @package    Weather
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Regions']           = array('AdminAction');
$admin_actions['Properties']        = array('AdminAction');
$admin_actions['GetGoogleMapImage'] = array('StandaloneAdminAction');

/* Normal actions*/
$index_actions['RegionWeather'] = array(
    'NormalAction,LayoutAction',
    _t('WEATHER_LAYOUT_REGION'),
    _t('WEATHER_LAYOUT_REGION_DESC'),
    true
);
$index_actions['AllRegionsWeather'] = array(
    'NormalAction,LayoutAction',
    _t('WEATHER_LAYOUT_REGIONS'),
    _t('WEATHER_LAYOUT_REGIONS_DESC')
);
