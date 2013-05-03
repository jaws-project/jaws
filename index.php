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
$request =& Jaws_Request::getInstance();
// Get forwarded error from webserver
$ReqError = $request->get('http_error', 'get');
if (empty($ReqError) && $GLOBALS['app']->Map->Parse()) {
    $ReqGadget = $request->get('gadget');
    $ReqAction = $request->get('action');

    if ($AccessToWebsiteDenied && $ReqGadget !== 'Users') {
        $ReqGadget = 'Users';
        $ReqAction = 'LoginBox';
    } elseif (empty($ReqGadget)) {
        $IsIndex = true;
        $ReqGadget = $GLOBALS['app']->Registry->fetch('main_gadget', 'Settings');
    }

    if (!empty($ReqGadget)) {
        if (Jaws_Gadget::IsValid($ReqGadget)) {
            $objGadget = $GLOBALS['app']->LoadGadget($ReqGadget);
            if (Jaws_Error::IsError($objGadget)) {
                Jaws_Error::Fatal("Error loading gadget: $ReqGadget");
            }

            if ($GLOBALS['app']->Session->GetPermission($ReqGadget, 'default')) {
                $ReqAction = empty($ReqAction)? 'DefaultAction' : $ReqAction;
                $objGadget->SetAction($ReqAction);
                $ReqAction = $objGadget->GetAction();
                $GLOBALS['app']->SetMainRequest($IsIndex, $ReqGadget, $ReqAction);
            } else {
                $ReqError = '403';
            }
        } else {
            $ReqError = '404';
        }
    }
} else {
    $ReqError = empty($ReqError)? '404' : $ReqError;
    $ReqGadget = null;
    $ReqAction = null;
}

// Init layout...
$GLOBALS['app']->InstanceLayout();
$GLOBALS['app']->Layout->Load();

// Run auto-load methods before standalone actions too
$GLOBALS['app']->RunAutoload();

if (empty($ReqError)) {
    $ReqResult = '';
    if (!empty($objGadget)) {
        $ReqResult = $objGadget->Execute();
        if (Jaws_Error::isError($ReqResult)) {
            $ReqResult = $ReqResult->GetMessage();
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, 'In '.$ReqGadget.'::'.$ReqAction.','.$ReqResult);
        }
        // we must check type of action after execute, because gadget can change it at runtime
        $IsReqActionStandAlone = $objGadget->IsStandAlone($ReqAction);
    }
} else {
    require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
    $ReqResult = Jaws_HTTPError::Get($ReqError);
}

if (!$IsReqActionStandAlone) {
    $GLOBALS['app']->Layout->Populate($objGadget, $IsIndex, $ReqResult, $AccessToWebsiteDenied);
    $ReqResult = $GLOBALS['app']->Layout->Get();
}

// Send content to client
echo $ReqResult;

// Sync session
$GLOBALS['app']->Session->Synchronize();
$GLOBALS['log']->End();
exit;
