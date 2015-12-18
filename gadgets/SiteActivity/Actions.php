<?php
/**
 * SiteActivity Actions
 *
 * @category    GadgetActions
 * @package     SiteActivity
 */

/**
 * Index actions
 */
$actions['SiteActivity'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'SiteActivity',
);
$actions['ReceiveData'] = array(
    'standalone' => true,
    'file' => 'SiteActivity',
);
$actions['SendData'] = array(
    'standalone' => true,
    'file' => 'SiteActivity',
);

/**
 * Admin actions
 */
$admin_actions['SiteActivity'] = array(
    'normal' => true,
    'file' => 'SiteActivity',
);
$admin_actions['GetSiteActivities'] = array(
    'standalone' => true,
    'file' => 'SiteActivity',
);
$admin_actions['GetSiteActivitiesCount'] = array(
    'standalone' => true,
    'file' => 'SiteActivity',
);
$admin_actions['DeleteSiteActivities'] = array(
    'standalone' => true,
    'file' => 'SiteActivity',
);
$admin_actions['DeleteAllSiteActivities'] = array(
    'standalone' => true,
    'file' => 'SiteActivity',
);
