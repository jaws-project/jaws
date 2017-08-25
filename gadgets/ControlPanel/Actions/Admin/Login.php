<?php
/**
 * ControlPanel Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     ControlPanel
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanel_Actions_Admin_Login extends Jaws_Gadget_Action
{
    /**
     * Get HTML login form
     *
     * @access  public
     * @param   string  $message If a message is needed
     * @return  string  XHTML template of the login form
     */
    function LoginBox($message = '')
    {
        // Init layout
        $GLOBALS['app']->Layout->Load('gadgets/ControlPanel/Templates', 'LoginBox.html');
        $ltpl =& $GLOBALS['app']->Layout->_Template;
        $ltpl->SetVariable('admin-script', BASE_SCRIPT);
        $ltpl->SetVariable('control-panel', _t('GLOBAL_CONTROLPANEL'));

        $reqpost = $this->gadget->request->fetch(array('username', 'authtype', 'remember', 'usecrypt', 'redirect_to'), 'post');
        if (is_null($reqpost['authtype'])) {
            $reqpost['authtype'] = $this->gadget->request->fetch('authtype', 'get');
        }

        // referrer page link
        $reqURL = Jaws_Utils::getRequestURL();
        $reqURL = (empty($reqURL) || $reqURL == BASE_SCRIPT)? (BASE_SCRIPT. '?checksess') : "$reqURL&checksess";
        $redirect_to = !isset($reqpost['redirect_to'])? bin2hex($reqURL) : $reqpost['redirect_to'];
        $ltpl->SetVariable('redirect_to', $redirect_to);

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $GLOBALS['app']->Layout->addScript('libraries/js/rsa.lib.js');
            $ltpl->SetBlock('layout/onsubmit');
            $ltpl->ParseBlock('layout/onsubmit');
            $ltpl->SetBlock('layout/encryption');
            $ltpl->SetVariable('length',   $JCrypt->length());
            $ltpl->SetVariable('modulus',  $JCrypt->modulus());
            $ltpl->SetVariable('exponent', $JCrypt->exponent());
            $ltpl->ParseBlock('layout/encryption');

            // usecrypt
            $ltpl->SetBlock('layout/usecrypt');
            $ltpl->SetVariable('lbl_usecrypt', _t('GLOBAL_LOGIN_SECURE'));
            if (empty($reqpost['username']) || !empty($reqpost['usecrypt'])) {
                $ltpl->SetBlock('layout/usecrypt/selected');
                $ltpl->ParseBlock('layout/usecrypt/selected');
            }
            $ltpl->ParseBlock('layout/usecrypt');
        }

        $ltpl->SetVariable('legend_title', _t('CONTROLPANEL_LOGIN_TITLE'));
        $ltpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $ltpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $ltpl->SetVariable('lbl_password', _t('GLOBAL_PASSWORD'));

        $authtype = $this->gadget->registry->fetch('authtype', 'Users');
        if (!is_null($reqpost['authtype']) || $authtype !== 'Default') {
            $authtype = is_null($reqpost['authtype'])? $authtype : $reqpost['authtype'];
            $ltpl->SetBlock('layout/authtype');
            $ltpl->SetVariable('lbl_authtype', _t('GLOBAL_AUTHTYPE'));
            foreach ($GLOBALS['app']->GetAuthTypes() as $method) {
                $ltpl->SetBlock('layout/authtype/item');
                $ltpl->SetVariable('method', $method);
                if ($method == $authtype) {
                    $ltpl->SetVariable('selected', 'selected="selected"');
                } else {
                    $ltpl->SetVariable('selected', '');
                }
                $ltpl->ParseBlock('layout/authtype/item');
            }
            $ltpl->ParseBlock('layout/authtype');
        }

        // remember
        $ltpl->SetBlock('layout/remember');
        $ltpl->SetVariable('lbl_remember', _t('GLOBAL_REMEMBER_ME'));
        if (!empty($reqpost['remember'])) {
            $ltpl->SetBlock('layout/remember/selected');
            $ltpl->ParseBlock('layout/remember/selected');
        }
        $ltpl->ParseBlock('layout/remember');

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($ltpl, 'layout', 'login');

        $ltpl->SetVariable('login', _t('GLOBAL_LOGIN'));
        $ltpl->SetVariable('back', _t('CONTROLPANEL_LOGIN_BACK_TO_SITE'));

        $message = is_null($this->gadget->request->fetch('checksess'))? $message : _t('GLOBAL_ERROR_SESSION_NOTFOUND');
        if (!empty($message)) {
            $ltpl->SetBlock('layout/message');
            $ltpl->SetVariable('message', $message);
            $ltpl->ParseBlock('layout/message');
        }

        return $GLOBALS['app']->Layout->Get();
    }

}