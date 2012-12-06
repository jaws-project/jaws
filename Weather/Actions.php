<?php
/**
 * Weather Actions file
 *
 * @category    GadgetActions
 * @package     Weather
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['RegionWeather'] = array(
    'NormalAction,LayoutAction',
    _t('WEATHER_LAYOUT_REGION'),
    _t('WEATHER_LAYOUT_REGION_DESC'),
    true
);
$actions['AllRegionsWeather'] = array(
    'NormalAction,LayoutAction',
    _t('WEATHER_LAYOUT_REGIONS'),
    _t('WEATHER_LAYOUT_REGIONS_DESC')
);
