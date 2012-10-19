<?php
/**
 * Users Gadget (layout actions for client side)
 *
 * @category   Gadget
 * @package    Users
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_LoginBox extends UsersLayoutHTML
{
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

        $use_crypt = ($GLOBALS['app']->Registry->Get('/crypt/enabled') == 'true')? true : false;
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

        // remember
        $tpl->SetBlock('LoginBox/remember');
        $tpl->SetVariable('lbl_remember', _t('GLOBAL_REMEMBER_ME'));
        if (!empty($reqpost['remember'])) {
            $tpl->SetBlock('LoginBox/remember/selected');
            $tpl->ParseBlock('LoginBox/remember/selected');
        }
        $tpl->ParseBlock('LoginBox/remember');

        if ($GLOBALS['app']->Registry->Get('/config/anon_register') == 'true') {
            $link =& Piwi::CreateWidget('Link', _t('USERS_REGISTER'),
                                        $GLOBALS['app']->Map->GetURLFor('Users', 'Registration'));
            $tpl->SetVariable('user-register', $link->Get());
        }

        if ($GLOBALS['app']->Registry->Get('/gadgets/Users/password_recovery') == 'true') {
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
                $admin_script = $GLOBALS['app']->Registry->Get('/config/admin_script');
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
            if ($GLOBALS['app']->Registry->Get('/config/anon_register') == 'true') {
                $tpl->SetBlock('LoginLinks/registeration');
                $tpl->SetVariable('user_registeration', _t('USERS_REGISTER'));
                $tpl->SetVariable('registeration_url',  $GLOBALS['app']->Map->GetURLFor('Users', 'Registration'));
                $tpl->ParseBlock('LoginLinks/registeration');
            }

            // forget user/password
            if ($GLOBALS['app']->Registry->Get('/gadgets/Users/password_recovery') == 'true') {
                $tpl->SetBlock('LoginLinks/forgot');
                $tpl->SetVariable('user_forgot', _t('USERS_FORGOT_LOGIN'));
                $tpl->SetVariable('forgot_url',  $GLOBALS['app']->Map->GetURLFor('Users', 'ForgotLogin'));
                $tpl->ParseBlock('LoginLinks/forgot');
            }

            $tpl->ParseBlock('LoginLinks');
        }

        return $tpl->Get();
    }

}