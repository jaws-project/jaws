<?php
/**
 * Components Actions
 *
 * @category    GadgetActions
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['Version'] = array(
    'standalone' => true,
    'file' => 'Version',
);

/**
 * Admin actions
 */
$admin_actions['Gadgets'] = array(
    'normal' => true,
    'file' => 'Gadgets',
);
$admin_actions['Plugins'] = array(
    'normal' => true,
    'file' => 'Plugins',
);
$admin_actions['InstallGadget'] = array(
    'normal' => true,
    'file' => 'GadgetInstaller',
);
$admin_actions['UpgradeGadget'] = array(
    'normal' => true,
    'file' => 'GadgetInstaller',
);
$admin_actions['UninstallGadget'] = array(
    'normal' => true,
    'file' => 'GadgetInstaller',
);
