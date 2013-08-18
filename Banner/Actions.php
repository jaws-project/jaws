<?php
/**
 * Banner Actions file
 *
 * @category    GadgetActions
 * @package     Banner
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Banners'] = array(
    'layout' => true,
    'parametric' => true,
    'standalone' => true,
    'file' => 'Banners',
);
$actions['Click'] = array(
    'normal' => true,
    'file' => 'Banners',
);