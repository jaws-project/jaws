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
$admin_actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$admin_actions['SaveSettings'] = array(
    'standalone' => true,
    'file' => 'Settings',
);
