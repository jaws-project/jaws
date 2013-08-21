<?php
/**
 * StaticPage Actions file
 *
 * @category    GadgetActions
 * @package     StaticPage
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['GroupPages'] = array(
    'normal' => true,
    'layout' => true,
    'parametric' => true,
    'file'   => 'Group',
);
$actions['PagesList'] = array(
    'layout' => true,
    'file'   => 'Page',
);
$actions['GroupsList'] = array(
    'layout' => true,
    'file'   => 'Group',
);
$actions['Page'] = array(
    'normal' => true,
    'file'   => 'Page',
);
$actions['Pages'] = array(
    'normal' => true,
    'file'   => 'Page',
);
$actions['PagesTree'] = array(
    'normal' => true,
    'file'   => 'Page',
);
