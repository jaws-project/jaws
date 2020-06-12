<?php
/**
 * Settings Actions
 *
 * @category    GadgetActions
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$actions['UpdateSettings'] = array(
    'standalone' => true,
    'file' => 'Settings',
);
$actions['GetProvinces'] = array(
    'standalone' => true,
    'file'   => 'Zones',
);
$actions['GetCities'] = array(
    'standalone' => true,
    'file'   => 'Zones',
);
$actions['HealthStatus'] = array(
    'global' => true,
    'standalone' => true,
    'file'   => 'Settings',
);
$actions['ServiceWorker'] = array(
    'standalone' => true,
    'global' => true,
    'file'   => 'ServiceWorker',
);
$actions['Manifest'] = array(
    'standalone' => true,
    'global' => true,
    'file'   => 'ServiceWorker',
);
$actions['Offline'] = array(
    'normal' => true,
    'file'   => 'ServiceWorker',
);
$actions['CleanupExpiredCache'] = array(
    'standalone' => true,
    'global' => true,
    'file'   => 'Cache',
);

/**
 * Admin actions
 */
$admin_actions['BasicSettings'] = array(
    'normal' => true,
    'file' => 'Basic',
);
$admin_actions['AdvancedSettings'] = array(
    'normal' => true,
    'file' => 'Advanced',
);
$admin_actions['MetaSettings'] = array(
    'normal' => true,
    'file' => 'Meta',
);
$admin_actions['MailSettings'] = array(
    'normal' => true,
    'file' => 'Mail',
);
$admin_actions['FTPSettings'] = array(
    'normal' => true,
    'file' => 'FTP',
);
$admin_actions['ProxySettings'] = array(
    'normal' => true,
    'file' => 'Proxy',
);
$admin_actions['UpdateBasicSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateAdvancedSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateMetaSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateMailSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateFTPSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateProxySettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['GetProvinces'] = array(
    'standalone' => true,
    'file'   => 'Zones',
);
$admin_actions['GetCities'] = array(
    'standalone' => true,
    'file'   => 'Zones',
);