<?php
/**
 * Upgrade stage list
 *
 * @category   Application
 * @package    Upgrade
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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

// Upgrade from 0.9 to 1.0.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('0.9', '1.0.0'),
    'file' => '09To100',
);

// Upgrade from 1.0.0 to 1.1.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.0.0', '1.1.0'),
    'file' => '100To110',
);

// Upgrade from 1.1.0 to 1.1.1
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.1.0', '1.1.1'),
    'file' => '110To111',
);

// Upgrade from 1.1.1 to 1.2.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.1.1', '1.2.0'),
    'file' => '111To120',
);

// Upgrade from 1.2.0 to 1.3.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.2.0', '1.3.0'),
    'file' => '120To130',
);

// Upgrade from 1.3.0 to 1.4.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.3.0', '1.4.0'),
    'file' => '130To140',
);
// Upgrade from 1.4.0 to 1.5.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.4.0', '1.5.0'),
    'file' => '140To150',
);
// Upgrade from 1.5.0 to 1.6.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.5.0', '1.6.0'),
    'file' => '150To160',
);
// Upgrade from 1.6.0 to 1.7.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.6.0', '1.7.0'),
    'file' => '160To170',
);
// Upgrade from 1.7.0 to 1.8.0
$stages[] = array(
    'name' => 'VER_TO_VER',
    'vars' => array('1.7.0', '1.8.0'),
    'file' => '170To180',
);
// Saves the config file.
$stages[] = array(
    'file' => 'WriteConfig',
);

// Everything done! Go log in :)
$stages[] = array(
    'file' => 'Finished',
);