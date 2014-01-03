<?php
/**
 * Languages Actions
 *
 * @category    GadgetActions
 * @package     Languages
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Admin actions
 */
$admin_actions['Languages'] = array(
    'normal' => true,
    'file' => 'Languages'
);
$admin_actions['Export'] = array(
    'standalone' => true,
    'file' => 'Export'
);
$admin_actions['SaveLanguage'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetLangDataUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SetLangData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
