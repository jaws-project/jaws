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

/**
 * Index actions
 */
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

/**
 * Admin actions
 */
$admin_actions['ManagePages'] = array(
    'normal' => true,
    'file'   => 'Page',
);
$admin_actions['AddPage'] = array(
    'normal' => true,
    'file'   => 'Page',
);
$admin_actions['AddNewPage'] = array(
    'normal' => true,
    'file'   => 'Page',
);
$admin_actions['EditPage'] = array(
    'normal' => true,
    'file'   => 'Page',
);
$admin_actions['SaveEditPage'] = array(
    'normal' => true,
    'file'   => 'Page',
);
$admin_actions['Groups'] = array(
    'normal' => true,
    'file'   => 'Group',
);
$admin_actions['Properties'] = array(
    'normal' => true,
    'file'   => 'Settings',
);
$admin_actions['AddNewTranslation'] = array(
    'normal' => true,
    'file'   => 'Translation',
);
$admin_actions['AddTranslation'] = array(
    'normal' => true,
    'file'   => 'Translation',
);
$admin_actions['EditTranslation'] = array(
    'normal' => true,
    'file'   => 'Translation',
);
$admin_actions['SaveEditTranslation'] = array(
    'normal' => true,
    'file'   => 'Translation',
);
