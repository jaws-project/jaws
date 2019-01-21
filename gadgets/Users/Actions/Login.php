<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Actions_Login extends Jaws_Gadget_Action
{
    /**
     * Verifies if user/email/(captcha) are valid, if they are a mail
     * is sent to user with a secret(MD5) key
     *
     * @access  public
     * @return  void
     */
    function LoginRecovery()
    {
        if ($this->gadget->registry->fetch('password_recovery') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        $post = $this->gadget->request->fetch(array('step', 'email', 'key'), 'post');

        $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $htmlPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushResponse(
                $resCheck->GetMessage(),
                'Users.LoginForgot',
                RESPONSE_ERROR,
                $post
            );
            return Jaws_Header::Location($this->gadget->urlMap('LoginForgot'));
        }

        if (empty($post['step'])) {
            $result = $this->gadget->model->load('Registration')->SendLoginRecoveryKey($post['email']);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushResponse(
                    $result->GetMessage(),
                    'Users.LoginForgot',
                    RESPONSE_ERROR,
                    $post
                );
            } else {
                $post['step'] = 1;
                $GLOBALS['app']->Session->PushResponse(
                    _t('USERS_FORGOT_REQUEST_SENT'),
                    'Users.LoginForgot',
                    RESPONSE_NOTICE,
                    $post
                );
            }
        } else {
            $result = $this->gadget->model->load('Account')->UpdatePassword($post['email'], $post['key']);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushResponse(
                    $result->getMessage(),
                    'Users.LoginForgot',
                    RESPONSE_ERROR,
                    $post
                );
            } else {
                $post['step'] = 2;
                $GLOBALS['app']->Session->PushResponse(
                    _t('USERS_FORGOT_PASSWORD_CHANGED'),
                    'Users.LoginForgot',
                    RESPONSE_NOTICE,
                    $post
                );
            }
        }

        return Jaws_Header::Location($this->gadget->urlMap('LoginForgot'));
    }

    /**
     * Builds password recovery UI
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginForgot()
    {
        if ($this->gadget->registry->fetch('password_recovery') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        $response = $GLOBALS['app']->Session->PopResponse('Users.LoginForgot');
        if (!isset($response['data'])) {
            $post = array(
                'step'  => 0,
                'email' => '',
                'key'   => '',
            );
        } else {
            $post = $response['data'];
            $post['step'] = (int)$post['step'];
        }

        // Load the template
        $tpl = $this->gadget->template->load('LoginForgot.html');
        $tpl->SetBlock('forgot');
        $tpl->SetVariable('step', (int)$post['step']);
        $tpl->SetVariable('title', _t('USERS_FORGOT_REMEMBER'));

        switch ($post['step']) {
            case 2:
                $tpl->SetBlock('forgot/success');
                $tpl->SetVariable(
                    'message',
                    _t('USERS_FORGOT_RECOVERY_SUCCESS', $this->gadget->urlMap('LoginBox'))
                );
                $tpl->ParseBlock('forgot/success');
                break;

            case 1:
                $tpl->SetBlock('forgot/key');
                $tpl->SetVariable('lbl_key', _t('USERS_FORGOT_RECOVERY_KEY'));
                $tpl->SetVariable('key', $post['key']);
                $tpl->ParseBlock('forgot/key');
                // without break

            default:
                $tpl->SetBlock('forgot/email');
                $tpl->SetVariable('lbl_term', _t('USERS_FORGOT_TERM'));
                $tpl->SetVariable('email', $post['email']);
                if ($post['step']) {
                    $tpl->SetBlock('forgot/email/readonly');
                    $tpl->ParseBlock('forgot/email/readonly');
                }
                $tpl->ParseBlock('forgot/email');
                //captcha
                $tpl->SetBlock('forgot/captcha');
                $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                $mPolicy->loadCaptcha($tpl, 'forgot/captcha');
                $tpl->ParseBlock('forgot/captcha');
                // action
                $tpl->SetBlock('forgot/action');
                $tpl->SetVariable('remember', _t('GLOBAL_REQUEST'));
                $tpl->ParseBlock('forgot/action');
        }

        if ($response = $GLOBALS['app']->Session->PopResponse('Users.LoginForgot')) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }
        $tpl->ParseBlock('forgot');
        return $tpl->Get();
    }

    /**
     * Builds the login box
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginBox()
    {
        if ($GLOBALS['app']->Session->Logged()) {
            return $this->LoginLinks();
        }

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
     * Builds the login links
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginLinks()
    {
        $tpl = $this->gadget->template->load('LoginLinks.html');

        if ($GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('UserLinks');
            $tpl->SetVariable('title', _t('GLOBAL_MY_ACCOUNT'));

            // welcome
            $tpl->SetVariable('welcome', _t('USERS_WELCOME'));
            $tpl->SetVariable('profile', _t('USERS_PROFILE'));
            $uInfo = $GLOBALS['app']->Session->GetAttributes('username', 'nickname', 'avatar', 'email');
            // username
            $tpl->SetVariable('username',  $uInfo['username']);
            // nickname
            $tpl->SetVariable('nickname',  $uInfo['nickname']);
            // avatar
            $tpl->SetVariable('avatar', $uInfo['avatar']);

            // profile link
            $tpl->SetVariable(
                'profile_url',
                $this->gadget->urlMap('Profile', array('user' => $uInfo['username']))
            );
            // email
            $tpl->SetVariable('email',  $uInfo['email']);

            // manage friends
            if ($this->gadget->GetPermission('ManageFriends')) {
                $tpl->SetBlock('UserLinks/groups');
                $tpl->SetVariable('user_groups', _t('USERS_FRIENDS'));
                $tpl->SetVariable('groups_url', $this->gadget->urlMap('FriendsGroups'));
                $tpl->ParseBlock('UserLinks/groups');
            }

            // fetch current layout user
            $layout_user = (int)$GLOBALS['app']->Session->GetAttribute('layout');
            $logged_user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            // Layout/Dashboard manager
            if (empty($layout_user)) {
                // global site layout
                if ($GLOBALS['app']->Session->GetPermission('Layout', 'ManageLayout')) {
                    $tpl->SetBlock('UserLinks/layout');
                    $tpl->SetVariable('layout', _t('LAYOUT_TITLE'));
                    $tpl->SetVariable(
                        'layout_url',
                        $this->gadget->urlMap('Layout', array(), array(), 'Layout')
                    );
                    $tpl->ParseBlock('UserLinks/layout');
                }
            } else {
                // user's dashboard layout
                if ($this->gadget->GetPermission('ManageDashboard')) {
                    $tpl->SetBlock('UserLinks/layout');
                    $tpl->SetVariable('layout', _t('LAYOUT_TITLE'));
                    $tpl->SetVariable(
                        'layout_url',
                        $this->gadget->urlMap('Layout', array('layout' => 'Index.Dashboard'), array(), 'Layout')
                    );
                    $tpl->ParseBlock('UserLinks/layout');
                }
            }

            // Dashboard
            if ($this->gadget->GetPermission('AccessDashboard')) {
                $tpl->SetBlock('UserLinks/dashboard');
                if (empty($layout_user)) {
                    $tpl->SetVariable('dashboard', _t('USERS_DASHBOARD_USER'));
                    $tpl->SetVariable(
                        'dashboard_url',
                        $this->gadget->urlMap('Dashboard', array('user' => $logged_user), array(), 'Layout')
                    );
                } else {
                    $tpl->SetVariable('dashboard', _t('USERS_DASHBOARD_GLOBAL'));
                    $tpl->SetVariable(
                        'dashboard_url',
                        $this->gadget->urlMap('Dashboard', array('user' => 0), array(), 'Layout')
                    );
                }
                $tpl->ParseBlock('UserLinks/dashboard');
            }

            // ControlPanel
            if ($this->gadget->GetPermission('default_admin', '', array(), 'ControlPanel')) {
                $tpl->SetBlock('UserLinks/cpanel');
                $tpl->SetVariable('cpanel', _t('USERS_CONTROLPANEL'));
                $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
                $tpl->SetVariable('cpanel_url', empty($admin_script)? 'admin.php' : $admin_script);
                $tpl->ParseBlock('UserLinks/cpanel');
            }

            // Logout
            $tpl->SetVariable('logout', _t('GLOBAL_LOGOUT'));
            $tpl->SetVariable('logout_url', $this->gadget->urlMap('Logout'));

            $tpl->ParseBlock('UserLinks');
        } else {
            $referrer  = $this->gadget->request->fetch('referrer', 'get');
            $referrer  = is_null($referrer)? bin2hex(Jaws_Utils::getRequestURL(true)) : $referrer;
            $login_url = $this->gadget->urlMap('LoginBox', array('referrer'  => $referrer));

            $tpl->SetBlock('LoginLinks');
            $tpl->SetVariable('title', _t('USERS_LOGINLINKS'));

            // welcome
            $tpl->SetVariable('welcome', _t('USERS_WELCOME'));

            // login
            $tpl->SetVariable('user_login', _t('USERS_LOGIN_TITLE'));
            $tpl->SetVariable('login_url', $login_url);

            // registration
            if ($this->gadget->registry->fetch('anon_register') == 'true') {
                $tpl->SetBlock('LoginLinks/registration');
                $tpl->SetVariable('user_registeration', _t('USERS_REGISTER'));
                $tpl->SetVariable('registeration_url',  $this->gadget->urlMap('Registration'));
                $tpl->ParseBlock('LoginLinks/registration');
            }

            // forget user/password
            if ($this->gadget->registry->fetch('password_recovery') == 'true') {
                $tpl->SetBlock('LoginLinks/forgot');
                $tpl->SetVariable('user_forgot', _t('USERS_FORGOT_LOGIN'));
                $tpl->SetVariable('forgot_url',  $this->gadget->urlMap('LoginForgot'));
                $tpl->ParseBlock('LoginLinks/forgot');
            }

            $tpl->ParseBlock('LoginLinks');
        }

        return $tpl->Get();
    }

    /**
     * Logins user, if something goes wrong then redirect user to previous page
     * and notify the error
     *
     * @access  public
     * @return  void
     */
    function Login()
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

            // load authentication method driver
            if (!empty($loginData['authtype'])) {
                $authtype = preg_replace('/[^[:alnum:]_\-]/', '', $loginData['authtype']);
            } else {
                //$authtype = $GLOBALS['app']->Session->_AuthType;
                $authtype = 'Default';
            }
            $className = 'Jaws_Auth_' . $authtype;
            $modelAuth = new $className();

            // try authenticate user
            $user = $modelAuth->Auth($loginData);
            if (Jaws_Error::isError($user)) {
                throw new Exception($user->getMessage());
            }

            // two step verification?
            if ((bool)$GLOBALS['app']->Registry->fetchByUser($user['id'], 'two_step_verification', 'Users'))
            {
                // going to next authentication/verification step
                $loginData['authstep'] = 1;
                // check login key
                $loginkey = $GLOBALS['app']->Session->GetAttribute('loginkey');
                if (!isset($loginkey['text']) || ($loginkey['time'] < (time() - 300))) {
                    $loginkey = array(
                        'text' => Jaws_Utils::RandomText(5, true, false, true),
                        'time' => time()
                    );
                    $GLOBALS['app']->Session->SetAttribute('loginkey', $loginkey);
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
            $GLOBALS['app']->Session->DeleteAttribute('bad_login_count');
            // remove login verification key
            $GLOBALS['app']->Session->DeleteAttribute('verification_key');

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
            $GLOBALS['app']->Session->SetAttribute(
                'bad_login_count',
                (int)$GLOBALS['app']->Session->GetAttribute('bad_login_count') + 1
            );

            $this->gadget->session->push(
                $error->getMessage(),
                'Login.Response',
                RESPONSE_ERROR,
                $loginData
            );
            if ($httpAuthEnabled) {
                return $this->gadget->action->loadAdmin('Login')->LoginBox();
            }
        }

        $referrer = parse_url(hex2bin($loginData['referrer']));
        $referrer = (array_key_exists('path', $referrer)? $referrer['path'] : '') . 
                    (array_key_exists('query', $referrer)? "?{$referrer['query']}" : '') . 
                    (array_key_exists('fragment', $referrer)? "#{$referrer['fragment']}" : '');
        return Jaws_Header::Location($referrer);
    }

    /**
     * Logout user
     *
     * @access  public
     * @return  void
     */
    function Logout()
    {
        $GLOBALS['app']->Session->Logout();
        return Jaws_Header::Location();
    }

}