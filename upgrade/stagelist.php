<?php
/**
 * Upgrade stage list
 *
 * @category   Application
 * @package    Upgrade
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
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

// Report.
$stages[] = array(
    'name'  => _t('UPGRADE_REPORT'),
    'file'  => 'Report',
);

// Upgrade from 0.8 to 0.9.0 - First step
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.8.18', '0.9.0', _t('UPGRADE_VER_TO_VER_STEP1')),
    'file'  => '08To0901',
);

// Upgrade from 0.8 to 0.9.0 - Second step
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.8.18', '0.9.0', _t('UPGRADE_VER_TO_VER_STEP2')),
    'file'  => '08To0902',
);

// Upgrade from 0.8 to 0.9.0 - Third step
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.8.18', '0.9.0', _t('UPGRADE_VER_TO_VER_STEP3')),
    'file'  => '08To0903',
);

// Upgrade from 0.9.0 to 0.9.1
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.9.0', '0.9.1'),
    'file'  => '090To091',
);

// Upgrade from 0.9.1 to 0.9.2
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.9.1', '0.9.2'),
    'file'  => '091To092',
);
// Upgrade from 0.9.2 to 0.9.3
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.9.2', '0.9.3'),
    'file'  => '092To093',
);
// Saves the config file.
$stages[] = array(
    'name'  => _t('UPGRADE_WRITECONFIG'),
    'file'  => 'WriteConfig',
);

// cleanup files & directories
$stages[] = array(
    'name'  => _t('UPGRADE_CLEANUP'),
    'file'  => 'Cleanup',
);

// Everything's done! Go log in :)
$stages[] = array(
    'name'  => _t('UPGRADE_FINISHED'),
    'file'  => 'Finished',
);