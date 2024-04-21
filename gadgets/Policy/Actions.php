<?php
/**
 * Policy Actions file
 *
 * @category    GadgetActions
 * @package     Policy
 * @author      Amir Mohammad Saied <amir@gluegadget.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['Captcha'] = array(
    'standalone' => true,
    'file' => 'Captcha',
    'global' => true,
    'temporary' => true,
);

/**
 * Admin actions
 */
$admin_actions['Zones'] = array(
    'normal' => true,
    'file' => 'Zone',
);
$admin_actions['GetZones'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['GetZoneRanges'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['GetZone'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['GetZoneRange'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['InsertZone'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['UpdateZone'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['DeleteZone'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['InsertZoneRange'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['UpdateZoneRange'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['DeleteZoneRange'] = array(
    'standalone' => true,
    'file' => 'Zone',
);

$admin_actions['ZoneActions'] = array(
    'normal' => true,
    'file' => 'Zone',
);
$admin_actions['GetZoneActions'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['GetZoneAction'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['InsertZoneAction'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['UpdateZoneAction'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['DeleteZoneAction'] = array(
    'standalone' => true,
    'file' => 'Zone',
);
$admin_actions['GetGadgetActions'] = array(
    'standalone' => true,
    'file' => 'Zone',
);

$admin_actions['IPBlocking'] = array(
    'normal' => true,
    'file' => 'IP',
);
$admin_actions['AgentBlocking'] = array(
    'normal' => true,
    'file' => 'Agent',
);
$admin_actions['Encryption'] = array(
    'normal' => true,
    'file' => 'Encryption',
);
$admin_actions['AntiSpam'] = array(
    'normal' => true,
    'file' => 'AntiSpam',
);
$admin_actions['AdvancedPolicies'] = array(
    'normal' => true,
    'file' => 'AdvancedPolicies',
);
$admin_actions['GetIPRange'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddIPRange'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['EditIPRange'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['DeleteIPRange'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['GetAgent'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddAgent'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['EditAgent'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['DeleteAgent'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['IPBlockingBlockUndefined'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['AgentBlockingBlockUndefined'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateEncryptionSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateAntiSpamSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateAdvancedPolicies'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['getData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
