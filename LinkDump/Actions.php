<?php
/**
 * LinkDump Actions file
 *
 * @category    GadgetActions
 * @package     LinkDump
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Link'] = array(
    'normal' => true,
    'file' => 'Link'
);
$actions['Archive'] = array(
    'normal' => true,
    'file' => 'Archive'
);
$actions['Group'] = array(
    'normal' => true,
    'file' => 'Archive'
);
$actions['Tag'] = array(
    'normal' => true,
    'file' => 'Tag'
);
$actions['Display'] = array(
    'layout' => true,
    'parametric' => true,
    'file' => 'Display'
);
$actions['ShowCategories'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Categories'
);
$actions['ShowTagCloud'] = array(
    'layout' => true,
    'file' => 'TagCloud'
);
$actions['RSS'] = array(
    'standalone' => true,
    'file' => 'Feeds'
);