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
 * @copyright  2005-2013 Jaws Development Group
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
$GLOBALS['app']->loadObject('Jaws_ACL', 'ACL');

$IsIndex   = false;
$objGadget = null;
$IsReqActionStandAlone = false;
// Only registerd user can access not global website
$AccessToWebsiteDenied = !$GLOBALS['app']->Session->Logged() &&
                         $GLOBALS['app']->Registry->fetch('global_website', 'Settings') == 'false';

// Get forwarded error from webserver
$ReqError = jaws()->request->fetch('http_error', 'get');
if (empty($ReqError) && $GLOBALS['app']->Map->Parse()) {
    $ReqGadget = Jaws_Gadget::filter(jaws()->request->fetch('gadget'));
    $ReqAction = Jaws_Gadget_Action::filter(jaws()->request->fetch('action'));

    if ($AccessToWebsiteDenied && $ReqGadget !== 'Users') {
        $ReqGadget = 'Users';
        $ReqAction = 'LoginBox';
    } elseif (empty($ReqGadget)) {
        $IsIndex = true;
        $ReqGadget = $GLOBALS['app']->Registry->fetchByUser(
            $GLOBALS['app']->Session->GetAttribute('layout'),
            'main_gadget',
            'Settings'
        );
    }

    if (!empty($ReqGadget)) {
        if (Jaws_Gadget::IsGadgetEnabled($ReqGadget)) {
            $objGadget = Jaws_Gadget::getInstance($ReqGadget)->action->load();
            if (Jaws_Error::IsError($objGadget)) {
                Jaws_Error::Fatal("Error loading gadget: $ReqGadget");
            }

            if (!$GLOBALS['app']->Session->GetPermission($ReqGadget, 'default')) {
                $ReqError = '403';
            }

            if (empty($ReqAction)) {
                $ReqAction = $objGadget->gadget->default_action;
            }
            $GLOBALS['app']->SetMainRequest($IsIndex, $ReqGadget, $ReqAction);
        } else {
            $ReqError = '404';
        }
    }
} else {
    $ReqError = empty($ReqError)? '404' : $ReqError;
    $ReqGadget = null;
    $ReqAction = null;
}

// set main request
$GLOBALS['app']->SetMainRequest($IsIndex, $ReqGadget, $ReqAction);
// Init layout...
$GLOBALS['app']->InstanceLayout();
$GLOBALS['app']->Layout->Load();

// Run auto-load methods before standalone actions too
$GLOBALS['app']->RunAutoload();

if (empty($ReqError)) {
    $ReqResult = '';
    if (!empty($objGadget)) {
        $ReqResult = $objGadget->Execute($ReqAction);
        if (Jaws_Error::isError($ReqResult)) {
            $ReqResult = $ReqResult->GetMessage();
        }

        // we must check type of action after execute, because gadget can change it at runtime
        $IsReqActionStandAlone = $objGadget->IsStandAlone($ReqAction);
    }
} else {
    require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
    $ReqResult = Jaws_HTTPError::Get($ReqError);
}

if (!$IsReqActionStandAlone) {
    $GLOBALS['app']->Layout->Populate($ReqResult, $AccessToWebsiteDenied);
    $ReqResult = $GLOBALS['app']->Layout->Get();
}

terminate($ReqResult);
