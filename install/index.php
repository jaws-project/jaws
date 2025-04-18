<?php
/**
 * Jaws Installer System
 *
 * @category   Application
 * @package    Install
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Helgi �ormar �orbj�rnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/* Dummy way for developers to get the errors
 * Turn off when releasing.
 */
define('JAWS_SCRIPT',  'install');
define('BASE_SCRIPT',  'install/index.php');
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

define('ROOT_PATH', realpath($_SERVER['DOCUMENT_ROOT']) . '/');
define('JAWS_PATH', substr(dirname(__DIR__) . '/', strlen(ROOT_PATH)));
define('ROOT_JAWS_PATH', ROOT_PATH . JAWS_PATH);
define('PEAR_PATH', ROOT_JAWS_PATH . 'libraries/pear/');

// lets setup the include_path
set_include_path('.' . PATH_SEPARATOR . ROOT_JAWS_PATH . 'libraries/pear');

// this variables currently temporary until we complete multiple instance installing
// data path
define(
    'DATA_PATH',
    isset($_SESSION['DATA_PATH'])? $_SESSION['DATA_PATH'] : ('data/')
);
define('ROOT_DATA_PATH', ROOT_JAWS_PATH . DATA_PATH);
// base data path
define(
    'BASE_DATA_PATH',
    isset($_SESSION['BASE_DATA_PATH'])? $_SESSION['BASE_DATA_PATH'] : DATA_PATH
);
define('ROOT_BASE_DATA_PATH', ROOT_PATH . BASE_DATA_PATH);
// themes data path
define(
    'THEMES_PATH',
    isset($_SESSION['THEMES_PATH'])? $_SESSION['THEMES_PATH'] : (DATA_PATH. 'themes/')
);
define('ROOT_THEMES_PATH', ROOT_PATH . THEMES_PATH);
// themes base data path
define(
    'BASE_THEMES_PATH',
    isset($_SESSION['BASE_THEMES_PATH'])? $_SESSION['BASE_THEMES_PATH'] : THEMES_PATH
);
define('ROOT_BASE_THEMES_PATH', ROOT_PATH . BASE_THEMES_PATH);
// cache path
define(
    'CACHE_PATH',
    isset($_SESSION['CACHE_PATH'])? $_SESSION['CACHE_PATH'] : (DATA_PATH. 'cache/')
);
define('ROOT_CACHE_PATH', ROOT_PATH . CACHE_PATH);
// install path
define('INSTALL_PATH', __DIR__ . '/');

require_once ROOT_JAWS_PATH . 'include/Jaws/Const.php';
require_once ROOT_JAWS_PATH . 'include/Jaws/Utils.php';
require_once ROOT_JAWS_PATH . 'include/Jaws/FileManagement.php';
require_once ROOT_JAWS_PATH . 'include/Jaws/FileManagement/File.php';
require_once ROOT_JAWS_PATH . 'include/Jaws/Error.php';
require_once ROOT_JAWS_PATH . 'include/Jaws/Gadget.php';
// Lets support older PHP versions so we can use spanking new functions
require_once ROOT_JAWS_PATH . 'include/Jaws/Helper.php';

// Initialize the logger
$_SESSION['use_log'] = isset($_SESSION['use_log'])? $_SESSION['use_log']: false;
define('LOGGER_METHOD', 'LogToFile');
define('LOGGER_METHOD_FILE_PATH', ROOT_DATA_PATH . 'logs/');
require ROOT_JAWS_PATH . 'include/Jaws/Log.php';
$GLOBALS['log'] = new Jaws_Log($_SESSION['use_log']);
$GLOBALS['log']->Start();

if (!isset($_SESSION['install'])) {
    $_SESSION['install'] = array('stage' => 0, 'lastStage' => array());
}

// Lets handle our requests
require ROOT_JAWS_PATH . 'include/Jaws/Request.php';
$request = Jaws_Request::getInstance();
$lang = $request->fetch('language', 'post');
if (isset($lang)) {
    $_SESSION['install']['language'] = urlencode($lang);
} elseif (!isset($_SESSION['install']['language'])) {
    $_SESSION['install']['language'] = 'en';
}

