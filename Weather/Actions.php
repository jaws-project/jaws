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
$actions = array();

/* Admin actions */
$actions['Regions']           = array('AdminAction');
$actions['Properties']        = array('AdminAction');
$actions['GetGoogleMapImage'] = array('StandaloneAdminAction');

/* Normal actions*/
$actions['RegionWeather']     = array('NormalAction');
$actions['AllRegionsWeather'] = array('NormalAction,LayoutAction',
                                      _t('WEATHER_LAYOUT_REGIONS'),
                                      _t('WEATHER_LAYOUT_REGIONS_DESC'));
