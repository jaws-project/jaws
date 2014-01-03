<?php
/**
 * Quotes Actions file
 *
 * @category    GadgetActions
 * @package     Quotes
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
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
$admin_actions['GetQuote'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetQuotes'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertQuote'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateQuote'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteQuote'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroup'] = array(
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
$admin_actions['GroupQuotesUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddQuotesToGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
