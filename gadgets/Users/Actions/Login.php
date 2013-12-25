<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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
    function SendRecoverKey()
    {
        if ($this->gadget->registry->fetch('password_recovery') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        $post  = jaws()->request->fetch(array('email'), 'post');
        $error = '';

        $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $htmlPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushResponse(
                $resCheck->GetMessage(),
                'Users.ForgotLogin',
                RESPONSE_ERROR
            );
            Jaws_Header::Location($this->gadget->urlMap('ForgotLogin'));
        }

        $uModel = $this->gadget->model->load('Registration');
        $result = $uModel->SendRecoveryKey($post['email']);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                $result->GetMessage(),
                'Users.ForgotLogin',
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('USERS_FORGOT_MAIL_SENT'),
                'Users.ForgotLogin'
            );
        }

        Jaws_Header::Location($this->gadget->urlMap('ForgotLogin'));
    }

    /**
     * Builds password recovery UI
     *
     * @access  public
     * @return  string  XHTML content
     */
    function ForgotLogin()
    {
        if ($this->gadget->registry->fetch('password_recovery') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        // Load the template
        $tpl = $this->gadget->template->load('ForgotLogin.html');
        $tpl->SetBlock('forgot');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('title', _t('USERS_FORGOT_REMEMBER'));
        $tpl->SetVariable('info', _t('USERS_FORGOT_REMEMBER_INFO'));
        $tpl->SetVariable('email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('remember', _t('GLOBAL_SUBMIT'));

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl, 'forgot');

        if ($response = $GLOBALS['app']->Session->PopResponse('Users.ForgotLogin')) {
            $tpl->SetBlock('forgot/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('forgot/response');
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

        $tpl = $this->gadget->template->load('LoginBox.html');

        $use_crypt = $this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true';
        if ($use_crypt) {
            $JCrypt = new Jaws_Crypt();
            $use_crypt = $JCrypt->Init();
        }

        $tpl->SetBlock('LoginBox');
        $tpl->SetVariable('title', _t('USERS_LOGIN_TITLE'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $response = $GLOBALS['app']->Session->PopResponse('Users.Login.Response');
        if (!isset($response['data'])) {
            $referrer  = jaws()->request->fetch('referrer', 'get');
            $reqpost['username'] = '';
            $reqpost['authtype'] = '';
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
            $reqpost['referrer'] = is_null($referrer)? bin2hex(Jaws_Utils::getRequestURL(true)) : $referrer;
        } else {
            $reqpost = $response['data'];
        }

        if ($use_crypt) {
            $GLOBALS['app']->Layout->AddScriptLink('libraries/js/rsa.lib.js');
            $tpl->SetBlock('LoginBox/onsubmit');
            $tpl->ParseBlock('LoginBox/onsubmit');
            $tpl->SetBlock('LoginBox/encryption');
            $tpl->SetVariable('modulus',  $JCrypt->math->bin2int($JCrypt->pub_key->getModulus()));
            $tpl->SetVariable('exponent', $JCrypt->math->bin2int($JCrypt->pub_key->getExponent()));
            $tpl->ParseBlock('LoginBox/encryption');

            // usecrypt
            $tpl->SetBlock('LoginBox/usecrypt');
            $tpl->SetVariable('lbl_usecrypt', _t('GLOBAL_LOGIN_SECURE'));
            if (empty($reqpost['username']) || !empty($reqpost['usecrypt'])) {
                $tpl->SetBlock('LoginBox/usecrypt/selected');
                $tpl->ParseBlock('LoginBox/usecrypt/selected');
            }
            $tpl->ParseBlock('LoginBox/usecrypt');
        }

        $tpl->SetVariable('login', _t('GLOBAL_LOGIN'));
        $tpl->SetVariable('referrer', $reqpost['referrer']);
        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('username', $reqpost['username']);
        $tpl->SetVariable('lbl_password', _t('GLOBAL_PASSWORD'));

        $authtype = $this->gadget->registry->fetch('authtype');
        if ($authtype !== 'Default') {
            $authtype = empty($reqpost['authtype'])? $authtype : $reqpost['authtype'];
            $tpl->SetBlock('LoginBox/authtype');
            $tpl->SetVariable('lbl_authtype', _t('GLOBAL_AUTHTYPE'));
            foreach ($GLOBALS['app']->GetAuthTypes() as $method) {
                $tpl->SetBlock('LoginBox/authtype/item');
                $tpl->SetVariable('method', $method);
                if ($method == $authtype) {
                    $tpl->SetVariable('selected', 'selected="selected"');
                } else {
                    $tpl->SetVariable('selected', '');
                }
                $tpl->ParseBlock('LoginBox/authtype/item');
            }
            $tpl->ParseBlock('LoginBox/authtype');
        }

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl, 'LoginBox', 'login');

        // remember
        $tpl->SetBlock('LoginBox/remember');
        $tpl->SetVariable('lbl_remember', _t('GLOBAL_REMEMBER_ME'));
        if (!empty($reqpost['remember'])) {
            $tpl->SetBlock('LoginBox/remember/selected');
            $tpl->ParseBlock('LoginBox/remember/selected');
        }
        $tpl->ParseBlock('LoginBox/remember');

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
                $this->gadget->urlMap('ForgotLogin')
            );
            $tpl->SetVariable('forgot-password', $link->Get());
        }

        if (!empty($response)) {
            $tpl->SetBlock('LoginBox/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('LoginBox/response');
        }

        $tpl->ParseBlock('LoginBox');
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
            $uInfo = $GLOBALS['app']->Session->GetAttributes('username', 'nickname', 'avatar', 'email');
            // username
            $tpl->SetVariable('username',  $uInfo['username']);
            // nickname
            $tpl->SetVariable('nickname',  $uInfo['nickname']);
            // profile link
            $tpl->SetVariable(
                'profile_url',
                $this->gadget->urlMap('Profile', array('user' => $uInfo['username']))
            );
            // email
            $tpl->SetVariable('email',  $uInfo['email']);

            // edit account information
            if ($this->gadget->GetPermission(
                    'EditUserName,EditUserNickname,EditUserEmail,EditUserPassword', '', false)
            ) {
                $tpl->SetBlock('UserLinks/account');
                $tpl->SetVariable('user_account', _t('USERS_EDIT_ACCOUNT'));
                $tpl->SetVariable('account_url', $this->gadget->urlMap('Account'));
                $tpl->ParseBlock('UserLinks/account');
            }

            // edit account personal
            if ($this->gadget->GetPermission('EditUserPersonal')) {
                $tpl->SetBlock('UserLinks/personal');
                $tpl->SetVariable('user_personal', _t('USERS_EDIT_PERSONAL'));
                $tpl->SetVariable('personal_url', $this->gadget->urlMap('Personal'));
                $tpl->ParseBlock('UserLinks/personal');
            }

            // edit account preferences
            if ($this->gadget->GetPermission('EditUserPreferences')) {
                $tpl->SetBlock('UserLinks/preferences');
                $tpl->SetVariable('user_preferences', _t('USERS_EDIT_PREFERENCES'));
                $tpl->SetVariable('preferences_url', $this->gadget->urlMap('Preferences'));
                $tpl->ParseBlock('UserLinks/preferences');
            }

            // edit account contacts
            if ($this->gadget->GetPermission('EditUserContacts')) {
                $tpl->SetBlock('UserLinks/contacts');
                $tpl->SetVariable('user_contacts', _t('USERS_EDIT_CONTACTS'));
                $tpl->SetVariable('contacts_url', $this->gadget->urlMap('Contacts'));
                $tpl->ParseBlock('UserLinks/contacts');
            }

            // manage friends
            if ($this->gadget->GetPermission('ManageFriends')) {
                $tpl->SetBlock('UserLinks/groups');
                $tpl->SetVariable('user_groups', _t('USERS_MANAGE_GROUPS'));
                $tpl->SetVariable('groups_url', $this->gadget->urlMap('Groups'));
                $tpl->ParseBlock('UserLinks/groups');
            }

            // fetch current layout user
            $layout_user = $GLOBALS['app']->Session->GetAttribute('layout');
            // Layout/Dashboard manager
            if (empty($layout_user)) {
                // global site layout
                if ($GLOBALS['app']->Session->GetPermission('Layout', 'ManageLayout')) {
                    $tpl->SetBlock('UserLinks/layout');
                    $tpl->SetVariable('layout', _t('LAYOUT_TITLE'));
                    $tpl->SetVariable(
                        'layout_url',
                        $this->gadget->urlMap('Layout', array(), false, 'Layout')
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
                        $this->gadget->urlMap('Layout', array(), false, 'Layout')
                    );
                    $tpl->ParseBlock('UserLinks/layout');
                }
            }

            // Dashboard
            if ($this->gadget->GetPermission('AccessDashboard')) {
                $tpl->SetBlock('UserLinks/dashboard');
                if (empty($layout_user)) {
                    $tpl->SetVariable('dashboard', _t('USERS_DASHBOARD_USER'));
                } else {
                    $tpl->SetVariable('dashboard', _t('USERS_DASHBOARD_GLOBAL'));
                }
                $tpl->SetVariable(
                    'dashboard_url',
                    $this->gadget->urlMap('Dashboard', array(), false, 'Layout')
                );
                $tpl->ParseBlock('UserLinks/dashboard');
            }

            // ControlPanel
            if ($this->gadget->GetPermission('default_admin', '', false, 'ControlPanel')) {
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
            $referrer  = jaws()->request->fetch('referrer', 'get');
            $referrer  = is_null($referrer)? bin2hex(Jaws_Utils::getRequestURL(true)) : $referrer;
            $login_url = $this->gadget->urlMap('LoginBox', array('referrer'  => $referrer));

            $tpl->SetBlock('LoginLinks');
            $tpl->SetVariable('title', _t('USERS_LOGINLINKS'));

            // welcome
            $tpl->SetVariable('welcome', _t('USERS_WELCOME'));

            // login
            $tpl->SetVariable('user_login', _t('USERS_LOGIN_TITLE'));
            $tpl->SetVariable('login_url', $login_url);

            // registeration
            if ($this->gadget->registry->fetch('anon_register') == 'true') {
                $tpl->SetBlock('LoginLinks/registeration');
                $tpl->SetVariable('user_registeration', _t('USERS_REGISTER'));
                $tpl->SetVariable('registeration_url',  $this->gadget->urlMap('Registration'));
                $tpl->ParseBlock('LoginLinks/registeration');
            }

            // forget user/password
            if ($this->gadget->registry->fetch('password_recovery') == 'true') {
                $tpl->SetBlock('LoginLinks/forgot');
                $tpl->SetVariable('user_forgot', _t('USERS_FORGOT_LOGIN'));
                $tpl->SetVariable('forgot_url',  $this->gadget->urlMap('ForgotLogin'));
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
        $post = jaws()->request->fetch(
            array('username', 'password', 'authtype', 'remember', 'usecrypt', 'referrer'),
            'post'
        );

        if ($this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true' && isset($post['usecrypt'])) {
            $JCrypt = new Jaws_Crypt();
            $JCrypt->Init();
            $post['password'] = $JCrypt->decrypt($post['password']);
            if (Jaws_Error::isError($post['password'])) {
                $post['password'] = '';
            }
        }

        // check captcha
        $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $htmlPolicy->checkCaptcha('login');
        if (!Jaws_Error::IsError($resCheck)) {
            // try to login
            $resCheck = $GLOBALS['app']->Session->Login(
                $post['username'],
                $post['password'],
                $post['remember'],
                $post['authtype']
            );
        }
        if (Jaws_Error::isError($resCheck)) {
            unset($post['password']);
            $GLOBALS['app']->Session->PushResponse(
                $resCheck->GetMessage(),
                'Users.Login.Response',
                RESPONSE_ERROR,
                $post
            );
            $login_url = $this->gadget->urlMap('LoginBox', array('referrer'  => $post['referrer']));
            Jaws_Header::Location($login_url);
        }

        Jaws_Header::Location(hex2bin($post['referrer']));
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
        Jaws_Header::Referrer();
    }

}