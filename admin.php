<?php
/**
 * Admin page for jaws
 *
 * @category    Application
 * @package     Core
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Helgi Þormar <dufuz@php.net>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2020 Jaws Development Group
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

$ReqGadget = Jaws_Gadget::filter(Jaws::getInstance()->request->fetch('gadget'));
$ReqAction = Jaws_Gadget_Action::filter(Jaws::getInstance()->request->fetch('action'));

// Run auto-load methods before standalone actions too
$jawsApp->RunAutoload();

// Check for login action is requested
if (!$jawsApp->session->user->logged) {
    $gdgtUsers = Jaws_Gadget::getInstance('Users');
    if (Jaws_Error::IsError($gdgtUsers)) {
        Jaws_Error::Fatal($gdgtUsers->getMessage());
    }

    $ReqResult = '';
    if ($ReqGadget != 'Users' || !in_array($ReqAction, array('Login', 'Authenticate'))) {
        Jaws_Header::Location(
            $gdgtUsers->gadget->url(
                'Login',
                array('referrer' => bin2hex(Jaws_Utils::getRequestURL()))
            )
        );
    } else {
        $objAction = $gdgtUsers->action->loadAdmin();
        if (Jaws_Error::IsError($objAction)) {
            Jaws_Error::Fatal($objAction->getMessage());
        }

        // set main requested attributes
        $ReqAction = empty($ReqAction)? $objAction->gadget->default_admin_action : $ReqAction;
        $jawsApp->mainRequest = $objAction->getAttributes($ReqAction);
        $jawsApp->mainRequest['gadget'] = $ReqGadget;
        $jawsApp->mainRequest['action'] = $ReqAction;

        $jawsApp->define('', 'mainGadget', $ReqGadget);
        $jawsApp->define('', 'mainAction', $ReqAction);
        $ReqResult = $objAction->Execute($ReqAction);
        if (Jaws_Error::IsError($ReqResult)) {
            Jaws_Error::Fatal($ReqResult->getMessage());
        }
    }

    terminate($ReqResult, 401);
}

if (empty($ReqGadget)) {
    $ReqGadget = 'ControlPanel';
    $ReqAction = '';
}

// Can use Control Panel?
$jawsApp->session->CheckPermission('ControlPanel', 'default_admin');

if (Jaws_Gadget::IsGadgetEnabled($ReqGadget)) {
    $jawsApp->session->CheckPermission($ReqGadget, 'default_admin');
    $objAction = Jaws_Gadget::getInstance($ReqGadget)->action->loadAdmin();
    if (Jaws_Error::IsError($objAction)) {
        Jaws_Error::Fatal("Error loading gadget: $ReqGadget");
    }

    // set main requested attributes
    $ReqAction = empty($ReqAction)? $objAction->gadget->default_admin_action : $ReqAction;
    $jawsApp->mainRequest = $objAction->getAttributes($ReqAction);
    $jawsApp->mainRequest['gadget'] = $ReqGadget;
    $jawsApp->mainRequest['action'] = $ReqAction;

    $jawsApp->define('', 'mainGadget', $ReqGadget);
    $jawsApp->define('', 'mainAction', $ReqAction);

    // check referrer host
    if (!$jawsApp->session->extraCheck() ||
        Jaws_Utils::getReferrerHost() != $_SERVER['HTTP_HOST']
    ) {
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
