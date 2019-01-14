<?php
/**
 * Admin page for jaws
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
define('JAWS_SCRIPT', 'admin');
define('BASE_SCRIPT', basename(__FILE__));

// Redirect to the installer if JawsConfig can't be found.
$root = dirname(__FILE__);
if (!file_exists($root . '/config/JawsConfig.php')) {
    require_once 'include/Jaws/Utils.php';
    header('Location: '. Jaws_Utils::getBaseURL('/'). 'install/index.php');
    exit;
} else {
    require $root . '/config/JawsConfig.php';
}

require_once JAWS_PATH . 'include/Jaws/InitApplication.php';

$ReqGadget = Jaws_Gadget::filter(jaws()->request->fetch('gadget', array('post', 'get')));
$ReqAction = Jaws_Gadget_Action::filter(jaws()->request->fetch('action', array('post', 'get')));

$httpAuthEnabled = $GLOBALS['app']->Registry->fetch('http_auth', 'Settings') == 'true';
if ($httpAuthEnabled) {
    require_once JAWS_PATH . 'include/Jaws/HTTPAuth.php';
    $httpAuth = new Jaws_HTTPAuth();
}

// Init layout
$GLOBALS['app']->InstanceLayout();
// Run auto-load methods before standalone actions too
$GLOBALS['app']->RunAutoload();

// Check for login action is requested
if (!$GLOBALS['app']->Session->Logged()) {
    $gdgtUsers = Jaws_Gadget::getInstance('Users');
    if (Jaws_Error::IsError($gdgtUsers)) {
        Jaws_Error::Fatal($gdgtUsers->getMessage());
    }

    $ReqGadget = 'Users';
    if (($ReqAction != 'Login') &&
        (!$httpAuthEnabled || !isset($_SERVER['PHP_AUTH_USER']))
    ) {
        $ReqAction = 'LoginBox';
    } else {
        $ReqAction = 'Login';
    }

    $GLOBALS['app']->mainGadget = $ReqGadget;
    $GLOBALS['app']->mainAction = $ReqAction;
    $GLOBALS['app']->define('', 'mainGadget', $ReqGadget);
    $GLOBALS['app']->define('', 'mainAction', $ReqAction);
    $ReqResult = $gdgtUsers->action->loadAdmin()->Execute($ReqAction);
    if (Jaws_Error::IsError($ReqResult)) {
        Jaws_Error::Fatal($ReqResult->getMessage());
    }

    terminate($ReqResult, 401);
}

if (empty($ReqGadget)) {
    $ReqGadget = 'ControlPanel';
    $ReqAction = '';
}

// Can use Control Panel?
$GLOBALS['app']->Session->CheckPermission('ControlPanel', 'default_admin');

if (Jaws_Gadget::IsGadgetEnabled($ReqGadget)) {
    $GLOBALS['app']->Session->CheckPermission($ReqGadget, 'default_admin');
    $objAction = Jaws_Gadget::getInstance($ReqGadget)->action->loadAdmin();
    if (Jaws_Error::IsError($objAction)) {
        Jaws_Error::Fatal("Error loading gadget: $ReqGadget");
    }

    $ReqAction = empty($ReqAction)? $objAction->gadget->default_admin_action : $ReqAction;
    // set requested gadget/action
    $GLOBALS['app']->mainGadget = $ReqGadget;
    $GLOBALS['app']->mainAction = $ReqAction;
    $GLOBALS['app']->define('', 'mainGadget', $ReqGadget);
    $GLOBALS['app']->define('', 'mainAction', $ReqAction);

    // check referrer host
    if (!$GLOBALS['app']->Session->extraCheck()) {
        $ReqResult = Jaws_HTTPError::Get(403);
    } else {
        $ReqResult = $objAction->Execute($ReqAction);
        if (Jaws_Error::IsError($ReqResult)) {
            Jaws_Error::Fatal($ReqResult->getMessage());
        }
    }

    $IsReqActionStandAlone = $objAction->IsStandAloneAdmin($ReqAction);
    if (!$IsReqActionStandAlone) {
        $ReqResult = Jaws_Gadget::getInstance('ControlPanel')
            ->action
            ->loadAdmin('ControlPanel')
            ->Layout($ReqGadget, $ReqResult, $objAction->gadget->version);
    }

    terminate($ReqResult);
}

Jaws_Error::Fatal('Invalid requested gadget');
