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
     * @return  string  XHTML template of the login form
     */
    function LoginBox()
    {
        $this->AjaxMe('script.js');
        // Init layout
        $GLOBALS['app']->Layout->Load('gadgets/ControlPanel/Templates', 'LoginBox.html');
        $ltpl =& $GLOBALS['app']->Layout->_Template;
        $ltpl->SetVariable('admin-script', BASE_SCRIPT);
        $ltpl->SetVariable('control-panel', _t('GLOBAL_CONTROLPANEL'));

        $response = $this->gadget->session->pop('Login.Response');
        if (!isset($response['data'])) {
            $referrer  = $this->gadget->request->fetch('referrer', 'get');
            $reqpost['username'] = '';
            $reqpost['password'] = '';
            $reqpost['authstep'] = 0;
            $reqpost['authtype'] = '';
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
            $reqpost['referrer'] = bin2hex(Jaws_Utils::getRequestURL(true));
            $this->gadget->session->insert('checksess', 1);
        } else {
            $reqpost = $response['data'];
        }

        if (is_null($reqpost['authtype'])) {
            $reqpost['authtype'] = $this->gadget->request->fetch('authtype', 'get');
        }

        // referrer
        $ltpl->SetVariable('referrer', $reqpost['referrer']);
        //
        $ltpl->SetVariable('legend_title', _t('CONTROLPANEL_LOGIN_TITLE'));

        if (!empty($reqpost['authstep'])) {
            $this->LoginBoxStep2($ltpl, $reqpost);
        } else {
            $this->LoginBoxStep1($ltpl, $reqpost);
        }

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($ltpl, 'layout', 'login');

        $ltpl->SetVariable('login', _t('GLOBAL_LOGIN'));
        $ltpl->SetVariable('back', _t('CONTROLPANEL_LOGIN_BACK_TO_SITE'));

        if (!empty($response)) {
            $ltpl->SetVariable('response_type', $response['type']);
            $ltpl->SetVariable('response_text', $response['text']);
        }

        return $GLOBALS['app']->Layout->Get();
    }

    /**
     * Get HTML login form
     *
     * @access  public
     * @return  string  XHTML template of the login form
     */
    private function LoginBoxStep1(&$tpl, $reqpost)
    {
        $tpl->SetBlock('layout/login_step_1');

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $tpl->SetBlock('layout/login_step_1/encryption');
            $tpl->SetVariable('pubkey', $JCrypt->getPublic());
            $tpl->ParseBlock('layout/login_step_1/encryption');

            // usecrypt
            $tpl->SetBlock('layout/login_step_1/usecrypt');
            $tpl->SetVariable('lbl_usecrypt', _t('GLOBAL_LOGIN_SECURE'));
            if (empty($reqpost['username']) || !empty($reqpost['usecrypt'])) {
                $tpl->SetBlock('layout/login_step_1/usecrypt/selected');
                $tpl->ParseBlock('layout/login_step_1/usecrypt/selected');
            }
            $tpl->ParseBlock('layout/login_step_1/usecrypt');
        }

        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('lbl_password', _t('GLOBAL_PASSWORD'));

        $authtype = $this->gadget->registry->fetch('authtype', 'Users');
        if (!empty($reqpost['authtype']) || $authtype !== 'Default') {
            $authtype = is_null($reqpost['authtype'])? $authtype : $reqpost['authtype'];
            $tpl->SetBlock('layout/login_step_1/authtype');
            $tpl->SetVariable('lbl_authtype', _t('GLOBAL_AUTHTYPE'));
            foreach ($GLOBALS['app']->GetAuthTypes() as $method) {
                $tpl->SetBlock('layout/login_step_1/authtype/item');
                $tpl->SetVariable('method', $method);
                if ($method == $authtype) {
                    $tpl->SetVariable('selected', 'selected="selected"');
                } else {
                    $tpl->SetVariable('selected', '');
                }
                $tpl->ParseBlock('layout/login_step_1/authtype/item');
            }
            $tpl->ParseBlock('layout/login_step_1/authtype');
        }

        // remember
        $tpl->SetBlock('layout/login_step_1/remember');
        $tpl->SetVariable('lbl_remember', _t('GLOBAL_REMEMBER_ME'));
        if (!empty($reqpost['remember'])) {
            $tpl->SetBlock('layout/login_step_1/remember/selected');
            $tpl->ParseBlock('layout/login_step_1/remember/selected');
        }
        $tpl->ParseBlock('layout/login_step_1/remember');

        $tpl->ParseBlock('layout/login_step_1');
    }

    /**
     * Get HTML login form
     *
     * @access  public
     * @return  string  XHTML template of the login form
     */
    private function LoginBoxStep2(&$tpl, $reqpost)
    {
        $tpl->SetBlock('layout/login_step_2');

        $tpl->SetVariable('usecrypt', $reqpost['usecrypt']);
        $tpl->SetVariable('authtype', $reqpost['authtype']);
        $tpl->SetVariable('remember', $reqpost['remember']);
        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('password', isset($reqpost['password'])? $reqpost['password'] : '');

        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('lbl_loginkey', _t('GLOBAL_LOGINKEY'));

        $tpl->ParseBlock('layout/login_step_2');
    }

}