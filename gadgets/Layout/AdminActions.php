<?php
/**
 * Layout Actions
 *
 * @category    GadgetActions
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

$actions['ChangeTheme'] = array(
    'standalone' => true,
    'file' => 'Layout',
);
$actions['LayoutManager'] = array(
    'standalone' => true,
    'normal' => true,
    'file' => 'Layout',
);
$actions['LayoutBuilder'] = array(
    'normal' => true,
    'file' => 'Layout',
);
$actions['SetLayoutMode'] = array(
    'normal' => true,
    'file' => 'Layout',
);
$actions['EditElementAction'] = array(
    'standalone' => true,
    'file' => 'Layout',
);
$actions['ChangeDisplayWhen'] = array(
    'standalone' => true,
    'file' => 'Layout',
);
$actions['AddLayoutElement'] = array(
    'standalone' => true,
    'file' => 'Layout',
);
$actions['SaveAddLayoutElement'] = array(
    'normal' => true,
    'file' => 'Layout',
);
