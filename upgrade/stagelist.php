<?php
/**
 * Upgrade stage list
 *
 * @category   Application
 * @package    Upgrade
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
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

// Upgrade from 0.8.14 to 0.8.15
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.8.14', '0.8.15'),
    'file'  => '0814To0815',
);

// Upgrade from 0.8.15 to 0.8.16
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.8.15', '0.8.16'),
    'file'  => '0815To0816',
);

// Upgrade from 0.8.16 to 0.8.17
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.8.16', '0.8.17'),
    'file'  => '0816To0817',
);

// Upgrade from 0.8.17 to 0.8.18
$stages[] = array(
    'name'  => _t('UPGRADE_VER_TO_VER', '0.8.17', '0.8.18'),
    'file'  => '0817To0818',
);

// Saves the config file.
$stages[] = array(
    'name'  => _t('UPGRADE_WRITECONFIG'),
    'file'  => 'WriteConfig',
);

// Everything's done! Go log in :)
$stages[] = array(
    'name'  => _t('UPGRADE_FINISHED'),
    'file'  => 'Finished',
);