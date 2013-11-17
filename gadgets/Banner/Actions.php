<?php
/**
 * Banner Actions file
 *
 * @category    GadgetActions
 * @package     Banner
 */

/**
 * Index actions
 */
$actions['Banners'] = array(
    'layout' => true,
    'parametric' => true,
    'standalone' => true,
    'file' => 'Banners',
);
$actions['Click'] = array(
    'normal' => true,
    'file' => 'Banners',
);

/**
 * Admin actions
 */
$admin_actions['Banners'] = array(
    'normal' => true,
    'file' => 'Banners',
);
$admin_actions['Groups'] = array(
    'normal' => true,
    'file' => 'Groups',
);
$admin_actions['Reports'] = array(
    'normal' => true,
    'file' => 'Reports',
);
$admin_actions['UploadBanner'] = array(
    'normal' => true,
    'file' => 'Banners',
);
$admin_actions['GetBanner'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetBanners'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroups'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertBanner'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateBanner'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddBannersToGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteBanner'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ResetViews'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ResetClicks'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroupUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroupBannersUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['getBannersDataGrid'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetBannersCount'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
