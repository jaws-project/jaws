<?php
/**
 * Initiates all the whole JawsApplication stuff.
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */

// set default timezone to utc
date_default_timezone_set('UTC');

if (!defined('PEAR_PATH')) {
    define('PEAR_PATH', JAWS_PATH . 'libraries/pear/');
}

// Initialize the logger
require JAWS_PATH . 'include/Jaws/Log.php';
$GLOBALS['log'] = new Jaws_Log(defined('LOG_ACTIVATED')? LOG_ACTIVATED : false,
                               isset($GLOBALS['logger'])? $GLOBALS['logger'] : null);
$GLOBALS['log']->Start();

// initial common constants like version number
require JAWS_PATH . 'include/Jaws/Const.php';

// for availability Jaws_Utils methods
require_once JAWS_PATH . 'include/Jaws/Utils.php';

// Get our error bling bling going.
require JAWS_PATH . 'include/Jaws/Error.php';

if (!defined('JAWS_DATA')) {
    define('JAWS_DATA', JAWS_PATH . 'data'. DIRECTORY_SEPARATOR);
}
if (!defined('JAWS_BASE_DATA')) {
    define('JAWS_BASE_DATA', JAWS_DATA);
}
if (!defined('JAWS_THEMES')) {
    define('JAWS_THEMES', JAWS_DATA. 'themes'. DIRECTORY_SEPARATOR);
}
if (!defined('JAWS_BASE_THEMES')) {
    define('JAWS_BASE_THEMES', JAWS_BASE_DATA. 'themes'. DIRECTORY_SEPARATOR);
}
if (!defined('JAWS_CACHE')) {
    define('JAWS_CACHE', JAWS_DATA. 'cache'. DIRECTORY_SEPARATOR);
}

if (!defined('JAWS_WIKI')) {
    define('JAWS_WIKI', 'http://dev.jaws-project.com/wiki');
}
if (!defined('JAWS_WIKI_FORMAT')) {
    define('JAWS_WIKI_FORMAT', '{url}/{lang}/{lower-type}/{page}');
}

if (!defined('COMPRESS_LEVEL')) {
    define('COMPRESS_LEVEL', 4);
}

// Lets support older PHP versions so we can use spanking new functions
require JAWS_PATH . 'include/Jaws/PHPFunctions.php';

// lets setup the include_path
set_include_path('.' . PATH_SEPARATOR . JAWS_PATH . 'libraries/pear');

// Create application
$GLOBALS['app'] = jaws();
// get an instance of Jaws_DB
$GLOBALS['db'] = Jaws_DB::getInstance($db);
if (Jaws_Error::IsError($GLOBALS['db'])) {
    Jaws_Error::Fatal($GLOBALS['db']->getMessage());
}

$db_jaws_version = $GLOBALS['app']->Registry->Init();
if ($db_jaws_version != JAWS_VERSION) {
    if (strrstr(JAWS_VERSION, '.', true) != strrstr($db_jaws_version, '.', true)) {
        //require_once JAWS_PATH . 'upgrade/JawsUpgrader.php';
        //require_once JAWS_PATH . 'upgrade/JawsUpgraderStage.php';
        //require_once JAWS_PATH . 'upgrade/stages/111To120.php';
        //$objStage = new Upgrader_111To120;
        //$result = $objStage->Run();
        //if (Jaws_Error::IsError($result)) {
            Jaws_Header::Location('upgrade/index.php');
        //}
    }

    $GLOBALS['app']->Registry->update('version', JAWS_VERSION);
}

// init application
$GLOBALS['app']->init();

// load Piwi initialize
require_once JAWS_PATH . 'include/Jaws/InitPiwi.php';
