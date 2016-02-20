<?php
/**
 * Activities Actions
 *
 * @category    GadgetActions
 * @package     Activities
 */

/**
 * Index actions
 */
$actions['Activities'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Activities',
);
$actions['GetData'] = array(
    'standalone' => true,
    'file' => 'Activities',
);
$actions['PostData'] = array(
    'standalone' => true,
    'file' => 'Activities',
);

/**
 * Admin actions
 */
$admin_actions['Activities'] = array(
    'normal' => true,
    'file' => 'Activities',
);
$admin_actions['GetSiteActivities'] = array(
    'standalone' => true,
    'file' => 'Activities',
);
$admin_actions['GetSiteActivitiesCount'] = array(
    'standalone' => true,
    'file' => 'Activities',
);
$admin_actions['DeleteSiteActivities'] = array(
    'standalone' => true,
    'file' => 'Activities',
);
$admin_actions['DeleteAllSiteActivities'] = array(
    'standalone' => true,
    'file' => 'Activities',
);
