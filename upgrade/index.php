<?php
/**
 * Jaws Upgrade System
 *
 * @category   Application
 * @package    Upgrade
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/* Dummy way for developers to get the errors
 * Turn off when releasing.
 */
define('JAWS_SCRIPT',  'upgrade');
define('BASE_SCRIPT',  'upgrade/index.php');
define('JAWS_APPTYPE', 'Web');

if (!defined('JAWS_WIKI')) {
    define('JAWS_WIKI', 'http://dev.jaws-project.com/wiki/');
}
if (!defined('JAWS_WIKI_FORMAT')) {
    define('JAWS_WIKI_FORMAT', '{url}{lang}/{page}');
}

session_start();

if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
    date_default_timezone_set('UTC');
}

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config/JawsConfig.php';

if (!defined('PEAR_PATH')) {
define('PEAR_PATH', JAWS_PATH . 'libraries/pear/');
}

// lets setup the include_path
set_include_path('.' . PATH_SEPARATOR . JAWS_PATH . 'libraries/pear');
// JAWS_DATA
if (!defined('JAWS_DATA')) {
    define('JAWS_DATA', JAWS_PATH . 'data'. DIRECTORY_SEPARATOR);
} else {
    $_SESSION['JAWS_BASE_DATA'] = JAWS_DATA;
}
// JAWS_BASE_DATA
if (!defined('JAWS_BASE_DATA')) {
    define('JAWS_BASE_DATA', JAWS_DATA);
} else {
    $_SESSION['JAWS_BASE_DATA'] = JAWS_BASE_DATA;
}
// JAWS_THEMES
if (!defined('JAWS_THEMES')) {
    define('JAWS_THEMES', JAWS_DATA. 'themes'. DIRECTORY_SEPARATOR);
} else {
    $_SESSION['JAWS_THEMES'] = JAWS_THEMES;
}
// JAWS_BASE_THEMES
if (!defined('JAWS_BASE_THEMES')) {
    define('JAWS_BASE_THEMES', JAWS_THEMES);
} else {
    $_SESSION['JAWS_BASE_THEMES'] = JAWS_BASE_THEMES;
}
// JAWS_CACHE
if (!defined('JAWS_CACHE')) {
    define('JAWS_CACHE', JAWS_DATA. 'cache'. DIRECTORY_SEPARATOR);
} else {
    $_SESSION['JAWS_CACHE'] = JAWS_CACHE;
}
define('UPGRADE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// Initialize the logger
$_SESSION['use_log'] = isset($_SESSION['use_log'])? $_SESSION['use_log']: false;
$logger = array('method'  => 'LogToFile',
                'options' => array('file' => JAWS_DATA . 'logs/.upgrade.log'));
require JAWS_PATH . 'include/Jaws/Log.php';
$GLOBALS['log'] = new Jaws_Log($_SESSION['use_log'], $logger);
$GLOBALS['log']->Start();

// Lets support older PHP versions so we can use spanking new functions
require_once JAWS_PATH . 'include/Jaws/PHPFunctions.php';

require_once JAWS_PATH . 'include/Jaws/Const.php';
require_once JAWS_PATH . 'include/Jaws/Error.php';
require_once JAWS_PATH . 'include/Jaws/Utils.php';
require_once JAWS_PATH . 'include/Jaws/Gadget.php';

if (!isset($_SESSION['upgrade'])) {
    $_SESSION['upgrade'] = array('stage' => 0, 'lastStage' => array());
}

// Lets handle our requests
require JAWS_PATH . 'include/Jaws/Request.php';
$request = Jaws_Request::getInstance();
$lang = $request->fetch('language', 'post');
if (isset($lang)) {
    $_SESSION['upgrade']['language'] = urlencode($lang);
} elseif (!isset($_SESSION['upgrade']['language'])) {
    $_SESSION['upgrade']['language'] = 'en';
}

include_once JAWS_PATH . 'include/Jaws/Translate.php';
$objTranslate = Jaws_Translate::getInstance(false);
if (isset($_SESSION['upgrade']['language'])) {
    $objTranslate->SetLanguage($_SESSION['upgrade']['language']);
}
$objTranslate->LoadTranslation('Global');
$objTranslate->LoadTranslation('Upgrade', JAWS_COMPONENT_UPGRADE);

require_once 'stagelist.php';
require_once 'JawsUpgrader.php';
require_once 'JawsUpgraderStage.php';

$upgrader = new JawsUpgrader($db);
$upgrader->loadStages($stages);
$stages = $upgrader->getStages();
$stage = $stages[$_SESSION['upgrade']['stage']];

$stageobj = $upgrader->loadStage($stage);
$stages_count = count($stages);

$go_next_step = $request->fetch($stage['file'] . '_complete', 'post');
// Only attempt to validate if the next button has been hit.
if (isset($go_next_step)) {
    $result = $stageobj->validate();
    if (!Jaws_Error::isError($result)) {
        $result = $stageobj->run();

        if (!Jaws_Error::isError($result)) {
            if ($_SESSION['upgrade']['stage'] < $stages_count - 1) {
                $_SESSION['upgrade']['stage']++;
                $stageobj = $upgrader->loadStage($stages[$_SESSION['upgrade']['stage']]);
            }

            $result = null;
        }
    }

    $GLOBALS['message'] = $result;
}

// Mark the stage as having been run.
$_SESSION['upgrade']['lastStage'] = $_SESSION['upgrade']['stage'];

include_once JAWS_PATH . 'include/Jaws/Template.php';
$tpl = new Jaws_Template(false, false);
$tpl->Load('page.html', 'templates');
$tpl->SetBlock('page');
$tpl->SetVariable('title', $stages[$_SESSION['upgrade']['stage']]['name']);
$tpl->SetVariable('body',  $stageobj->display());
$tpl->SetVariable('stage', $stages[$_SESSION['upgrade']['stage']]['file']);

foreach ($stages as $key => $stage) {
    if ($key < $_SESSION['upgrade']['stage']) {
        $tpl->SetBlock('page/completed_stage');
        $tpl->SetVariable('name', $stage['name']);
        $tpl->ParseBlock('page/completed_stage');
    } elseif ($key == $_SESSION['upgrade']['stage']) {
        $tpl->SetBlock('page/current_stage');
        $tpl->SetVariable('name', $stage['name']);
        $tpl->ParseBlock('page/current_stage');
    } else {
        $tpl->SetBlock('page/stage');
        $tpl->SetVariable('name', $stage['name']);
        $tpl->ParseBlock('page/stage');
    }
}

// Check if we are on the last stage, Key + 1 because an array starts with 0 :-)
if (($_SESSION['upgrade']['stage'] + 1) == $stages_count) {
    // Kill of the session cookie (path cookie in FF)
    unset($_SESSION['upgrade']);
}

if (isset($GLOBALS['message'])) {
    switch ($GLOBALS['message']->getLevel()) {
        case JAWS_ERROR_INFO:
            $type = 'info';
            break;
        case JAWS_ERROR_WARNING:
            $type = 'warning';
            break;
        case JAWS_ERROR_ERROR:
            $type = 'error';
            break;
    }

    $tpl->setBlock('page/message');
    $tpl->setVariable('text', $GLOBALS['message']->getMessage());
    $tpl->setVariable('type', $type);
    $tpl->parseBlock('page/message');
}
$tpl->ParseBlock('page');

// Defines where the layout template should be loaded from.
$direction = _t('GLOBAL_LANG_DIRECTION');
$dir  = $direction == 'rtl' ? '.' . $direction : '';

// Display the layout
$layout = new Jaws_Template(false, false);
$layout->Load('layout.html', 'templates');
$layout->SetBlock('layout');

// Basic setup
$layout->SetVariable('BASE_URL', Jaws_Utils::getBaseURL('/upgrade/'));
$layout->SetVariable('.dir', $dir);
$layout->SetVariable('site-title', 'Jaws ' . JAWS_VERSION);
$layout->SetVariable('site-name',  'Jaws ' . JAWS_VERSION);
$layout->SetVariable('site-slogan', JAWS_VERSION_CODENAME);

// Load js files
$layout->SetBlock('layout/head');
$layout->SetVariable('ELEMENT', '<script type="text/javascript" src="../libraries/js/jsencrypt.min.js"></script>');
$layout->ParseBlock('layout/head');

// Display the stage
$layout->SetBlock('layout/main');
$layout->SetVariable('ELEMENT', $tpl->Get());
$layout->ParseBlock('layout/main');
$layout->ParseBlock('layout');

echo $layout->Get();
