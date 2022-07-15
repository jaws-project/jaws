<?php
/**
 * Quotes Actions file
 *
 * @category    GadgetActions
 * @package     Quotes
 */
/**
 * Index actions
 */
$actions['display'] = array(
    'normal' => true,
    'file' => 'Quotes',
    'navigation' => array(
        'order' => 0
    ),
);
$actions['doPayment'] = array(
    'standalone' => true,
    'file' => 'Quotes',
);

/**
 * Admin actions
 */
$admin_actions['quotes'] = array(
    'normal' => true,
    'file' => 'Quotes',
);
$admin_actions['getQuotes'] = array(
    'standalone' => true,
    'file' => 'Quotes',
);
$admin_actions['getQuote'] = array(
    'standalone' => true,
    'file' => 'Quotes',
);
$admin_actions['insertQuote'] = array(
    'standalone' => true,
    'file' => 'Quotes',
);
$admin_actions['updateQuote'] = array(
    'standalone' => true,
    'file' => 'Quotes',
);
$admin_actions['deleteQuote'] = array(
    'standalone' => true,
    'file' => 'Quotes',
);

$admin_actions['categories'] = array(
    'normal' => true,
    'file' => 'Categories',
);
