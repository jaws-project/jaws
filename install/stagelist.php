<?php
/**
 * Install stage list
 *
 * @category   Application
 * @package    Install
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$stages = array();

// Displays a brief introduction
$stages[] = array(
    'name'  => _t('INSTALL_INTRODUCTION'),
    'file'  => 'Introduction',
);

// Authenticate user.
$stages[] = array(
    'name'  => _t('INSTALL_AUTHENTICATION'),
    'file'  => 'Authentication',
);

// Filesystem permission checks.
$stages[] = array(
    'name'  => _t('INSTALL_REQUIREMENTS'),
    'file'  => 'Requirements',
);

// Database setup and population.
$stages[] = array(
    'name'  => _t('INSTALL_DATABASE'),
    'file'  => 'Database',
);

// Creates a default user.
$stages[] = array(
    'name'  => _t('INSTALL_CREATEUSER'),
    'file'  => 'CreateUser',
);

// Does assorted stuff, such as a default gadget.
$stages[] = array(
    'name'  => _t('INSTALL_SETTINGS'),
    'file'  => 'Settings',
);

// Saves the config file.
$stages[] = array(
    'name'  => _t('INSTALL_WRITECONFIG'),
    'file'  => 'WriteConfig',
);

// Everything's done! Go log in :)
$stages[] = array(
    'name'  => _t('INSTALL_FINISHED'),
    'file'  => 'Finished',
);