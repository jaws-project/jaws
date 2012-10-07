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
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */

if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
    date_default_timezone_set('UTC');
}

// Initialize the logger
require JAWS_PATH . 'include/Jaws/Log.php';
$GLOBALS['log'] = new Jaws_Log(defined('DEBUG_ACTIVATED')? DEBUG_ACTIVATED : false,
                               isset($GLOBALS['logger'])? $GLOBALS['logger'] : null);
$GLOBALS['log']->Start();

// initial common constants like version number
require JAWS_PATH . 'include/Jaws/Const.php';

// for availability Jaws_Utils methods
require_once JAWS_PATH . 'include/Jaws/Utils.php';

// Get our error bling bling going.
require JAWS_PATH . 'include/Jaws/Error.php';

if (!defined('JAWS_BASE_DATA')) {
    define('JAWS_BASE_DATA', JAWS_PATH . 'data'. DIRECTORY_SEPARATOR);
}
if (!defined('JAWS_DATA')) {
    define('JAWS_DATA', JAWS_BASE_DATA);
}
if (!defined('JAWS_CACHE')) {
    define('JAWS_CACHE', JAWS_DATA. 'cache');
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

// Lets handle our requests
require JAWS_PATH . 'include/Jaws/Request.php';
$request =& Jaws_Request::getInstance();

// Add request filters
///FIXME these should only be added in the web bootstrappers
$request->addFilter('htmlstrip', 'strip_tags');
$request->addFilter('htmlclean', 'htmlspecialchars', array(ENT_QUOTES, 'UTF-8'));
$request->addFilter('ambiguous', array('Jaws_Request', 'strip_ambiguous'));

// Connect to the database
require JAWS_PATH . 'include/Jaws/DB.php';

// for fix bug in Jaws 0.7.x
if (isset($db['charset']) && $db['charset'] == 'UTF-8') {
    $db['charset'] = '';
}

$GLOBALS['db'] =& Jaws_DB::getInstance($db);
#if (Jaws_Error::IsError($GLOBALS['db'])) {
#    Jaws_Error::Fatal('Couldn\'t connect to database');
#}

// Create application
require_once JAWS_PATH . 'include/Jaws.php';
$GLOBALS['app'] = new Jaws();
$GLOBALS['app']->loadClass('Registry', 'Jaws_Registry');
$GLOBALS['app']->Registry->Init();
if ($GLOBALS['app']->Registry->Get('/version') != JAWS_VERSION) {
    Jaws_Header::Location('upgrade/index.php', true);
}
$GLOBALS['app']->create();

require_once JAWS_PATH . 'include/Jaws/InitPiwi.php';