include_once ROOT_JAWS_PATH . 'include/Jaws/Translate.php';
$objTranslate = Jaws_Translate::getInstance(false);
if (isset($_SESSION['install']['language'])) {
    $objTranslate->SetLanguage($_SESSION['install']['language']);
}
$objTranslate->LoadTranslation('Global');
$objTranslate->LoadTranslation('Install', JAWS_COMPONENT_INSTALL);

// go to previous stage
$go_prev_step = $request->fetch('prev_stage', 'post');
if (isset($go_prev_step)) {
    // Go to back if the previous button has been hit
    $_SESSION['install']['stage']--;
    $GLOBALS['message'] = null;
}

require_once 'JawsInstaller.php';
JawsInstaller::loadStages();
$objStage = JawsInstaller::loadStage($_SESSION['install']['stage']);

$skip = false;
/*
$_SESSION['install']['predefined'] = $predefined = $installer->hasPredefined();
$_SESSION['install']['data'] = $data = $installer->getPredefinedData();

if (
    ($predefined && isset($data[$stage['file']]['skip']) && $data[$stage['file']]['skip'] === '1')
    || (isset($_SESSION['install'][$stage['file']]['skip']) && $_SESSION['install'][$stage['file']]['skip'] === '1')
) {
    $skip = true;
    // Fake a next button push
    $auto_next_step = true;
}
*/

$go_next_step = $request->fetch('next_stage', 'post');
// Only attempt to validate if the next button has been hit
if (isset($go_next_step) || isset($auto_next_step)) {
    $result = $objStage->validate();
    if (!Jaws_Error::isError($result)) {
        $result = $objStage->run();

        if (!Jaws_Error::isError($result)) {
            if ($_SESSION['install']['stage'] < $objStage->countStages() - 1) {
                $_SESSION['install']['stage']++;
                header('Location: index.php');
            }

            $result = null;
        }
    }

    $GLOBALS['message'] = $result;
}

// Mark the stage as having been run.
$_SESSION['install']['lastStage'] = $_SESSION['install']['stage'];

if (!isset($GLOBALS['message']) && $skip) {
    header('Location: index.php');
}

include_once ROOT_JAWS_PATH . 'include/Jaws/Template.php';
$tpl = new Jaws_Template(false, false);
$tpl->Load('page.html', 'templates');
$tpl->SetBlock('page');
$tpl->SetVariable('title', $objStage->name);
$tpl->SetVariable('body',  $objStage->display());
$tpl->SetVariable('stage', $objStage->file);

foreach ($objStage->getStages() as $key => $stage) {
    if ($key < $_SESSION['install']['stage']) {
        $tpl->SetBlock('page/completed_stage');
        $tpl->SetVariable('name', $stage['name']);
        $tpl->ParseBlock('page/completed_stage');
    } elseif ($key == $_SESSION['install']['stage']) {
        $tpl->SetBlock('page/current_stage');
        $tpl->SetVariable('name', $stage['name']);
        $tpl->ParseBlock('page/current_stage');
    } else {
        $tpl->SetBlock('page/stage');
        $tpl->SetVariable('name', $stage['name']);
        $tpl->ParseBlock('page/stage');
    }
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
$direction = Jaws::t('LANG_DIRECTION');
$dir  = $direction == 'rtl' ? '.' . $direction : '';

// Display the layout
$layout = new Jaws_Template(false, false);
$layout->Load('layout.html', 'templates');
$layout->SetBlock('layout');

// Basic setup
$layout->SetVariable('base_url', Jaws_Utils::getBaseURL('/install/'));
$layout->SetVariable('.dir', $dir);
if ($_SESSION['install']['stage'] < 2 ) {
    $layout->SetVariable('site-title', 'Jaws Project');
    $layout->SetVariable('site-name',  'Jaws Project');
} else {
    $layout->SetVariable('site-title', 'Jaws ' . JAWS_VERSION);
    $layout->SetVariable('site-name',  'Jaws ' . JAWS_VERSION);
}
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
exit;