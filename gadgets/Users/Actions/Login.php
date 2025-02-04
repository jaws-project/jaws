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
     * Builds the login/user links
     *
     * @access  public
     * @return  string  XHTML content
     */
    function links()
    {
        $assigns = array();
        return $this->gadget->template->xLoad('Links.html')->render($assigns);
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

        if ($this->app->session->user->logged) {
            $tpl->SetBlock('UserLinks');
            $tpl->SetVariable('title', Jaws::t('MY_ACCOUNT'));

            $response = $this->gadget->session->pop('Login.Response');
            if (!empty($response)) {
                $tpl->SetVariable('response_type', $response['type']);
                $tpl->SetVariable('response_text', $response['text']);
            }

            $tpl->SetVariable('profile', $this::t('PROFILE'));
            // username
            $tpl->SetVariable('username', $this->app->session->user->username);
            // nickname
            $tpl->SetVariable('nickname', $this->app->session->user->nickname);
            // avatar
            $tpl->SetVariable(
                'avatar',
                $this->gadget->urlMap('Avatar', array('user'  => $this->app->session->user->username))
            );
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
                $tpl->SetVariable('user_groups', $this::t('FRIENDS'));
                $tpl->SetVariable('groups_url', $this->gadget->urlMap('FriendsGroups'));
                $tpl->ParseBlock('UserLinks/groups');
            }

            $layoutGadget = Jaws_Gadget::getInstance('Layout');
            if ($layoutGadget->GetPermission('MainLayoutManage')) {
                // link to manage global layout
                $tpl->SetBlock('UserLinks/manage-layout');
                $tpl->SetVariable('layout', Jaws_Gadget::t('LAYOUT.LAYOUT'));
                $tpl->SetVariable(
                    'layout_url',
                    $layoutGadget->urlMap('Layout')
                );
                $tpl->ParseBlock('UserLinks/manage-layout');
            } elseif ($this->gadget->GetPermission('ManageUserLayout')) {
                // link to manage personal layout
                $tpl->SetBlock('UserLinks/manage-layout');
                $tpl->SetVariable('layout', Jaws_Gadget::t('LAYOUT.LAYOUT'));
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
                $tpl->SetVariable('layout', $this::t("DASHBOARD_$layout"));
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
                $tpl->SetVariable('cpanel', $this::t('CONTROLPANEL'));
                $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
                $tpl->SetVariable('cpanel_url', empty($admin_script)? 'admin.php' : $admin_script);
                $tpl->ParseBlock('UserLinks/cpanel');
            }

            // Logout
            $tpl->SetVariable('logout', Jaws::t('LOGOUT'));
            $tpl->SetVariable('logout_url', $this->gadget->urlMap('Logout'));

            $tpl->ParseBlock('UserLinks');
        } else {
            $urlParams = array();
            $referrer  = $this->gadget->request->fetch('referrer', 'get');
            $referrer  = is_null($referrer)? bin2hex(Jaws_Utils::getRequestURL(true)) : $referrer;
            if (!empty($referrer)) {
                $urlParams['referrer'] = $referrer;
            }
            $login_url = $this->gadget->urlMap('Login', $urlParams);

            $tpl->SetBlock('LoginLinks');
            $tpl->SetVariable('title', $this::t('LOGINLINKS'));

            // login
            $tpl->SetVariable('user_login', $this::t('LOGIN_TITLE'));
            $tpl->SetVariable('login_url', $login_url);

            // registration
            if ($this->gadget->registry->fetch('anon_register') == 'true') {
                $tpl->SetBlock('LoginLinks/registration');
                $tpl->SetVariable('user_registeration', $this::t('REGISTER'));
                $tpl->SetVariable('registeration_url',  $this->gadget->urlMap('Registration'));
                $tpl->ParseBlock('LoginLinks/registration');
            }

            // forget user/password
            if ($this->gadget->registry->fetch('password_recovery') == 'true') {
                $tpl->SetBlock('LoginLinks/forgot');
                $tpl->SetVariable('user_forgot', $this::t('FORGOT_LOGIN'));
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
            return Jaws_Header::Location(
                $this->gadget->urlMap('Profile', array('user' => $this->app->session->user->username))
            );
        }

        // get/check given authentication type
        $authtype = $this->gadget->request->fetch('authtype');
        if (empty($authtype)) {
            $authtype = $this->gadget->registry->fetch('authtype');
        }
        $authtype = preg_replace('/[^[:alnum:]_\-]/', '', $authtype);
        $drivers = array_map('basename', glob(ROOT_JAWS_PATH . 'gadgets/Users/Account/*', GLOB_ONLYDIR));
        if (false === $dIndex = array_search(strtolower($authtype), array_map('strtolower', $drivers))) {
            $GLOBALS['log']->Log(
                JAWS_NOTICE,
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

        $defaults = (string)$this->gadget->request->fetch('defaults');
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

            // overwrite referrer to default login transfer gadget
            if (JAWS_SCRIPT == 'index') {
                $defaultActionAttribute = 'default_action';
                $default_transfer_gadget = $this->gadget->registry->fetch('login_transfer_gadget_index');
            } else {
                $defaultActionAttribute = 'default_admin_action';
                $default_transfer_gadget = $this->gadget->registry->fetch('login_transfer_gadget_admin');
            }

            if (!empty($default_transfer_gadget)) {
                $defaultAction = Jaws_Gadget::getInstance($default_transfer_gadget)->$defaultActionAttribute;
                if (!Jaws_Error::IsError($defaultAction) && !empty($defaultAction)) {
                    $referrer = bin2hex(
                        Jaws_Gadget::getInstance($default_transfer_gadget)->urlMap($defaultAction)
                    );
                }
            }
        } else {
            $referrer = bin2hex(Jaws_XSS::filterURL(hex2bin($referrer), true, true));
        }

        // load authentication method driver
        $classname = "Users_Account_{$authtype}_Login";
        $objAccount = new $classname($this->gadget);
        return $objAccount->Login(hex2bin($defaults), $referrer);
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
        // fetch default authentication type from registry
        $default_authtype = $this->gadget->registry->fetch('authtype');
        // fetch authentication type from session
        $session_authtype = $this->gadget->session->auth;
        if (empty($session_authtype)) {
            // session authtype not found
            $classname = "Users_Account_{$default_authtype}_Authenticate";
            $objAccount = new $classname($this->gadget);
            $loginData = Jaws_Error::raiseError('Authentication type is not valid!', 401, JAWS_ERROR_WARNING);
        } else {
            $classname = "Users_Account_{$session_authtype}_Authenticate";
            $objAccount = new $classname($this->gadget);
            $loginData = $objAccount->Authenticate();
        }
        
        $referrer = '';
        if (!Jaws_Error::IsError($loginData)) {
            $loginData['auth'] = $session_authtype;
            // parse referrer url
            $referrer = Jaws_XSS::filterURL(hex2bin((string)@$loginData['referrer']), true, true);
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
                    'result'   => 200,
                    'status'   => true,
                )
            );
            // let everyone know a user has been logged in
            $this->gadget->event->shout('LoginUser', $loginData);
        }

        return $objAccount->AuthenticateError(
            $loginData,
            ($session_authtype != $default_authtype)? $session_authtype : '',
            $referrer
        );
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
        $params['name']    = 'UserVerification';
        $params['key']     = $uData['id'];
        $params['title']   = Jaws::t('LOGINKEY_TITLE');
        $params['summary'] = Jaws::t('LOGINKEY_SUMMARY', $loginkey['text']);
        $params['verbose'] = Jaws::t('LOGINKEY_SUMMARY', $loginkey['text']);
        $params['variables'] = array(
            'nickname'     => $uData['nickname'],
            'message'      => $this::t('REGISTRATION_ACTIVATION_REQUIRED_BY_USER'),
            'lbl_username' => $this::t('USERS_USERNAME'),
            'username'     => $uData['username'],
            'lbl_key'      => $this::t('LOGIN_KEY'),
            'key'          => $loginkey['text'],
            'lbl_ip'       => Jaws::t('IP'),
            'ip'           => $_SERVER['REMOTE_ADDR'],
            'thanks'       => Jaws::t('THANKS'),
            'site-name'    => $settings['site_name'],
            'site-url'     => $site_url,
        );
        $params['user'] = $uData['id'];

        $this->gadget->event->shout('Notify', $params);
        // update session login-key
        $this->gadget->session->loginkey = $loginkey;

        return true;
    }

}