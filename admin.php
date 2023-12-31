<?php
/**
 * Admin page for jaws
 *
 * @category    Application
 * @package     Core
 * @copyright   2005-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
define('JAWS_SCRIPT', 'admin');
define('BASE_SCRIPT', basename(__FILE__));

// Redirect to the installer if JawsConfig can't be found.
require_once 'include/Jaws/Utils.php';
if (!file_exists(__DIR__ . '/config/JawsConfig.php')) {
    header('Location: '. Jaws_Utils::getBaseURL('/'). 'install/index.php');
    exit;
} else {
    require __DIR__ . '/config/JawsConfig.php';
    if (!defined('ROOT_JAWS_PATH')) {
        header('Location: '. Jaws_Utils::getBaseURL('/'). 'upgrade/index.php');
    }
}

require_once ROOT_JAWS_PATH . 'include/Jaws/InitApplication.php';
$jawsApp = Jaws::getInstance();

$result = Jaws_Gadget::ExecuteMainRequest();
if (!$result['standalone']) {
    $result['return'] = Jaws_Gadget::getInstance('ControlPanel')
        ->action
        ->loadAdmin('ControlPanel')
        ->Layout($result['gadget'], $result['return'], $result['version']);
}

terminate($result['return']);
