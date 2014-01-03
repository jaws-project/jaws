<?php
/**
 * LinkDump Actions file
 *
 * @category    GadgetActions
 * @package     LinkDump
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Link'] = array(
    'normal' => true,
    'file' => 'Link'
);
$actions['Category'] = array(
    'normal' => true,
    'layout' => true,
    'parametric' => true,
    'file' => 'Groups'
);
$actions['Categories'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Groups'
);
$actions['RSS'] = array(
    'standalone' => true,
    'file' => 'Feeds'
);

/**
 * Admin actions
 */
$admin_actions['LinkDump'] = array(
    'normal' => true,
    'file'   => 'LinkDump'
);
$admin_actions['GetGroupUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetLinkUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetLink'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroups'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetLinksList'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertLink'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateLink'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteLink'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
