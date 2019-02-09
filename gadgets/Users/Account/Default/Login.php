<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Default_Login extends Users_Account_Default
{
    /**
     * Builds the login box
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Login()
    {
        return (JAWS_SCRIPT == 'index')? $this->IndexLogin() : $this->AdminLogin();
    }

    /**
     * Builds the frontend login box
     *
     * @access  public
     * @return  string  XHTML content
     */
    function IndexLogin()
    {
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('LoginBox.html');
        $tpl->SetBlock('LoginBox');
        $tpl->SetVariable('title', _t('USERS_LOGIN_TITLE'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $response = $this->gadget->session->pop('Login.Response');
        if (!isset($response['data'])) {
            $reqpost['domain'] = $this->gadget->registry->fetch('default_domain');
            $reqpost['username'] = '';
            $reqpost['authstep'] = 0;
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
        } else {
            $reqpost = $response['data'];
        }

        // global variables
        $tpl->SetVariable('login', _t('GLOBAL_LOGIN'));

        if (!empty($reqpost['authstep'])) {
            $this->LoginBoxStep2($tpl, $reqpost);
        } else {
            $this->LoginBoxStep1($tpl, $reqpost);
        }

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl, 'LoginBox', 'login');

        if ($this->gadget->registry->fetch('anon_register') == 'true') {
            $link =& Piwi::CreateWidget(
                'Link',
                _t('USERS_REGISTER'),
                $this->gadget->urlMap('Registration')
            );
            $tpl->SetVariable('user-register', $link->Get());
        }

        if ($this->gadget->registry->fetch('password_recovery') == 'true') {
            $link =& Piwi::CreateWidget(
                'Link',
                _t('USERS_FORGOT_LOGIN'),
                $this->gadget->urlMap('LoginForgot')
            );
            $tpl->SetVariable('forgot-password', $link->Get());
        }

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('LoginBox');
        return $tpl->Get();
    }

    /**
     * Builds the backend login box
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AdminLogin()
    {
        if ($GLOBALS['app']->Registry->fetch('http_auth', 'Settings') == 'true') {
            $httpAuth = new Jaws_HTTPAuth();
            $httpAuth->showLoginBox();
            return false;
        }

        $this->AjaxMe('script.js');
        // Init layout
        $GLOBALS['app']->Layout->Load('gadgets/Users/Templates/Admin', 'LoginBox.html');
        $ltpl =& $GLOBALS['app']->Layout->_Template;
        $ltpl->SetVariable('admin-script', BASE_SCRIPT);
        $ltpl->SetVariable('control-panel', _t('GLOBAL_CONTROLPANEL'));

        $response = $this->gadget->session->pop('Login.Response');
        if (!isset($response['data'])) {
            $reqpost['domain'] = $this->gadget->registry->fetch('default_domain');
            $reqpost['username'] = '';
            $reqpost['authstep'] = 0;
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
            //$reqpost['referrer'] = bin2hex(Jaws_Utils::getRequestURL(true));
        } else {
            $reqpost = $response['data'];
        }

        // referrer
        //$ltpl->SetVariable('referrer', $reqpost['referrer']);
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
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/login_step_1");

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $tpl->SetBlock("$block/login_step_1/encryption");
            $tpl->SetVariable('pubkey', $JCrypt->getPublic());
            $tpl->ParseBlock("$block/login_step_1/encryption");

            // usecrypt
            $tpl->SetBlock("$block/login_step_1/usecrypt");
            $tpl->SetVariable('lbl_usecrypt', _t('GLOBAL_LOGIN_SECURE'));
            if (empty($reqpost['username']) || !empty($reqpost['usecrypt'])) {
                $tpl->SetBlock("$block/login_step_1/usecrypt/selected");
                $tpl->ParseBlock("$block/login_step_1/usecrypt/selected");
            }
            $tpl->ParseBlock("$block/login_step_1/usecrypt");
        }

        // domain
        if ($this->gadget->registry->fetch('multi_domain') == 'true') {
            $domains = $this->gadget->model->load('Domains')->getDomains();
            if (!Jaws_Error::IsError($domains) && !empty($domains)) {
                $tpl->SetBlock("$block/login_step_1/multi_domain");
                $tpl->SetVariable('lbl_domain', _t('USERS_DOMAIN'));
                array_unshift($domains, array('id' => 0, 'title' => _t('USERS_NODOMAIN')));
                foreach ($domains as $domain) {
                    $tpl->SetBlock("$block/login_step_1/multi_domain/domain");
                    $tpl->SetVariable('id', $domain['id']);
                    $tpl->SetVariable('title', $domain['title']);
                    $tpl->SetVariable('selected', ($domain['id'] == $reqpost['domain'])? 'selected="selected"': '');
                    $tpl->ParseBlock("$block/login_step_1/multi_domain/domain");
                }
                $tpl->ParseBlock("$block/login_step_1/multi_domain");
            }
        }

        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('lbl_password', _t('GLOBAL_PASSWORD'));

        // remember
        $tpl->SetBlock("$block/login_step_1/remember");
        $tpl->SetVariable('lbl_remember', _t('GLOBAL_REMEMBER_ME'));
        if (!empty($reqpost['remember'])) {
            $tpl->SetBlock("$block/login_step_1/remember/selected");
            $tpl->ParseBlock("$block/login_step_1/remember/selected");
        }
        $tpl->ParseBlock("$block/login_step_1/remember");

        $tpl->ParseBlock("$block/login_step_1");
    }

    /**
     * Get HTML login form
     *
     * @access  public
     * @return  string  XHTML template of the login form
     */
    private function LoginBoxStep2(&$tpl, $reqpost)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/login_step_2");

        $tpl->SetVariable('remember', $reqpost['remember']);
        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');

        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('lbl_loginkey', _t('GLOBAL_LOGINKEY'));

        $tpl->ParseBlock("$block/login_step_2");
    }

}