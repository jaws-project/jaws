<?php
/**
 * Index page for jaws
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
define('JAWS_SCRIPT', 'index');
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
$jawsApp = Jaws::getInstance();

$IsIndex   = false;
$objAction = null;
$IsReqActionStandAlone = false;
// Only registered user can access not global website
$AccessToWebsiteDenied = !$jawsApp->session->Logged() &&
                         $jawsApp->registry->fetch('global_website', 'Settings') == 'false';

// Get forwarded error from webserver
$ReqError = Jaws::getInstance()->request->fetch('http_error', 'get');
if (empty($ReqError) && $jawsApp->map->Parse()) {
    $ReqGadget = Jaws_Gadget::filter(Jaws::getInstance()->request->fetch('gadget'));
    $ReqAction = Jaws_Gadget_Action::filter(Jaws::getInstance()->request->fetch('action'));

    if (empty($ReqGadget)) {
        $IsIndex = true;
        $ReqGadget = $jawsApp->registry->fetchByUser(
            $jawsApp->session->getAttribute('layout'),
            'main_gadget',
            'Settings'
        );
    }

    if (!empty($ReqGadget) && $ReqGadget != '-') {
        if (Jaws_Gadget::IsGadgetEnabled($ReqGadget)) {
            $objAction = Jaws_Gadget::getInstance($ReqGadget)->action->load();
            if (Jaws_Error::IsError($objAction)) {
                Jaws_Error::Fatal("Error loading gadget: $ReqGadget");
            }

            // check referrer host for internal action
            if ($objAction->getAttribute($ReqAction, 'internal') &&
                (!$jawsApp->session->extraCheck() || Jaws_Utils::getReferrerHost() != $_SERVER['HTTP_HOST'])
            ) {
                $ReqError = '403';
            }

            $ReqAction = empty($ReqAction)? $objAction->gadget->default_action : $ReqAction;
            // set requested gadget/action
            $jawsApp->mainGadget = $ReqGadget;
            $jawsApp->mainAction = $ReqAction;
            $jawsApp->define('', 'mainGadget', $ReqGadget);
            $jawsApp->define('', 'mainAction', $ReqAction);
        } else {
            $ReqError = '404';
            $ReqGadget = null;
            $ReqAction = null;
        }
    }

    // if action not a global action and site is protected, so request redirected to login page
    if ($AccessToWebsiteDenied && (empty($objAction) || !$objAction->getAttribute($ReqAction, 'global'))) {
        $IsIndex = false;
        $ReqGadget = 'Users';
        $ReqAction = 'Login';
        $objAction = Jaws_Gadget::getInstance($ReqGadget)->action->load();
        if (Jaws_Error::IsError($objAction)) {
            Jaws_Error::Fatal("Error loading gadget: $ReqGadget");
        }

        $ReqError = '';
        // set requested gadget
        $jawsApp->mainGadget = $ReqGadget;
        $jawsApp->mainAction = $ReqAction;
        $jawsApp->define('', 'mainGadget', $ReqGadget);
        $jawsApp->define('', 'mainAction', $ReqAction);
    }
} else {
    $ReqError = empty($ReqError)? '404' : $ReqError;
    $ReqGadget = null;
    $ReqAction = null;
}

// set requested in front-end first/home page
$jawsApp->mainIndex = $IsIndex;
// Init layout...
$jawsApp->instanceLayout();
$jawsApp->layout->Load();

// Run auto-load methods before standalone actions too
$jawsApp->RunAutoload();

if (empty($ReqError)) {
    $ReqResult = '';
    if (!empty($objAction)) {
        // set in main request
        $jawsApp->inMainRequest = true;
        $ReqResult = $objAction->Execute($ReqAction);
        if (Jaws_Error::isError($ReqResult)) {
            $ReqResult = $ReqResult->GetMessage();
        }
        $jawsApp->inMainRequest = false;

        // we must check type of action after execute, because gadget can change it at runtime
        $ReqMode = Jaws_Gadget::filter(Jaws::getInstance()->request->fetch('mode'));
        $IsReqActionStandAlone = ($ReqMode == 'standalone') || $objAction->IsStandAlone($ReqAction);
    }
} else {
    $ReqResult = Jaws_HTTPError::Get($ReqError);
}

if (!$IsReqActionStandAlone) {
    $jawsApp->layout->Populate($ReqResult, $AccessToWebsiteDenied);
    $ReqResult = $jawsApp->layout->Get();
}

terminate($ReqResult);
