<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Login extends UsersHTML
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
        if ($GLOBALS['app']->Registry->Get('/gadgets/Users/password_recovery') !== 'true') {
            return parent::_404();
        }

        $request =& Jaws_Request::getInstance();
        $post  = $request->get(array('email'), 'post');
        $error = '';

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $resCheck = $mPolicy->CheckCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'Users.ForgotLogin');
            Jaws_Header::Location($this->GetURLFor('ForgotLogin'));
        }

        $model  = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Registration');
        $result = $model->SendRecoveryKey($post['email']);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->GetMessage(), 'Users.ForgotLogin');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('USERS_FORGOT_MAIL_SENT'), 'Users.ForgotLogin');
        }
        Jaws_Header::Location($this->GetURLFor('ForgotLogin'));
    }

    /**
     * Builds password recovery UI
     *
     * @access  public
     * @return  string  XHTML content
     */
    function ForgotLogin()
    {
        if ($GLOBALS['app']->Registry->Get('/gadgets/Users/password_recovery') !== 'true') {
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
     * Calls Login box
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginBox()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Users', 'LayoutHTML', 'LoginBox');
        if ($GLOBALS['app']->Session->Logged()) {
            return $layoutGadget->LoginLinks();
        } else {
            return $layoutGadget->LoginBox();
        }
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

        if ($GLOBALS['app']->Registry->Get('/crypt/enabled') == 'true' && isset($post['usecrypt'])) {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
            $JCrypt = new Jaws_Crypt();
            $JCrypt->Init();
            $post['password'] = $JCrypt->decrypt($post['password']);
            if (Jaws_Error::isError($post['password'])) {
                $post['password'] = '';
            }
        }

        $login = $GLOBALS['app']->Session->Login($post['username'], $post['password'], $post['remember']);
        if (Jaws_Error::isError($login)) {
            $GLOBALS['app']->Session->PushSimpleResponse($login->GetMessage(), 'Users.Login');
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