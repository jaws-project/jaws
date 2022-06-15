<?php
/**
 * Install stage list
 *
 * @category   Application
 * @package    Install
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$stages = array();

// Displays a brief introduction
$stages[] = array(
    'file'  => 'Introduction',
);

// Authenticate user.
$stages[] = array(
    'file'  => 'Authentication',
);

// Customize install directories
$stages[] = array(
    'file'  => 'Customize',
);

// Filesystem permission checks.
$stages[] = array(
    'file'  => 'Requirements',
);

// Database setup and population.
$stages[] = array(
    'file'  => 'Database',
);

// Creates a default user.
$stages[] = array(
    'file'  => 'CreateUser',
);

// Does assorted stuff, such as a default gadget.
$stages[] = array(
    'file'  => 'Settings',
);

// Saves the config file.
$stages[] = array(
    'file'  => 'WriteConfig',
);

// Everything's done! Go log in :)
$stages[] = array(
    'file'  => 'Finished',
);