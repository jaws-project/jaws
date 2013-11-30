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
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Terminate script
 *
 * @param   mixed   $data   Response data
 * @param   bool    $sync   Synchronize session
 * @return  void
 */
function terminate(&$data = null, $status_code = 200, $next_location = '', $sync = true)
{
    // Send content to client
    $resType = jaws()->request->fetch('restype');
    switch ($resType) {
        case 'json':
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            if (in_array($status_code, array(301, 302))) {
                $data = $GLOBALS['app']->Session->PopResponse($data);
            }
            // Sync session
            if ($sync && isset($GLOBALS['app'])) {
                $GLOBALS['app']->Session->Synchronize();
            }

            echo Jaws_UTF8::json_encode($data);
            break;

        case 'gzip':
        case 'x-gzip':
            $data = gzencode($data, COMPRESS_LEVEL, FORCE_GZIP);
            header('Content-Length: '.strlen($data));
            header('Content-Encoding: '. $resType);
        default:
            // Sync session
            if ($sync && isset($GLOBALS['app']->Session)) {
                $GLOBALS['app']->Session->Synchronize();
            }

            switch ($status_code) {
                case 301:
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: '.$next_location);
                    break;
                case 302:
                    header('HTTP/1.1 302 Found');
                    header('Location: '.$next_location);
                    break;
                default:
            }

            echo $data;
    }

    if (isset($GLOBALS['log'])) {
        $GLOBALS['log']->End();
    }

    exit;
}

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

if (!defined('JAWS_BASE_DATA')) {
    define('JAWS_BASE_DATA',
           defined('JAWS_DATA')? JAWS_DATA : (JAWS_PATH . 'data'. DIRECTORY_SEPARATOR));
}
if (!defined('JAWS_DATA')) {
    define('JAWS_DATA', JAWS_BASE_DATA);
}
if (!defined('JAWS_BASE_THEMES')) {
    define('JAWS_BASE_THEMES',
           defined('JAWS_THEMES')? JAWS_THEMES: (JAWS_DATA. 'themes'. DIRECTORY_SEPARATOR));
}
if (!defined('JAWS_THEMES')) {
    define('JAWS_THEMES', JAWS_BASE_THEMES);
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
        Jaws_Header::Location('upgrade/index.php');
    }

    $GLOBALS['app']->Registry->update('version', JAWS_VERSION);
}

// init application
$GLOBALS['app']->init();

// load Piwi initialize
require_once JAWS_PATH . 'include/Jaws/InitPiwi.php';
