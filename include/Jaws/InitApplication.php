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
 * @copyright  2005-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */

// set default timezone to utc
date_default_timezone_set('UTC');

if (!defined('PEAR_PATH')) {
    define('PEAR_PATH', ROOT_JAWS_PATH . 'libraries/pear/');
}

// initial common constants like version number
require ROOT_JAWS_PATH . 'include/Jaws/Const.php';

// Initialize the logger
require ROOT_JAWS_PATH . 'include/Jaws/Log.php';
$GLOBALS['log'] = new Jaws_Log(defined('LOG_ACTIVATED')? LOG_ACTIVATED : false,
                               isset($GLOBALS['logger'])? $GLOBALS['logger'] : null);
$GLOBALS['log']->Start();

// for availability Jaws_Utils methods
require_once ROOT_JAWS_PATH . 'include/Jaws/Utils.php';

// Get our error bling bling going.
require ROOT_JAWS_PATH . 'include/Jaws/Error.php';

if (!defined('ROOT_DATA_PATH')) {
    define('ROOT_DATA_PATH', ROOT_JAWS_PATH . 'data'. DIRECTORY_SEPARATOR);
}
if (!defined('JAWS_BASE_DATA')) {
    define('JAWS_BASE_DATA', ROOT_DATA_PATH);
}
if (!defined('JAWS_THEMES')) {
    define('JAWS_THEMES', ROOT_DATA_PATH. 'themes'. DIRECTORY_SEPARATOR);
}
if (!defined('JAWS_BASE_THEMES')) {
    define('JAWS_BASE_THEMES', JAWS_BASE_DATA. 'themes'. DIRECTORY_SEPARATOR);
}
if (!defined('JAWS_CACHE_PATH')) {
    define('JAWS_CACHE_PATH', ROOT_DATA_PATH. 'cache'. DIRECTORY_SEPARATOR);
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

if (!defined('JAWS_GODUSER')) {
    define('JAWS_GODUSER', 0);
}

// Lets support older PHP versions so we can use spanking new functions
require ROOT_JAWS_PATH . 'include/Jaws/Helper.php';

// lets setup the include_path
set_include_path('.' . PATH_SEPARATOR . ROOT_JAWS_PATH . 'libraries/pear');

// Create application
$jawsApp = Jaws::getInstance();
// get an instance of Jaws_DB
$objDatabase = Jaws_DB::getInstance('default', $db, isset($wdb)? $wdb : array());
if (Jaws_Error::IsError($objDatabase)) {
    Jaws_Error::Fatal($objDatabase->getMessage());
}

$db_jaws_version = $jawsApp->registry->init();
if ($db_jaws_version != JAWS_VERSION) {
    if (strrstr(JAWS_VERSION, '.', true) != strrstr($db_jaws_version, '.', true)) {
        //require_once ROOT_JAWS_PATH . 'upgrade/JawsUpgrader.php';
        //require_once ROOT_JAWS_PATH . 'upgrade/JawsUpgraderStage.php';
        //require_once ROOT_JAWS_PATH . 'upgrade/stages/111To120.php';
        //$objStage = new Upgrader_111To120;
        //$result = $objStage->Run();
        //if (Jaws_Error::IsError($result)) {
            Jaws_Header::Location('upgrade/index.php');
        //}
    }

    $jawsApp->registry->update('version', JAWS_VERSION);
}

// init application
$jawsApp->init();

// load Piwi initialize
require_once ROOT_JAWS_PATH . 'include/Jaws/InitPiwi.php';
