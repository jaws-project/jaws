<?php
/**
 * Upgrade stage list
 *
 * @category   Application
 * @package    Upgrade
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$stages = array();

// Displays a brief introduction
$stages[] = array(
    'name'  => _t('UPGRADE_INTRODUCTION'),
    'file'  => 'Introduction',
);

// Authenticate user.
$stages[] = array(
    'name'  => _t('UPGRADE_AUTHENTICATION'),
    'file'  => 'Authentication',
);

// Filesystem permission checks.
$stages[] = array(
    'name'  => _t('UPGRADE_REQUIREMENTS'),
    'file'  => 'Requirements',
);

// Database setup and population.
$stages[] = array(
    'name'    => _t('UPGRADE_DATABASE'),
    'file'    => 'Database',
    'options' => $db,
);

// cleanup files & directories
$stages[] = array(
    'name'  => _t('UPGRADE_CLEANUP'),
    'file'  => 'Cleanup',
);

// Report.
$stages[] = array(
    'name'  => _t('UPGRADE_REPORT'),
    'file'  => 'Report',
);

// Upgrade from 0.9 to 1.0.0
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.9', '1.0.0'),
    'file'  => '09To100',
);

// Upgrade from 1.0.0 to 1.1.0
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '1.0.0', '1.1.0'),
    'file'  => '100To110',
);

// Upgrade from 1.1.0 to 1.1.1
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '1.1.0', '1.1.1'),
    'file'  => '110To111',
);

// Upgrade from 1.1.1 to 1.2.0
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '1.1.1', '1.2.0'),
    'file'  => '111To120',
);

// Upgrade from 1.2.0 to 1.3.0
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '1.2.0', '1.3.0'),
    'file'  => '120To130',
);

// Saves the config file.
$stages[] = array(
    'name'  => _t('UPGRADE_WRITECONFIG'),
    'file'  => 'WriteConfig',
);

// Everything done! Go log in :)
$stages[] = array(
    'name'  => _t('UPGRADE_FINISHED'),
    'file'  => 'Finished',
);