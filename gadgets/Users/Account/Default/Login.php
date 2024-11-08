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
     * @param   string  $referrer   Referrer page url
     * @return  string  XHTML content
     */
    function Login($defaults = '', $referrer = '')
    {
        return (JAWS_SCRIPT == 'index')? $this->IndexLogin($referrer) : $this->AdminLogin($referrer);
    }

    /**
     * Builds the front-end login box
     *
     * @access  public
     * @param   string  $referrer   Referrer page url
     * @return  string  XHTML content
     */
    function IndexLogin($referrer)
    {
        $this->AjaxMe('index.js');
        if ($this->app->requestedActionMode === 'normal') {
            $tFilename = 'Login.html';
        } else {
            $tFilename = 'Login0.html';
        }

        $tpl = $this->gadget->template->load($tFilename);
        $tpl->SetBlock('login');

        $response = $this->gadget->session->pop('Login.Response');
        if (!isset($response['data'])) {
            $reqpost['domain'] = $this->gadget->registry->fetch('default_domain');
            $reqpost['username'] = '';
            $reqpost['loginstep'] = 1;
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
        } else {
            $reqpost = $response['data'];
            $reqpost['loginstep'] = (int)$reqpost['loginstep'];
        }

        $tpl->SetVariable('title', $this::t("login_title_step_{$reqpost['loginstep']}"));
        $tpl->SetBlock("login/login_step_{$reqpost['loginstep']}");
        switch ($reqpost['loginstep']) {
            case 2:
                $this->LoginBoxStep2($tpl, $reqpost, $referrer);
                break;

            case 3:
                $this->LoginBoxStep3($tpl, $reqpost, $referrer);
                break;

            default:
                $this->LoginBoxStep1($tpl, $reqpost, $referrer);
        }

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock("login/login_step_{$reqpost['loginstep']}");
        $tpl->ParseBlock('login');
        return $tpl->Get();
    }

    /**
     * Builds the backend login box
     *
     * @access  public
     * @param   string  $referrer   Referrer page url
     * @return  string  XHTML content
     */
    function AdminLogin($referrer)
    {
        $this->AjaxMe('script.js');
        // Init layout
        $this->app->layout->Load('gadgets/Users/Templates/Admin', 'Login.html');
        $ltpl =& $this->app->layout->_Template;
        $ltpl->SetVariable('admin-script', BASE_SCRIPT);
        $ltpl->SetVariable('control-panel', Jaws::t('CONTROLPANEL'));

        $response = $this->gadget->session->pop('Login.Response');
        if (!isset($response['data'])) {
            $reqpost['domain'] = $this->gadget->registry->fetch('default_domain');
            $reqpost['username'] = '';
            $reqpost['loginstep'] = 0;
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
        } else {
            $reqpost = $response['data'];
            $reqpost['loginstep'] = (int)$reqpost['loginstep'];
        }

        //
        $ltpl->SetVariable('legend_title', Jaws_Gadget::t('CONTROLPANEL.LOGIN_TITLE'));

        if ($reqpost['loginstep'] == 3) {
            $this->LoginBoxStep3($ltpl, $reqpost, $referrer);
        } elseif ($reqpost['loginstep'] == 2) {
            $this->LoginBoxStep2($ltpl, $reqpost, $referrer);
        } else {
            $this->LoginBoxStep1($ltpl, $reqpost, $referrer);
        }

        $ltpl->SetVariable('login', Jaws::t('LOGIN'));
        $ltpl->SetVariable('url_back', $this->app->getSiteURL('/'));
        $ltpl->SetVariable('lbl_back', Jaws_Gadget::t('CONTROLPANEL.LOGIN_BACK_TO_SITE'));

        if (!empty($response)) {
            $ltpl->SetVariable('response_type', $response['type']);
            $ltpl->SetVariable('response_text', $response['text']);
        }

        return $this->app->layout->Get();
    }

    /**
     * Get HTML login form
     *
     * @access  public
     * @return  string  XHTML template of the login form
     */
    public function LoginBoxStep1(&$tpl, $reqpost, $referrer)
    {
        http_response_code(401);

        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $tpl->SetBlock("$block/encryption");
            $tpl->SetVariable('pubkey', $JCrypt->getPublic());
            $tpl->ParseBlock("$block/encryption");

            // usecrypt
            $tpl->SetBlock("$block/usecrypt");
            $tpl->SetVariable('lbl_usecrypt', Jaws::t('LOGIN_SECURE'));
            if (empty($reqpost['username']) || !empty($reqpost['usecrypt'])) {
                $tpl->SetBlock("$block/usecrypt/selected");
                $tpl->ParseBlock("$block/usecrypt/selected");
            }
            $tpl->ParseBlock("$block/usecrypt");
        }

        // domain
        if ($this->gadget->registry->fetch('multi_domain') == 'true') {
            $domains = $this->gadget->model->load('Domains')->getDomains();
            if (!Jaws_Error::IsError($domains) && !empty($domains)) {
                $tpl->SetBlock("$block/multi_domain");
                $tpl->SetVariable('lbl_domain', $this::t('DOMAIN'));
                array_unshift($domains, array('id' => 0, 'title' => $this::t('NODOMAIN')));
                foreach ($domains as $domain) {
                    $tpl->SetBlock("$block/multi_domain/domain");
                    $tpl->SetVariable('id', $domain['id']);
                    $tpl->SetVariable('title', $domain['title']);
                    $tpl->SetVariable('selected', ($domain['id'] == $reqpost['domain'])? 'selected="selected"': '');
                    $tpl->ParseBlock("$block/multi_domain/domain");
                }
                $tpl->ParseBlock("$block/multi_domain");
            }
        }

        $tpl->SetVariable('lbl_username', Jaws::t('USERNAME'));
        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('lbl_password', Jaws::t('PASSWORD'));

        // remember
        $tpl->SetBlock("$block/remember");
        $tpl->SetVariable('lbl_remember', Jaws::t('REMEMBER_ME'));
        if (!empty($reqpost['remember'])) {
            $tpl->SetBlock("$block/remember/selected");
            $tpl->ParseBlock("$block/remember/selected");
        }
        $tpl->ParseBlock("$block/remember");

        // display captcha?
        $max_captcha_login_bad_count = (int)$this->gadget->registry->fetch('login_captcha_status', 'Policy');
        if ($this->gadget->action->load('Login')->BadLogins($reqpost['username']) >= $max_captcha_login_bad_count) {
            $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $mPolicy->loadCaptcha($tpl, 'login');
        }

        // global variables
        $tpl->SetVariable('login', Jaws::t('LOGIN'));
        $tpl->SetVariable('url_back', $referrer);
        $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));

        // anon_register
        if ($this->gadget->registry->fetch('anon_register') == 'true') {
            $tpl->SetVariable('lbl_register',  $this::t('REGISTER'));
            $tpl->SetVariable('url_register', $this->gadget->urlMap('Registration'));
        } else {
            $tpl->SetVariable('hidden_register', 'hidden');
        }

        // password_recovery
        if ($this->gadget->registry->fetch('password_recovery') == 'true') {
            $tpl->SetVariable('lbl_forgot', $this::t('FORGOT_LOGIN'));
            $tpl->SetVariable('url_forgot', $this->gadget->urlMap('LoginForgot'));
        } else {
            $tpl->SetVariable('hidden_forgot', 'hidden');
        }
    }

    /**
     * Get HTML login form
     *
     * @access  public
     * @return  string  XHTML template of the login form
     */
    public function LoginBoxStep2(&$tpl, $reqpost, $referrer)
    {
        $block = $tpl->GetCurrentBlockPath();

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('remember', isset($reqpost['remember'])? $reqpost['remember'] : '');
        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');

        $tpl->SetVariable('lbl_username', Jaws::t('USERNAME'));
        $tpl->SetVariable('lbl_loginkey', Jaws::t('LOGINKEY'));

        // display captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl, 'login');

        // global variables
        $tpl->SetVariable('login', Jaws::t('LOGIN'));
        $tpl->SetVariable('url_back', $referrer);
        $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));
    }

    /**
     * Get HTML login form
     *
     * @access  public
     * @return  string  XHTML template of the login form
     */
    public function LoginBoxStep3(&$tpl, $reqpost, $referrer)
    {
        $block = $tpl->GetCurrentBlockPath();

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('remember', $reqpost['remember']);
        $tpl->SetVariable('username', $reqpost['username']);

        $tpl->SetVariable('lbl_username', Jaws::t('USERNAME'));
        $tpl->SetVariable('lbl_password', Jaws::t('PASSWORD'));
        $tpl->SetVariable('lbl_old_password', $this::t('USERS_PASSWORD_OLD'));

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $tpl->SetBlock("$block/encryption");
            $tpl->SetVariable('pubkey', $JCrypt->getPublic());
            $tpl->ParseBlock("$block/encryption");

            // usecrypt
            $tpl->SetBlock("$block/usecrypt");
            $tpl->SetVariable('lbl_usecrypt', Jaws::t('LOGIN_SECURE'));
            if (empty($reqpost['pubkey']) || !empty($reqpost['usecrypt'])) {
                $tpl->SetBlock("$block/usecrypt/selected");
                $tpl->ParseBlock("$block/usecrypt/selected");
            }
            $tpl->ParseBlock("$block/usecrypt");
        }

        // global variables
        $tpl->SetVariable('login', Jaws::t('LOGIN'));
        $tpl->SetVariable('url_back', $referrer);
        $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));
    }

}