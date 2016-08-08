<?php
/**
 * Weather URL maps
 *
 * @category   GadgetMaps
 * @package    Weather
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('AllRegionsWeather', 'weather');
$maps[] = array(
    'RegionWeather',
    'weather/{id}',
    array('id' =>  '[\p{L}[:digit:]\-_\.]+',)
);
$maps[] = array(
    'UserRegionsList',
    'weather/user/regions',
);
