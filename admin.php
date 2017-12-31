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
if (empty($ReqGadget)) {
    $ReqGadget = 'ControlPanel';
    $ReqAction = '';
}

$httpAuthEnabled = $GLOBALS['app']->Registry->fetch('http_auth', 'Settings') == 'true';
if ($httpAuthEnabled) {
    require_once JAWS_PATH . 'include/Jaws/HTTPAuth.php';
    $httpAuth = new Jaws_HTTPAuth();
}

// Check for login action is requested
if (!$GLOBALS['app']->Session->Logged())
{
    // Init layout
    $GLOBALS['app']->InstanceLayout();

    $cplGadget = Jaws_Gadget::getInstance('ControlPanel');
    if (Jaws_Error::IsError($cplGadget)) {
        Jaws_Error::Fatal($cplGadget->getMessage());
    }

    if (($ReqGadget == 'ControlPanel' && $ReqAction == 'Login') ||
        ($httpAuthEnabled && isset($_SERVER['PHP_AUTH_USER'])))
    {
        if ($httpAuthEnabled) {
            $httpAuth->AssignData();
            jaws()->request->update('username', $httpAuth->getUsername(), 'post');
            jaws()->request->update('password', $httpAuth->getPassword(), 'post');
        }

        $loginData = jaws()->request->fetch(
            array('username', 'password', 'usecrypt', 'loginkey', 'authstep', 'referrer', 'remember', 'authtype'),
            'post'
        );
        // check captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $mPolicy->checkCaptcha('login');
        if (!Jaws_Error::IsError($resCheck)) {
            $resCheck = $GLOBALS['app']->Session->Login($loginData);
        }
        if (!Jaws_Error::IsError($resCheck)) {
            // Can enter to Control Panel?
            if ($cplGadget->GetPermission('default_admin')) {
                if (!$cplGadget->session->fetch('checksess')) {
                    $cplGadget->session->push(
                        _t('GLOBAL_ERROR_SESSION_NOTFOUND'),
                        'Login.Response',
                        RESPONSE_WARNING
                    );
                } else {
                    $referrer = parse_url(hex2bin($loginData['referrer']));
                    $referrer = (array_key_exists('path', $referrer)? $referrer['path'] : '') . 
                                (array_key_exists('query', $referrer)? "?{$referrer['query']}" : '') . 
                                (array_key_exists('fragment', $referrer)? "#{$referrer['fragment']}" : '');
                    Jaws_Header::Location($referrer);
                }
            } else {
                $GLOBALS['app']->Session->Logout();
                $cplGadget->session->push(
                    _t('GLOBAL_ERROR_LOGIN_NOTCP'),
                    'Login.Response',
                    RESPONSE_ERROR
                );
            }
        } else {
            $cplGadget->session->push(
                $resCheck->GetMessage(),
                'Login.Response',
                RESPONSE_ERROR,
                $loginData
            );
        }
    }

    if ($httpAuthEnabled) {
        $httpAuth->showLoginBox();
    }

    $data = $cplGadget->action->loadAdmin('Login')->LoginBox();
    terminate($data, 401);
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

    // Init layout
    $GLOBALS['app']->InstanceLayout();

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
