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
                    _t('USERS_FORGOT_RECOVERY_SUCCESS', $this->gadget->urlMap('Login'))
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
            $login_url = $this->gadget->urlMap('Login', array('referrer'  => $referrer));

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
     * Builds the login box
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Login()
    {
        if ($GLOBALS['app']->Session->Logged()) {
            return $this->LoginLinks();
        }

        http_response_code(401);
        // 
        $authtype = $this->gadget->request->fetch('authtype');
        if (empty($authtype)) {
            $authtype = $this->gadget->registry->fetch('authtype');
        }
        $authtype = preg_replace('/[^[:alnum:]_\-]/', '', $authtype);
        $authfile = JAWS_PATH . "gadgets/Users/Account/$authtype.php";
        if (!file_exists($authfile)) {
            $GLOBALS['log']->Log(
                JAWS_LOG_NOTICE,
                $authtype. ' authentication driver doesn\'t exists, switched to default driver'
            );
            $authtype = 'Default';
        }
        // set authentication type in session
        $this->gadget->session->update('authtype', $authtype);

        // store referrer in session
        $referrer = $this->gadget->request->fetch('referrer');
        if (empty($referrer)) {
            $referrer = bin2hex(Jaws_Utils::getRequestURL());
        }
        $this->gadget->session->update('referrer', $referrer);

        // load authentication method driver
        $classname = "Users_Account_$authtype";
        $objAccount = new $classname($this->gadget);
        return $objAccount->Login();
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
        // fetch authentication type from session
        $authtype = $this->gadget->session->fetch('authtype');
        if (empty($authtype)) {
            return Jaws_HTTPError::Get(401, '', 'Authentication type is not valid!');
        }

        // parse referrer url
        $referrer = parse_url(hex2bin($this->gadget->session->fetch('referrer')));
        foreach ($referrer as $part => $value) {
            if (in_array($part, array('path', 'query', 'fragment'))) {
                $referrer[$part] = implode('/', array_map('rawurlencode', explode('/', $value)));
            } else {
                // unset schema|host|port|user|pass for security reason
                $referrer[$part] = null;
            }
        }
        $referrer = str_replace(array('%2C', '%3D', '%26'), array(',', '=', '&'), build_url($referrer));

        $classname = "Users_Account_$authtype";
        $objAccount = new $classname($this->gadget);
        $loginData = $objAccount->Authenticate();
        if (Jaws_Error::IsError($loginData)) {
            if (method_exists($objAccount, 'LoginError')) {
                $default_authtype = $this->gadget->registry->fetch('authtype');
                return $objAccount->LoginError(
                    $loginData,
                    ($authtype != $default_authtype)? $authtype : '',
                    bin2hex($referrer)
                );
            }
        } else {
            $loginData['authtype'] = $authtype;
            // create session & cookie
            $GLOBALS['app']->Session->Create($loginData, $loginData['remember']);
            // login event logging
            $GLOBALS['app']->Listener->Shout('Session', 'Log', array('Users', 'Login', JAWS_NOTICE));
            // let everyone know a user has been logged in
            $this->gadget->event->shout('LoginUser', $loginData);

            // call Authorize method if exists
            if (method_exists($objAccount, 'Authorize')) {
                $objAccount->Authorize($loginData);
            }
        }

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

        if (JAWS_SCRIPT == 'index') {
            return Jaws_Header::Location();
        } else {
            $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
            return Jaws_Header::Location($admin_script?: 'admin.php');
        }
    }

}