<?php
/**
 * AbuseReporter Actions file
 *
 * @category    GadgetActions
 * @package     AbuseReporter
 */

/**
 * Index actions
 */
$actions['ReportUI'] = array(
    'standalone' => true,
    'file'   => 'Report'
);
$actions['SaveReport'] = array(
    'standalone' => true,
    'file'   => 'Report'
);

/**
 * Admin actions
 */
$admin_actions['Reports'] = array(
    'normal' => true,
    'file' => 'Reports',
);
$admin_actions['GetReports'] = array(
    'standalone' => true,
    'file' => 'Reports',
);
$admin_actions['GetReport'] = array(
    'standalone' => true,
    'file' => 'Reports',
);
$admin_actions['UpdateReport'] = array(
    'standalone' => true,
    'file' => 'Reports',
);
$admin_actions['DeleteReport'] = array(
    'standalone' => true,
    'file' => 'Reports',
);
