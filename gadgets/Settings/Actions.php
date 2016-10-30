<?php
/**
 * Settings Actions
 *
 * @category    GadgetActions
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
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
