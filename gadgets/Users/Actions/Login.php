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
     * Builds the login links
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginLinks()
    {
        $tpl = $this->gadget->template->load('LoginLinks.html');

        if ($this->app->session->user->logged) {
            $tpl->SetBlock('UserLinks');
            $tpl->SetVariable('title', _t('GLOBAL_MY_ACCOUNT'));

            // welcome
            $tpl->SetVariable('welcome', _t('USERS_WELCOME'));

            $response = $this->gadget->session->pop('Login.Response');
            if (empty($response)) {
                $response['type'] = RESPONSE_NOTICE;
                $response['text'] = _t('USERS_WELCOME');
            }
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);

            $tpl->SetVariable('profile', _t('USERS_PROFILE'));
            // username
            $tpl->SetVariable('username', $this->app->session->user->username);
            // nickname
            $tpl->SetVariable('nickname', $this->app->session->user->nickname);
            // avatar
            $tpl->SetVariable('avatar', $this->app->session->user->avatar);

            // profile link
            $tpl->SetVariable(
                'profile_url',
                $this->gadget->urlMap('Profile', array('user' => $this->app->session->user->username))
            );
            // email
            $tpl->SetVariable('email',  $this->app->session->user->email);

            // manage friends
            if ($this->gadget->GetPermission('ManageFriends')) {
                $tpl->SetBlock('UserLinks/groups');
                $tpl->SetVariable('user_groups', _t('USERS_FRIENDS'));
                $tpl->SetVariable('groups_url', $this->gadget->urlMap('FriendsGroups'));
                $tpl->ParseBlock('UserLinks/groups');
            }

            $layoutGadget = Jaws_Gadget::getInstance('Layout');
            if ($layoutGadget->GetPermission('MainLayoutManage')) {
                // link to manage global layout
                $tpl->SetBlock('UserLinks/manage-layout');
                $tpl->SetVariable('layout', _t('LAYOUT_TITLE'));
                $tpl->SetVariable(
                    'layout_url',
                    $layoutGadget->urlMap('Layout')
                );
                $tpl->ParseBlock('UserLinks/manage-layout');
            } elseif ($this->gadget->GetPermission('ManageUserLayout')) {
                // link to manage personal layout
                $tpl->SetBlock('UserLinks/manage-layout');
                $tpl->SetVariable('layout', _t('LAYOUT_TITLE'));
                $tpl->SetVariable(
                    'layout_url',
                    $layoutGadget->urlMap('Layout', array('layout' => 'Index.User'))
                );
                $tpl->ParseBlock('UserLinks/manage-layout');
            }

            // layout type
            if ($this->gadget->GetPermission('AccessUserLayout') ||
                $this->gadget->GetPermission('AccessUsersLayout')
            ) {
                $layout = $layoutGadget->session->layout_type? 0 : 1;

                $tpl->SetBlock('UserLinks/layouts');
                $tpl->SetBlock('UserLinks/layouts/layout');
                $tpl->SetVariable('layout', _t("USERS_DASHBOARD_$layout"));
                $tpl->SetVariable(
                    'layout_url',
                    $layoutGadget->urlMap('LayoutType', array('type' => $layout))
                );
                $tpl->ParseBlock('UserLinks/layouts/layout');
                $tpl->ParseBlock('UserLinks/layouts');
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
        if ($this->app->session->user->logged) {
            return $this->LoginLinks();
        }

        http_response_code(401);
        // get/check given authentication type
        $authtype = $this->gadget->request->fetch('authtype');
        if (empty($authtype)) {
            $authtype = $this->gadget->registry->fetch('authtype');
        }
        $authtype = preg_replace('/[^[:alnum:]_\-]/', '', $authtype);
        $drivers = array_map('basename', glob(ROOT_JAWS_PATH . 'gadgets/Users/Account/*', GLOB_ONLYDIR));
        if (false === $dIndex = array_search(strtolower($authtype), array_map('strtolower', $drivers))) {
            $GLOBALS['log']->Log(
                JAWS_LOG_NOTICE,
                $authtype. ' authentication driver doesn\'t exists, switched to default driver'
            );
            $authtype = 'Default';
        } else {
            $authtype = $drivers[$dIndex];
        }
        $authfile = ROOT_JAWS_PATH . "gadgets/Users/Account/$authtype/Login.php";
        if (!file_exists($authfile)) {
            Jaws_Error::Fatal($authtype. ' authentication driver doesn\'t exists');
        }

        // set authentication type in session
        $this->gadget->session->auth = $authtype;

        // store referrer into session
        $referrer = $this->gadget->request->fetch('referrer');
        if (empty($referrer)) {
            if ($this->app->mainRequest['gadget'] == $this->app->requestedGadget &&
                $this->app->mainRequest['action'] == $this->app->requestedAction
            ) {
                $referrer = '';
            } else {
                $referrer = bin2hex(Jaws_Utils::getRequestURL());
            }
        }
        $this->gadget->session->referrer = $referrer;

        // load authentication method driver
        $classname = "Users_Account_{$authtype}_Login";
        $objAccount = new $classname($this->gadget);
        return $objAccount->Login(Jaws_XSS::filterURL(hex2bin($referrer), true, true));
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
        $authtype = $this->gadget->session->auth;
        if (empty($authtype)) {
            return Jaws_HTTPError::Get(401, '', 'Authentication type is not valid!');
        }

        // parse referrer url
        $referrer = Jaws_XSS::filterURL(hex2bin($this->gadget->session->referrer), true, true);

        $classname = "Users_Account_{$authtype}_Authenticate";
        $objAccount = new $classname($this->gadget);
        $loginData = $objAccount->Authenticate();
        if (Jaws_Error::IsError($loginData)) {
            $default_authtype = $this->gadget->registry->fetch('authtype');
            return $objAccount->AuthenticateError(
                $loginData,
                ($authtype != $default_authtype)? $authtype : '',
                bin2hex($referrer)
            );
        } else {
            $loginData['auth'] = $authtype;
            // create session & cookie
            $this->app->session->create($loginData, $loginData['remember']);
            // login event logging
            $this->gadget->event->shout(
                'Log',
                array(
                    'action'   => 'Login',
                    'auth'     => $loginData['auth'],
                    'domain'   => (int)$loginData['domain'],
                    'username' => $loginData['username'],
                    'priority' => JAWS_NOTICE,
                    'status'   => 200,
                )
            );
            // let everyone know a user has been logged in
            $this->gadget->event->shout('LoginUser', $loginData);
        }

        http_response_code(201);
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
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location('');
        }

        $authtype = $this->app->session->auth;
        $classfile = ROOT_JAWS_PATH . "gadgets/Users/Account/$authtype/Logout.php";
        if (!file_exists($classfile)) {
            Jaws_Error::Fatal($authtype. ' logout class doesn\'t exists');
        }

        // load logout method of account driver
        $classname = "Users_Account_{$authtype}_Logout";
        $objAccount = new $classname($this->gadget);
        $objAccount->Logout();

        // logout from jaws
        $this->app->session->logout();
        if (JAWS_SCRIPT == 'index') {
            return Jaws_Header::Location();
        } else {
            $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
            return Jaws_Header::Location($admin_script?: 'admin.php');
        }

    }

    /**
     * Set/Get bad logins count
     *
     * @access  public
     * @param   string  $username   Username
     * @param   bool    $operation  Operation type(1: increase, 0: get, -1: remove)
     * @return  int     Bad logins count
     */
    function BadLogins($username, $operation = 0)
    {
        $result = 0;
        $memLogins = Jaws_SharedSegment::getInstance('bad_logins');
        $memLogins->lock(true);
        if ($memLogins->open('c', 64*1024)) {
            $logins = @unserialize($memLogins->read());
            if (!$logins) {
                $logins = array();
            }

            $lockedout_time = (int)$this->app->registry->fetch('password_lockedout_time', 'Policy');
            // loop for find outdated records
            foreach ($logins as $user => $access) {
                if ($access['time'] < time() - $lockedout_time) {
                    unset($logins[$user]);
                }
            }
            // fetch bad logins count
            $result = isset($logins[$username])? (int)$logins[$username]['count'] : 0;
            switch ($operation) {
                case 1:     // increase
                    $result++;
                    $logins[$username] = array(
                        'count' => $result,
                        'time' => time()
                    );
                    break;

                case -1:    // remove
                    $result = 0;
                    unset($logins[$username]);
                    break;
            }

            // write new date
            $memLogins->write(serialize($logins));
            $memLogins->close();
        }

        $memLogins->lock(false);
        return $result;
    }

    /**
     * Notify user login key by email/mobile
     *
     * @access  public
     * @param   array   $uData  User data array
     * @return  bool    True
     */
    function NotifyLoginKey($uData)
    {
        // generate login/verification key
        $loginkey = array(
            'text' => Jaws_Utils::RandomText(5, array('number' => true)),
            'time' => time()
        );

        $site_url = $this->app->getSiteURL('/');
        $settings = $this->app->registry->fetchAll('Settings');

        $params = array();
        $params['key']     = crc32('Users.Login.Key.' . $uData['id']);
        $params['name']    = 'UserVerification';
        $params['title']   = _t('GLOBAL_LOGINKEY_TITLE');
        $params['summary'] = array(
            'format'    => 'GLOBAL_LOGINKEY_SUMMARY',
            'arguments' => array(
                $loginkey['text']
            )
        );

        $params['verbose'] = array(
            'template' => 'UserLogin',
            'variables' => array(
                'nickname'     => $uData['nickname'],
                'message'      => _t('USERS_REGISTRATION_ACTIVATION_REQUIRED_BY_USER'),
                'lbl_username' => _t('USERS_USERS_USERNAME'),
                'username'     => $uData['username'],
                'lbl_key'      => _t('USERS_LOGIN_KEY'),
                'key'          => $loginkey['text'],
                'lbl_ip'       => _t('GLOBAL_IP'),
                'ip'           => $_SERVER['REMOTE_ADDR'],
                'thanks'       => _t('GLOBAL_THANKS'),
                'site-name'    => $settings['site_name'],
                'site-url'     => $site_url,
            )
        );
        $params['user'] = $uData['id'];

        $this->gadget->event->shout('Notify', $params);
        // update session login-key
        $this->gadget->session->loginkey = $loginkey;

        return true;
    }

}