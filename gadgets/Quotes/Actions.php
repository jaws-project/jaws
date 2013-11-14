<?php
/**
 * Quotes Actions file
 *
 * @category    GadgetActions
 * @package     Quotes
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Display'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Quotes',
);
$actions['RecentQuotes'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Quotes',
);
$actions['ViewQuote'] = array(
    'normal' => true,
    'file'   => 'Quotes',
);
$actions['ViewGroupQuotes'] = array(
    'normal' => true,
    'file'   => 'Groups',
);
$actions['QuotesByGroup'] = array(
    'standalone' => true,
    'file'   => 'Quotes',
);

/**
 * Admin actions
 */
$admin_actions['Quotes'] = array(
    'normal' => true,
    'file'   => 'Quotes',
);
$admin_actions['QuoteGroups'] = array(
    'normal' => true,
    'file'   => 'Groups',
);
