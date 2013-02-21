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
class Users_Actions_Login extends Users_HTML
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
        if ($this->gadget->GetRegistry('password_recovery') !== 'true') {
            return parent::_404();
        }

        $request =& Jaws_Request::getInstance();
        $post  = $request->get(array('email'), 'post');
        $error = '';

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $resCheck = $mPolicy->CheckCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'Users.ForgotLogin');
            Jaws_Header::Location($this->gadget->GetURLFor('ForgotLogin'));
        }

        $model  = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Registration');
        $result = $model->SendRecoveryKey($post['email']);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->GetMessage(), 'Users.ForgotLogin');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('USERS_FORGOT_MAIL_SENT'), 'Users.ForgotLogin');
        }
        Jaws_Header::Location($this->gadget->GetURLFor('ForgotLogin'));
    }

    /**
     * Builds password recovery UI
     *
     * @access  public
     * @return  string  XHTML content
     */
    function ForgotLogin()
    {
        if ($this->gadget->GetRegistry('password_recovery') !== 'true') {
            return parent::_404();
        }

        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('ForgotLogin.html');
        $tpl->SetBlock('forgot');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('title', _t('USERS_FORGOT_REMEMBER'));
        $tpl->SetVariable('info', _t('USERS_FORGOT_REMEMBER_INFO'));
        $tpl->SetVariable('email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('remember', _t('GLOBAL_SUBMIT'));

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        if ($mPolicy->LoadCaptcha($captcha, $entry, $label, $description)) {
            $tpl->SetBlock('forgot/captcha');
            $tpl->SetVariable('lbl_captcha', $label);
            $tpl->SetVariable('captcha', $captcha);
            if (!empty($entry)) {
                $tpl->SetVariable('captchavalue', $entry);
            }
            $tpl->SetVariable('captcha_msg', $description);
            $tpl->ParseBlock('forgot/captcha');
        }

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Users.ForgotLogin')) {
            $tpl->SetBlock('forgot/response');
            $tpl->SetVariable('msg', $response);
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

        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('LoginBox.html');

        $use_crypt = $this->gadget->GetRegistry('crypt_enabled', 'Policy') == 'true';
        if ($use_crypt) {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
            $JCrypt = new Jaws_Crypt();
            $use_crypt = $JCrypt->Init();
        }

        $tpl->SetBlock('LoginBox');
        $tpl->SetVariable('title', _t('USERS_LOGIN_TITLE'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $reqpost = $GLOBALS['app']->Session->PopSimpleResponse('Users.Login.Data');
        if (empty($reqpost)) {
            $request =& Jaws_Request::getInstance();
            $referrer  = $request->get('referrer', 'get');
            $reqpost['username'] = '';
            $reqpost['remember'] = '';
            $reqpost['usecrypt'] = '';
            $reqpost['referrer'] = is_null($referrer)? bin2hex(Jaws_Utils::getRequestURL(true)) : $referrer;
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

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        if ($mPolicy->LoadCaptcha($captcha, $entry, $label, $description, 'login', 'login')) {
            $tpl->SetBlock('LoginBox/captcha');
            $tpl->SetVariable('lbl_captcha', $label);
            $tpl->SetVariable('captcha', $captcha);
            if (!empty($entry)) {
                $tpl->SetVariable('captchavalue', $entry);
            }
            $tpl->SetVariable('captcha_msg', $description);
            $tpl->ParseBlock('LoginBox/captcha');
        }

        // remember
        $tpl->SetBlock('LoginBox/remember');
        $tpl->SetVariable('lbl_remember', _t('GLOBAL_REMEMBER_ME'));
        if (!empty($reqpost['remember'])) {
            $tpl->SetBlock('LoginBox/remember/selected');
            $tpl->ParseBlock('LoginBox/remember/selected');
        }
        $tpl->ParseBlock('LoginBox/remember');

        if ($this->gadget->GetRegistry('anon_register') == 'true') {
            $link =& Piwi::CreateWidget('Link', _t('USERS_REGISTER'),
                                        $GLOBALS['app']->Map->GetURLFor('Users', 'Registration'));
            $tpl->SetVariable('user-register', $link->Get());
        }

        if ($this->gadget->GetRegistry('password_recovery') == 'true') {
            $link =& Piwi::CreateWidget('Link', _t('USERS_FORGOT_LOGIN'),
                                        $GLOBALS['app']->Map->GetURLFor('Users', 'ForgotLogin'));
            $tpl->SetVariable('forgot-password', $link->Get());
        }

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Users.Login')) {
            $tpl->SetBlock('LoginBox/response');
            $tpl->SetVariable('msg', $response);
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
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('LoginLinks.html');

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
            $tpl->SetVariable('profile_url',
                              $GLOBALS['app']->Map->GetURLFor('Users',
                                                              'Profile',
                                                              array('user' => $uInfo['username'])));
            // email
            $tpl->SetVariable('email',  $uInfo['email']);

            // edit account information
            if ($GLOBALS['app']->Session->GetPermission(
                    'Users',
                    'EditUserName,EditUserNickname,EditUserEmail,EditUserPassword', false))
            {
                $tpl->SetBlock('UserLinks/account');
                $tpl->SetVariable('user_account', _t('USERS_EDIT_ACCOUNT'));
                $tpl->SetVariable('account_url', $GLOBALS['app']->Map->GetURLFor('Users', 'Account'));
                $tpl->ParseBlock('UserLinks/account');
            }

            // edit account personal
            if ($GLOBALS['app']->Session->GetPermission('Users', 'EditUserPersonal')) {
                $tpl->SetBlock('UserLinks/personal');
                $tpl->SetVariable('user_personal', _t('USERS_EDIT_PERSONAL'));
                $tpl->SetVariable('personal_url', $GLOBALS['app']->Map->GetURLFor('Users', 'Personal'));
                $tpl->ParseBlock('UserLinks/personal');
            }

            // edit account preferences
            if ($GLOBALS['app']->Session->GetPermission('Users', 'EditUserPreferences')) {
                $tpl->SetBlock('UserLinks/preferences');
                $tpl->SetVariable('user_preferences', _t('USERS_EDIT_PREFERENCES'));
                $tpl->SetVariable('preferences_url', $GLOBALS['app']->Map->GetURLFor('Users', 'Preferences'));
                $tpl->ParseBlock('UserLinks/preferences');
            }

            // ControlPanel
            if ($GLOBALS['app']->Session->GetPermission('ControlPanel', 'default_admin')) {
                $tpl->SetBlock('UserLinks/cpanel');
                $tpl->SetVariable('cpanel', _t('USERS_CONTROLPANEL'));
                $admin_script = $this->gadget->GetRegistry('admin_script', 'Settings');
                $tpl->SetVariable('cpanel_url', empty($admin_script)? 'admin.php' : $admin_script);
                $tpl->ParseBlock('UserLinks/cpanel');
            }

            $tpl->SetVariable('logout', _t('GLOBAL_LOGOUT'));
            $tpl->SetVariable('logout_url', $GLOBALS['app']->Map->GetURLFor('Users', 'Logout'));

            $tpl->ParseBlock('UserLinks');
        } else {
            $request   =& Jaws_Request::getInstance();
            $referrer  = $request->get('referrer', 'get');
            $referrer  = is_null($referrer)? bin2hex(Jaws_Utils::getRequestURL(true)) : $referrer;
            $login_url = $GLOBALS['app']->Map->GetURLFor(
                'Users',
                'LoginBox',
                array('referrer'  => $referrer)
            );

            $tpl->SetBlock('LoginLinks');
            $tpl->SetVariable('title', _t('USERS_LOGINLINKS'));

            // welcome
            $tpl->SetVariable('welcome', _t('USERS_WELCOME'));

            // login
            $tpl->SetVariable('user_login', _t('USERS_LOGIN_TITLE'));
            $tpl->SetVariable('login_url', $login_url);

            // registeration
            if ($this->gadget->GetRegistry('anon_register') == 'true') {
                $tpl->SetBlock('LoginLinks/registeration');
                $tpl->SetVariable('user_registeration', _t('USERS_REGISTER'));
                $tpl->SetVariable('registeration_url',  $GLOBALS['app']->Map->GetURLFor('Users', 'Registration'));
                $tpl->ParseBlock('LoginLinks/registeration');
            }

            // forget user/password
            if ($this->gadget->GetRegistry('password_recovery') == 'true') {
                $tpl->SetBlock('LoginLinks/forgot');
                $tpl->SetVariable('user_forgot', _t('USERS_FORGOT_LOGIN'));
                $tpl->SetVariable('forgot_url',  $GLOBALS['app']->Map->GetURLFor('Users', 'ForgotLogin'));
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
        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('username', 'password', 'remember', 'usecrypt', 'referrer'), 'post');

        if ($this->gadget->GetRegistry('crypt_enabled', 'Policy') == 'true' && isset($post['usecrypt'])) {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
            $JCrypt = new Jaws_Crypt();
            $JCrypt->Init();
            $post['password'] = $JCrypt->decrypt($post['password']);
            if (Jaws_Error::isError($post['password'])) {
                $post['password'] = '';
            }
        }

        // check captcha
        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $resCheck = $mPolicy->CheckCaptcha('login');
        if (!Jaws_Error::IsError($resCheck)) {
            // try to login
            $resCheck = $GLOBALS['app']->Session->Login($post['username'], $post['password'], $post['remember']);
        }
        if (Jaws_Error::isError($resCheck)) {
            $GLOBALS['app']->Session->PushSimpleResponse($resCheck->GetMessage(), 'Users.Login');
            unset($post['password']);
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Users.Login.Data');
            $login_url = $GLOBALS['app']->Map->GetURLFor(
                'Users',
                'LoginBox',
                array('referrer'  => $post['referrer'])
            );

            Jaws_Header::Location($login_url, true);
        }

        Jaws_Header::Location(hex2bin($post['referrer']), true);
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