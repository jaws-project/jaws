<?php
/**
 * Weather Actions file
 *
 * @category    GadgetActions
 * @package     Weather
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Regions'] = array(
    'normal' => true,
    'file' => 'Regions',
);
$actions['Properties'] = array(
    'normal' => true,
    'file' => 'Properties',
);
$actions['GetGoogleMapImage'] = array(
    'standalone' => true,
    'file' => 'GoogleMap',
);
