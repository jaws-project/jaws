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
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('LoginBox.html');
        $tpl->SetBlock('LoginBox');
        $tpl->SetVariable('title', _t('USERS_LOGIN_TITLE'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $response = $this->gadget->session->pop('Login.Response');
        if (!isset($response['data'])) {
            //$referrer  = $this->gadget->request->fetch('referrer', 'get');
            $reqpost['username'] = '';
            $reqpost['password'] = '';
            $reqpost['authstep'] = 0;
            $reqpost['authtype'] = '';
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
            //$reqpost['referrer'] = is_null($referrer)? bin2hex(Jaws_Utils::getRequestURL(true)) : $referrer;
            $reqpost['referrer'] = bin2hex(Jaws_Utils::getRequestURL(true));
        } else {
            $reqpost = $response['data'];
        }

        // set session key/value for check through login process
        $this->gadget->session->insert('checksess', 1);

        if (is_null($reqpost['authtype'])) {
            $reqpost['authtype'] = $this->gadget->request->fetch('authtype', 'get');
        }

        // global variables
        $tpl->SetVariable('referrer', $reqpost['referrer']);
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
     * Get HTML login form
     *
     * @access  public
     * @return  string  XHTML template of the login form
     */
    private function LoginBoxStep1(&$tpl, $reqpost)
    {
        $tpl->SetBlock('LoginBox/login_step_1');

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $tpl->SetBlock('LoginBox/login_step_1/encryption');
            $tpl->SetVariable('pubkey', $JCrypt->getPublic());
            $tpl->ParseBlock('LoginBox/login_step_1/encryption');

            // usecrypt
            $tpl->SetBlock('LoginBox/login_step_1/usecrypt');
            $tpl->SetVariable('lbl_usecrypt', _t('GLOBAL_LOGIN_SECURE'));
            if (empty($reqpost['username']) || !empty($reqpost['usecrypt'])) {
                $tpl->SetBlock('LoginBox/login_step_1/usecrypt/selected');
                $tpl->ParseBlock('LoginBox/login_step_1/usecrypt/selected');
            }
            $tpl->ParseBlock('LoginBox/login_step_1/usecrypt');
        }

        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('lbl_password', _t('GLOBAL_PASSWORD'));

        $authtype = $this->gadget->registry->fetch('authtype', 'Users');
        if (!empty($reqpost['authtype']) || $authtype !== 'Default') {
            $authtype = is_null($reqpost['authtype'])? $authtype : $reqpost['authtype'];
            $tpl->SetBlock('LoginBox/login_step_1/authtype');
            $tpl->SetVariable('lbl_authtype', _t('GLOBAL_AUTHTYPE'));
            foreach ($GLOBALS['app']->GetAuthTypes() as $method) {
                $tpl->SetBlock('LoginBox/login_step_1/authtype/item');
                $tpl->SetVariable('method', $method);
                if ($method == $authtype) {
                    $tpl->SetVariable('selected', 'selected="selected"');
                } else {
                    $tpl->SetVariable('selected', '');
                }
                $tpl->ParseBlock('LoginBox/login_step_1/authtype/item');
            }
            $tpl->ParseBlock('LoginBox/login_step_1/authtype');
        }

        // remember
        $tpl->SetBlock('LoginBox/login_step_1/remember');
        $tpl->SetVariable('lbl_remember', _t('GLOBAL_REMEMBER_ME'));
        if (!empty($reqpost['remember'])) {
            $tpl->SetBlock('LoginBox/login_step_1/remember/selected');
            $tpl->ParseBlock('LoginBox/login_step_1/remember/selected');
        }
        $tpl->ParseBlock('LoginBox/login_step_1/remember');

        $tpl->ParseBlock('LoginBox/login_step_1');
    }

    /**
     * Get HTML login form
     *
     * @access  public
     * @return  string  XHTML template of the login form
     */
    private function LoginBoxStep2(&$tpl, $reqpost)
    {
        $tpl->SetBlock('LoginBox/login_step_2');

        $tpl->SetVariable('usecrypt', $reqpost['usecrypt']);
        $tpl->SetVariable('authtype', $reqpost['authtype']);
        $tpl->SetVariable('remember', $reqpost['remember']);
        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('password', isset($reqpost['password'])? $reqpost['password'] : '');

        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('lbl_loginkey', _t('GLOBAL_LOGINKEY'));

        $tpl->ParseBlock('LoginBox/login_step_2');
    }

    /**
     * Logins user, if something goes wrong then redirect user to previous page
     * and notify the error
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
            jaws()->request->update('referrer', bin2hex(Jaws_Utils::getRequestURL(true)), 'post');
            jaws()->request->update('usecrypt', 0, 'post');
        }

        $loginData = $this->gadget->request->fetch(
            array('username', 'password', 'usecrypt', 'loginkey', 'authstep', 'referrer', 'remember', 'authtype'),
            'post'
        );

        try {
            // check captcha
            if (!$httpAuthEnabled) {
                $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                $resCheck = $htmlPolicy->checkCaptcha('login');
                if (Jaws_Error::IsError($resCheck)) {
                    throw new Exception($resCheck->getMessage());
                }
            }

            $loginData['authstep'] = 0;
            if ($loginData['username'] === '' && $loginData['password'] === '') {
                throw new Exception(_t('GLOBAL_ERROR_LOGIN_WRONG'));
            }

            if ($loginData['usecrypt']) {
                $JCrypt = Jaws_Crypt::getInstance();
                if (!Jaws_Error::IsError($JCrypt)) {
                    $loginData['password'] = $JCrypt->decrypt($loginData['password']);
                }
            } else {
                $loginData['password'] = Jaws_XSS::defilter($loginData['password']);
            }

            // fetch user information from database
            $userModel = $GLOBALS['app']->loadObject('Jaws_User');
            $user = $userModel->VerifyUser($loginData['username'], $loginData['password']);
            if (Jaws_Error::isError($user)) {
                throw new Exception($user->getMessage());
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
                    $params['key']     = crc32('Session.Loginkey.' . $GLOBALS['app']->GetAttribute('sid'));
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
                    throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'));
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

                throw new Exception(_t('GLOBAL_ERROR_LOGIN_CONCURRENT_REACHED'));
            }

            // remove login trying count from session
            $this->gadget->session->delete('bad_login_count');
            // remove login key
            $this->gadget->session->delete('loginkey');

            // create session & cookie
            $GLOBALS['app']->Session->Create($user, (bool)$loginData['remember']);
            // login event logging
            $GLOBALS['app']->Listener->Shout('Session', 'Log', array('Users', 'Login', JAWS_NOTICE));
            // let everyone know a user has been logged
            $GLOBALS['app']->Listener->Shout('Session', 'LoginUser', $GLOBALS['app']->Session->GetAttributes());

            // check password age
            $password_max_age = (int)$GLOBALS['app']->Registry->fetch('password_max_age', 'Policy');
            if ($password_max_age > 0) {
                $expPasswordTime = time() - 3600 * $password_max_age;
                if ((int)$user['last_password_update'] <= $expPasswordTime) {
                    $this->gadget->session->push(
                        _t('GLOBAL_ERROR_PASSWORD_EXPIRED'),
                        'Account.Response',
                        RESPONSE_WARNING,
                        $loginData
                    );
                    return Jaws_Header::Location($this->gadget->urlMap('Account'));
                }
            }

            if (!$this->gadget->session->fetch('checksess')) {
                // do logout
                $GLOBALS['app']->Session->Logout();
                throw new Exception(_t('GLOBAL_ERROR_SESSION_NOTFOUND'));
            }

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
        }

        $referrer = parse_url(hex2bin($loginData['referrer']));
        $referrer = (array_key_exists('path', $referrer)? $referrer['path'] : '') . 
                    (array_key_exists('query', $referrer)? "?{$referrer['query']}" : '') . 
                    (array_key_exists('fragment', $referrer)? "#{$referrer['fragment']}" : '');
        return Jaws_Header::Location($referrer);
    }

}