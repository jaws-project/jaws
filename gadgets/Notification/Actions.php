<?php
/**
 * Notification Actions
 *
 * @category    GadgetActions
 * @package     Notification
 */

/**
 * Index actions
 */
$actions['SendNotifications'] = array(
    'global' => true,
    'standalone' => true,
    'file' => 'Notification',
);
$actions['UpdateWebPushSubscription'] = array(
    'standalone' => true,
    'file'   => 'Notification',
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
$admin_actions['NotificationDrivers'] = array(
    'normal' => true,
    'file' => 'NotificationDrivers',
);
$admin_actions['GetNotificationDrivers'] = array(
    'standalone' => true,
    'file' => 'NotificationDrivers',
);
$admin_actions['GetNotificationDriver'] = array(
    'standalone' => true,
    'file' => 'NotificationDrivers',
);
$admin_actions['GetNotificationDriverSettingsUI'] = array(
    'standalone' => true,
    'file' => 'NotificationDrivers',
);
$admin_actions['UpdateNotificationDriver'] = array(
    'standalone' => true,
    'file' => 'NotificationDrivers',
);
$admin_actions['InstallNotificationDriver'] = array(
    'standalone' => true,
    'file' => 'NotificationDrivers',
);
$admin_actions['UninstallNotificationDriver'] = array(
    'standalone' => true,
    'file' => 'NotificationDrivers',
);
$admin_actions['Messages'] = array(
    'normal' => true,
    'file' => 'Messages',
);
$admin_actions['GetMessages'] = array(
    'standalone' => true,
    'file' => 'Messages',
);
$admin_actions['GetMessage'] = array(
    'standalone' => true,
    'file' => 'Messages',
);
$admin_actions['DeleteMessage'] = array(
    'standalone' => true,
    'file' => 'Messages',
);
