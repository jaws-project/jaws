<?php
/**
 * Layout Actions file
 *
 * @category    GadgetActions
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['LayoutSwitch'] = array(
    'standalone' => true,
    'file' => 'Layout',
);

/**
 * Admin actions
 */
$admin_actions['LayoutManager'] = array(
    'standalone' => true,
    'normal' => true,
    'file' => 'Layout',
);
$admin_actions['ChangeTheme'] = array(
    'standalone' => true,
    'file' => 'Theme',
);
$admin_actions['LayoutSwitch'] = array(
    'standalone' => true,
    'file' => 'Layout',
);
$admin_actions['EditElementAction'] = array(
    'standalone' => true,
    'file' => 'Element',
);
$admin_actions['ChangeDisplayWhen'] = array(
    'standalone' => true,
    'file' => 'When',
);
$admin_actions['AddLayoutElement'] = array(
    'standalone' => true,
    'file' => 'Element',
);
$admin_actions['MoveElement'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteElement'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ChangeDisplayWhen2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGadgetActions'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddGadget'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['EditElementAction2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
