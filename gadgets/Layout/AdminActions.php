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

$actions['LayoutManager'] = array(
    'standalone' => true,
    'normal' => true,
    'file' => 'Layout',
);
$actions['ChangeTheme'] = array(
    'standalone' => true,
    'file' => 'Theme',
);
$actions['LayoutSwitch'] = array(
    'standalone' => true,
    'file' => 'Layout',
);
$actions['EditElementAction'] = array(
    'standalone' => true,
    'file' => 'Element',
);
$actions['ChangeDisplayWhen'] = array(
    'standalone' => true,
    'file' => 'When',
);
$actions['AddLayoutElement'] = array(
    'standalone' => true,
    'file' => 'Element',
);