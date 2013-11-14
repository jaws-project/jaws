<?php
/**
 * Banner Actions file
 *
 * @category    GadgetActions
 * @package     Banner
 */
$actions = array();

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
