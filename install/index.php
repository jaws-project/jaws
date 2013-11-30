<?php
/**
 * Jaws Installer System
 *
 * @category   Application
 * @package    Install
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
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

define('JAWS_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('PEAR_PATH', JAWS_PATH . 'libraries/pear/');

// lets setup the include_path
set_include_path('.' . PATH_SEPARATOR . JAWS_PATH . 'libraries/pear');

// this variables currently temporary util we complete multible instance installing
define('JAWS_BASE_DATA', JAWS_PATH . 'data'. DIRECTORY_SEPARATOR);
define('JAWS_DATA', JAWS_BASE_DATA);
define('JAWS_THEMES', JAWS_DATA. 'themes'. DIRECTORY_SEPARATOR);
define('JAWS_BASE_THEMES', JAWS_THEMES);
define('JAWS_CACHE', JAWS_DATA. 'cache'. DIRECTORY_SEPARATOR);
define('INSTALL_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// Lets support older PHP versions so we can use spanking new functions
require_once JAWS_PATH . 'include/Jaws/PHPFunctions.php';

// Initialize the logger
$_SESSION['use_log'] = isset($_SESSION['use_log'])? $_SESSION['use_log']: false;
$logger = array('method'  => 'LogToFile',
                'options' => array('file' => JAWS_DATA . 'logs/.install.log'));
require JAWS_PATH . 'include/Jaws/Log.php';
$GLOBALS['log'] = new Jaws_Log($_SESSION['use_log'], $logger);
$GLOBALS['log']->Start();

require_once JAWS_PATH . 'include/Jaws/Const.php';
require_once JAWS_PATH . 'include/Jaws/Error.php';
require_once JAWS_PATH . 'include/Jaws/Utils.php';
require_once JAWS_PATH . 'include/Jaws/Gadget.php';

if (!isset($_SESSION['install'])) {
    $_SESSION['install'] = array('stage' => 0, 'lastStage' => array());
}

// Lets handle our requests
require JAWS_PATH . 'include/Jaws/Request.php';
$request = Jaws_Request::getInstance();
$lang = $request->fetch('language', 'post');
if (isset($lang)) {
    $_SESSION['install']['language'] = urlencode($lang);
} elseif (!isset($_SESSION['install']['language'])) {
    $_SESSION['install']['language'] = 'en';
}

include_once JAWS_PATH . 'include/Jaws/Translate.php';
$objTranslate = Jaws_Translate::getInstance(false);
if (isset($_SESSION['install']['language'])) {
    $objTranslate->SetLanguage($_SESSION['install']['language']);
}
$objTranslate->LoadTranslation('Global');
$objTranslate->LoadTranslation('Install', JAWS_COMPONENT_INSTALL);

require_once 'stagelist.php';
require_once 'JawsInstaller.php';
require_once 'JawsInstallerStage.php';

$installer = new JawsInstaller();
$installer->loadStages($stages);
$stages = $installer->getStages();
$stage  = $stages[$_SESSION['install']['stage']];

$stageobj = $installer->loadStage($stage);
$stages_count = count($stages);

$_SESSION['install']['predefined'] = $predefined = $installer->hasPredefined();
$_SESSION['install']['data'] = $data = $installer->getPredefinedData();

$skip = false;
if (
    ($predefined && isset($data[$stage['file']]['skip']) && $data[$stage['file']]['skip'] === '1')
    || (isset($_SESSION['install'][$stage['file']]['skip']) && $_SESSION['install'][$stage['file']]['skip'] === '1')
) {
    $skip = true;
    // Fake a next button push
    $auto_next_step = true;
}

$go_next_step = $request->fetch($stage['file'] . '_complete', 'post');
// Only attempt to validate if the next button has been hit.
if (isset($go_next_step) || isset($auto_next_step)) {
    $result = $stageobj->validate();
    if (!Jaws_Error::isError($result)) {
        $result = $stageobj->run();

        if (!Jaws_Error::isError($result)) {
            if ($_SESSION['install']['stage'] < $stages_count - 1) {
                $_SESSION['install']['stage']++;
                $stageobj = $installer->loadStage($stages[$_SESSION['install']['stage']]);
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

include_once JAWS_PATH . 'include/Jaws/Template.php';
$tpl = new Jaws_Template(false);
$tpl->Load('page.html', 'templates');
$tpl->SetBlock('page');
$tpl->SetVariable('title', $stages[$_SESSION['install']['stage']]['name']);
$tpl->SetVariable('body',  $stageobj->display());
$tpl->SetVariable('stage', $stages[$_SESSION['install']['stage']]['file']);

foreach ($stages as $key => $stage) {
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
$direction = _t('GLOBAL_LANG_DIRECTION');
$dir  = $direction == 'rtl' ? '.' . $direction : '';

// Display the layout
$layout = new Jaws_Template(false);
$layout->Load('layout.html', 'templates');
$layout->SetBlock('layout');

// Basic setup
$layout->SetVariable('BASE_URL', Jaws_Utils::getBaseURL('/install/'));
$layout->SetVariable('.dir', $dir);
$layout->SetVariable('site-title', 'Jaws ' . JAWS_VERSION);
$layout->SetVariable('site-name',  'Jaws ' . JAWS_VERSION);
$layout->SetVariable('site-slogan', JAWS_VERSION_CODENAME);

// Load js files
$layout->SetBlock('layout/head');
$layout->SetVariable('ELEMENT', '<script type="text/javascript" src="../libraries/js/rsa.lib.js"></script>');
$layout->ParseBlock('layout/head');

// Display the stage
$layout->SetBlock('layout/main');
$layout->SetVariable('ELEMENT', $tpl->Get());
$layout->ParseBlock('layout/main');
$layout->ParseBlock('layout');

echo $layout->Get();

// Check if we are on the last stage, Key + 1 because an array starts with 0 :-)
if (($_SESSION['install']['stage'] + 1) == $stages_count) {
    // Kill of the session cookie (path cookie in FF)
    unset($_SESSION['install']);
}
exit;