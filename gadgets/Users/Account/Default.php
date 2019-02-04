<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Default extends Jaws_Gadget_Action
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
            $reqpost['password'] = '';
            $reqpost['authstep'] = 0;
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
        } else {
            $reqpost = $response['data'];
        }

        // set session key/value for check through login process
        $this->gadget->session->insert('checksess', 1);

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
            $this->gadget->session->insert('checksess', 1);
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
            $reqpost['password'] = '';
            $reqpost['authstep'] = 0;
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
            //$reqpost['referrer'] = bin2hex(Jaws_Utils::getRequestURL(true));
        } else {
            $reqpost = $response['data'];
        }

        // set session key/value for check through login process
        $this->gadget->session->insert('checksess', 1);

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

        $tpl->SetVariable('usecrypt', $reqpost['usecrypt']);
        $tpl->SetVariable('remember', $reqpost['remember']);
        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('password', isset($reqpost['password'])? $reqpost['password'] : '');

        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('lbl_loginkey', _t('GLOBAL_LOGINKEY'));

        $tpl->ParseBlock("$block/login_step_2");
    }

    /**
     * Authenticate
     *
     * @access  public
     * @return  void
     */
    function Authenticate()
    {
        $httpAuthEnabled = false;
        if (isset($_SERVER['PHP_AUTH_USER']) &&
            (jaws()->request->method() != 'post') &&
            $GLOBALS['app']->Registry->fetch('http_auth', 'Settings') == 'true'
        ) {
            $httpAuthEnabled = true;
            $httpAuth = new Jaws_HTTPAuth();
            $httpAuth->AssignData();
            jaws()->request->update('username', $httpAuth->getUsername(), 'post');
            jaws()->request->update('password', $httpAuth->getPassword(), 'post');
            jaws()->request->update('usecrypt', 0, 'post');
        }

        $loginData = $this->gadget->request->fetch(
            array('domain', 'username', 'password', 'usecrypt', 'loginkey', 'authstep', 'remember'),
            'post'
        );

        try {
            // check captcha
            if (!$httpAuthEnabled) {
                $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                $resCheck = $htmlPolicy->checkCaptcha('login');
                if (Jaws_Error::IsError($resCheck)) {
                    throw new Exception($resCheck->getMessage(), 401);
                }
            }

            $loginData['authstep'] = 0;
            if ($loginData['username'] === '' && $loginData['password'] === '') {
                throw new Exception(_t('GLOBAL_ERROR_LOGIN_WRONG'), 401);
            }

            if ($loginData['usecrypt']) {
                $JCrypt = Jaws_Crypt::getInstance();
                if (!Jaws_Error::IsError($JCrypt)) {
                    $loginData['password'] = $JCrypt->decrypt($loginData['password']);
                }
            } else {
                $loginData['password'] = Jaws_XSS::defilter($loginData['password']);
            }

            // set default domain if not set
            if (is_null($loginData['domain'])) {
                $loginData['domain'] = (int)$this->gadget->registry->fetch('default_domain');
            }

            // fetch user information from database
            $userModel = $GLOBALS['app']->loadObject('Jaws_User');
            $user = $userModel->VerifyUser($loginData['domain'], $loginData['username'], $loginData['password']);
            if (Jaws_Error::isError($user)) {
                throw new Exception($user->getMessage(), 401);
            }

            // fetch user groups
            $groups = $userModel->GetGroupsOfUser($user['id']);
            if (Jaws_Error::IsError($groups)) {
                $groups = array();
            }

            // FIXME: we must find better way for use password in extra protocols ex. IMAP
            $user['password'] = $loginData['password'];
            $user['groups'] = $groups;
            $user['avatar'] = $userModel->GetAvatar(
                $user['avatar'],
                $user['email'],
                48,
                $user['last_update']
            );
            $user['internal'] = true;
            $user['remember'] = (bool)$loginData['remember'];

            // two step verification?
            if ((bool)$GLOBALS['app']->Registry->fetchByUser($user['id'], 'two_step_verification', 'Users'))
            {
                // going to next authentication/verification step
                $loginData['authstep'] = 1;
                // check login key
                $loginkey = $this->gadget->session->fetch('loginkey');
                if (!isset($loginkey['text']) || ($loginkey['time'] < (time() - 300))) {
                    $loginkey = array(
                        'text' => Jaws_Utils::RandomText(5, true, false, true),
                        'time' => time()
                    );
                    $this->gadget->session->update('loginkey', $loginkey);
                    // notify
                    $params = array();
                    $params['key']     = crc32('Session.Loginkey.' . $GLOBALS['app']->Session->GetAttribute('sid'));
                    $params['title']   = _t('GLOBAL_LOGINKEY_TITLE');
                    $params['summary'] = _t(
                        'GLOBAL_LOGINKEY_SUMMARY',
                        $loginkey['text']
                    );
                    $params['description'] = $params['summary'];
                    $params['emails']  = array($user['email']);
                    $params['mobiles'] = array($user['mobile']);
                    $GLOBALS['app']->Listener->Shout('Users', 'Notify', $params);
                }
                // check verification key
                if ($loginkey['text'] != $loginData['loginkey']) {
                    throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);
                }
            }

            // check user concurrents logins
            $existSessions = 0;
            if (!empty($user['concurrents'])) {
                $existSessions = $GLOBALS['app']->Session->GetUserSessions($user['id'], true);
            }
            if (!empty($existSessions) && $existSessions >= $user['concurrents']) {
                // login conflict event logging
                $GLOBALS['app']->Listener->Shout(
                    'Session',
                    'Log',
                    array('Users', 'Login', JAWS_WARNING, null, 403, $user['id'])
                );

                throw new Exception(_t('GLOBAL_ERROR_LOGIN_CONCURRENT_REACHED'), 409);
            }

            // remove login trying count from session
            $this->gadget->session->delete('bad_login_count');
            // remove login key
            $this->gadget->session->delete('loginkey');

            if (!$this->gadget->session->fetch('checksess')) {
                // do logout
                $GLOBALS['app']->Session->Logout();
                throw new Exception(_t('GLOBAL_ERROR_SESSION_NOTFOUND'), 404);
            }

            return $user;
        } catch (Exception $error) {
            // increment login trying count in session
            $this->gadget->session->update(
                'bad_login_count',
                (int)$this->gadget->session->fetch('bad_login_count') + 1
            );

            $this->gadget->session->push(
                $error->getMessage(),
                'Login.Response',
                RESPONSE_ERROR,
                $loginData
            );
            if ($httpAuthEnabled) {
                return $this->gadget->action->loadAdmin('Login')->Login();
            }

            return Jaws_Error::raiseError($error->getMessage(), $error->getCode());
        }

    }

    /**
     * Authorize
     *
     * @access  public
     * @return  void
     */
    function Authorize($loginData = null)
    {
        // check password age
        $password_max_age = (int)$GLOBALS['app']->Registry->fetch('password_max_age', 'Policy');
        if ($password_max_age > 0) {
            $expPasswordTime = time() - 3600 * $password_max_age;
            if ((int)$loginData['last_password_update'] <= $expPasswordTime) {
                $this->gadget->session->push(
                    _t('GLOBAL_ERROR_PASSWORD_EXPIRED'),
                    'Account.Response',
                    RESPONSE_WARNING,
                    $loginData
                );
                return Jaws_Header::Location($this->gadget->urlMap('Account'));
            }
        }

        return true;
    }

    /**
     * Login Error
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginError($error, $authtype, $referrer)
    {
        $urlParams = array();
        if (!empty($authtype)) {
            $urlParams['authtype'] = $authtype;
        }
        if (!empty($referrer)) {
            $urlParams['referrer'] = $referrer;
        }

        http_response_code($error->getCode());
        if (JAWS_SCRIPT == 'index') {
            return Jaws_Header::Location($this->gadget->urlMap('Login', $urlParams));
        } else {
            $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
            $admin_script = empty($admin_script)? 'admin.php' : $admin_script;
            return Jaws_Header::Location($admin_script . (empty($referrer)? '' : "?referrer=$referrer"));
        }

    }

}