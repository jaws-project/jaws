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
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['Plugins'] = array(
    'normal' => true,
    'file' => 'Plugins',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['InstallGadget'] = array(
    'normal' => true,
    'file' => 'GadgetInstaller',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpgradeGadget'] = array(
    'normal' => true,
    'file' => 'GadgetInstaller',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UninstallGadget'] = array(
    'normal' => true,
    'file' => 'GadgetInstaller',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['GetGadgets'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGadgetInfo'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InstallGadget2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpgradeGadget2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UninstallGadget2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['EnableGadget'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['DisableGadget'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['GetPlugins'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetPluginInfo'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InstallPlugin'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UninstallPlugin'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['GetPluginUsage'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdatePluginUsage'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['GetRegistry'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateRegistry'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['GetACL'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateACL'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
