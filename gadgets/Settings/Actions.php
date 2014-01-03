<?php
/**
 * Settings Actions
 *
 * @category    GadgetActions
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Admin actions
 */
$admin_actions['BasicSettings'] = array(
    'normal' => true,
    'file' => 'Basic',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['AdvancedSettings'] = array(
    'normal' => true,
    'file' => 'Advanced',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['MetaSettings'] = array(
    'normal' => true,
    'file' => 'Meta',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['MailSettings'] = array(
    'normal' => true,
    'file' => 'Mail',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['FTPSettings'] = array(
    'normal' => true,
    'file' => 'FTP',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['ProxySettings'] = array(
    'normal' => true,
    'file' => 'Proxy',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateBasicSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpdateAdvancedSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpdateMetaSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpdateMailSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpdateFTPSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpdateProxySettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
