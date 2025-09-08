<?php
/**
 * Upgrade stage list
 *
 * @category    Application
 * @package     Upgrade
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$stages = array();

// Displays a brief introduction
$stages[] = array(
    'file' => 'Introduction',
);

// Authenticate user.
$stages[] = array(
    'file' => 'Authentication',
);

// Requirements checks.
$stages[] = array(
    'file' => 'Requirements',
);

// Database setup and population.
$stages[] = array(
    'file' => 'Database'
);

// cleanup files & directories
$stages[] = array(
    'file' => 'Cleanup',
);

// Report.
$stages[] = array(
    'file' => 'Report',
);

// Upgrade from 1.9.0 to 2.0.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.9.0', '2.0.0'),
    'file' => '190To200',
);
// Upgrade from 2.0.0 to 2.1.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('2.0.0', '2.1.0'),
    'file' => '200To210',
);
// Saves the config file.
$stages[] = array(
    'file' => 'WriteConfig',
);

// Everything done! Go log in :)
$stages[] = array(
    'file' => 'Finished',
);