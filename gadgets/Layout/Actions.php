<?php
/**
 * Layout Actions file
 *
 * @category    GadgetActions
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['Layout'] = array(
    'standalone' => true,
    'normal' => true,
    'file' => 'Layout',
);
$actions['AddLayoutElement'] = array(
    'standalone' => true,
    'file' => 'Element',
);
$actions['MoveElement'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$actions['DeleteElement'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$actions['DisplayWhen'] = array(
    'standalone' => true,
    'file' => 'DisplayWhen',
);
$actions['UpdateDisplayWhen'] = array(
    'standalone' => true,
    'file' => 'DisplayWhen',
);
$actions['GetGadgetActions'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$actions['AddGadget'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$actions['ElementAction'] = array(
    'standalone' => true,
    'file' => 'Element',
);
$actions['UpdateElementAction'] = array(
    'standalone' => true,
    'file' => 'Element',
);
$actions['Dashboard'] = array(
    'standalone' => true,
    'file' => 'Dashboard',
);

/**
 * Admin actions
 */
$admin_actions['Layout'] = array(
    'normal' => true,
    'file' => 'Layout',
);