<?php
/**
 * Search Actions file
 *
 * @category    GadgetActions
 * @package     Search
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['Box'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Search',
);
$actions['SimpleBox'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Search',
);
$actions['AdvancedBox'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Search',
);
$actions['Results'] = array(
    'normal' => true,
    'file'   => 'Results',
);

/**
 * Admin actions
 */
$admin_actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$admin_actions['SaveChanges'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
