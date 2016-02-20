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
$admin_actions['GetActivities'] = array(
    'standalone' => true,
    'file' => 'Activities',
);
$admin_actions['GetActivitiesCount'] = array(
    'standalone' => true,
    'file' => 'Activities',
);
$admin_actions['DeleteActivities'] = array(
    'standalone' => true,
    'file' => 'Activities',
);
$admin_actions['DeleteAllActivities'] = array(
    'standalone' => true,
    'file' => 'Activities',
);
