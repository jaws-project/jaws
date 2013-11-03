<?php
/**
 * Components Actions
 *
 * @category    GadgetActions
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();
$actions['Gadgets'] = array(
    'normal' => true,
    'file' => 'Gadgets',
);
$actions['Plugins'] = array(
    'normal' => true,
    'file' => 'Plugins',
);
$actions['InstallGadget'] = array(
    'normal' => true,
    'file' => 'GadgetInstaller',
);
$actions['UpgradeGadget'] = array(
    'normal' => true,
    'file' => 'GadgetInstaller',
);
$actions['UninstallGadget'] = array(
    'normal' => true,
    'file' => 'GadgetInstaller',
);
