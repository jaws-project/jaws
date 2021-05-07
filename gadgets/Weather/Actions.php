<?php
/**
 * Weather Actions file
 *
 * @category    GadgetActions
 * @package     Weather
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['AllRegionsWeather'] = array(
    'normal' => true,
    'layout' => true,
    'parametric' => true,
    'file' => 'RegionWeather',
);
$actions['RegionWeather'] = array(
    'normal' => true,
    'layout' => true,
    'parametric' => true,
    'file' => 'RegionWeather',
);
$actions['UserRegionWeather'] = array(
    'normal' => true,
    'layout' => true,
    'parametric' => true,
    'file' => 'RegionWeather',
);
$actions['UserRegionsList'] = array(
    'normal' => true,
    'file' => 'RegionWeather',
);
$actions['GetUserRegions'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'RegionWeather',
);
$actions['DeleteUserRegion'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'RegionWeather',
    'acls' => array(
        'UserRegions',
    ),
);
$actions['GetRegion'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'RegionWeather',
);
$actions['GetGoogleMapImage'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'GoogleMap',
);
$actions['InsertRegion'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'RegionWeather',
    'acls' => array(
        'UserRegions',
    ),
);
$actions['UpdateRegion'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'RegionWeather',
    'acls' => array(
        'UserRegions',
    ),
);

/**
 * Admin actions
 */
$admin_actions['Regions'] = array(
    'normal' => true,
    'file' => 'Regions',
);
$admin_actions['Properties'] = array(
    'normal' => true,
    'file' => 'Properties',
);
$admin_actions['GetRegion'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertRegion'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateRegion'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteRegion'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateProperties'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['getData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGoogleMapImage'] = array(
    'standalone' => true,
    'file' => 'GoogleMap',
);
