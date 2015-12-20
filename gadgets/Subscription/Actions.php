<?php
/**
 * Subscription Actions
 *
 * @category    GadgetActions
 * @package     Subscription
 */

/**
 * Index actions
 */
$actions['Subscription'] = array(
    'normal' => true,
    'file' => 'Subscription',
);

$actions['ShowSubscription'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Subscription',
);
$actions['UpdateSubscription'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Subscription',
);
$actions['UpdateGadgetSubscription'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Subscription',
);

/**
 * Admin actions
 */
$admin_actions['Subscription'] = array(
    'normal' => true,
    'file' => 'Subscription',
);
$admin_actions['GetSubscriptions'] = array(
    'standalone' => true,
    'file' => 'Subscription',
);
$admin_actions['GetSubscriptionsCount'] = array(
    'standalone' => true,
    'file' => 'Subscription',
);
$admin_actions['DeleteSubscriptions'] = array(
    'standalone' => true,
    'file' => 'Subscription',
);