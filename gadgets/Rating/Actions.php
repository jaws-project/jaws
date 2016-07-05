<?php
/**
 * Rating Actions
 *
 * @category    GadgetActions
 * @package     Rating
 */

/**
 * Index actions
 */
$actions['PostRating'] = array(
    'standalone' => true,
    'file' => 'Rating',
);

$actions['MostRatted'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Rating',
);

/**
 * Admin actions
 */
$admin_actions['Rating'] = array(
    'normal' => true,
    'file'   => 'Rating',
);
