<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_Account extends Users_Actions_Default
{
    /**
     * Builds a simple form to update user account info(nickname, email)
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Account()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission(
            'EditUserName,EditUserNickname,EditUserEmail',
            '',
            false
        );

        $authtype = $this->app->session->auth;
        $classfile = ROOT_JAWS_PATH . "gadgets/Users/Account/$authtype/Account.php";
        if (!file_exists($classfile)) {
            Jaws_Error::Fatal($authtype. ' account class doesn\'t exists');
        }

        // load logout method of account driver
        $classname = "Users_Account_{$authtype}_Account";
        $objAccount = new $classname($this->gadget);
        return $objAccount->Account();
    }

    /**
     * Updates user account information
     *
     * @access  public
     * @return  void
     */
    function UpdateAccount()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission(
            'EditUserName,EditUserNickname,EditUserEmail,EditUserMobile',
            '',
            false
        );

        $authtype = $this->app->session->auth;
        $classfile = ROOT_JAWS_PATH . "gadgets/Users/Account/$authtype/Account.php";
        if (!file_exists($classfile)) {
            Jaws_Error::Fatal($authtype. ' account class doesn\'t exists');
        }

        // load logout method of account driver
        $classname = "Users_Account_{$authtype}_Account";
        $objAccount = new $classname($this->gadget);
        $result = $objAccount->UpdateAccount();
        if (!Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $this::t('MYACCOUNT_UPDATED'),
                RESPONSE_NOTICE,
                'Account.Response'
            );
        }

        return Jaws_Header::Location(
            $this->gadget->urlMap('Account'),
            'Account.Response'
        );
    }

    /**
     * Builds a simple form to update user password
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Password()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        // Check Permission
        $this->gadget->CheckPermission('EditUserPassword');
        $authtype = $this->app->session->auth;
        $classfile = ROOT_JAWS_PATH . "gadgets/Users/Account/$authtype/Account.php";
        if (!file_exists($classfile)) {
            Jaws_Error::Fatal($authtype. ' account class doesn\'t exists');
        }

        // load logout method of account driver
        $classname = "Users_Account_{$authtype}_Account";
        $objAccount = new $classname($this->gadget);
        return $objAccount->Password();
    }

    /**
     * Updates user account information
     *
     * @access  public
     * @return  void
     */
    function UpdatePassword()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserPassword');
        $authtype = $this->app->session->auth;
        $classfile = ROOT_JAWS_PATH . "gadgets/Users/Account/$authtype/Account.php";
        if (!file_exists($classfile)) {
            Jaws_Error::Fatal($authtype. ' account class doesn\'t exists');
        }

        // load logout method of account driver
        $classname = "Users_Account_{$authtype}_Account";
        $objAccount = new $classname($this->gadget);
        $result = $objAccount->UpdatePassword();
        if (!Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $this::t('USERS_PASSWORD_UPDATED'),
                RESPONSE_NOTICE,
                'Password.Response'
            );
        }

        return Jaws_Header::Location(
            $this->gadget->urlMap('Password'),
            'Password.Response'
        );
    }

    /**
     * Sends replace email notification to user
     *
     * @access  public
     * @param   int     $user_id    User's ID
     * @param   string  $nickname   User's nickname
     * @param   string  $new_email  User's new email
     * @param   string  $old_email  User's old email
     * @param   string  $mobile     User's mobile number
     * @return  mixed   True on success otherwise Jaws_Error on failure
     */
    function ReplaceEmailNotification($user_id, $username, $nickname, $new_email, $old_email, $mobile)
    {
        $tpl = $this->gadget->template->load('NewEmail.txt');
        $tpl->SetBlock('Notification');
        $tpl->SetVariable('nickname', $nickname);
        $tpl->SetVariable('say_hello', $this::t('EMAIL_REPLACEMENT_HELLO', $nickname));
        $tpl->SetVariable('message', $this::t('EMAIL_REPLACEMENT_MSG'));

        $tpl->SetBlock('Notification/IP');
        $tpl->SetVariable('lbl_ip', Jaws::t('IP'));
        $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);
        $tpl->ParseBlock('Notification/IP');

        $tpl->SetVariable('lbl_username', $this::t('USERS_USERNAME'));
        $tpl->SetVariable('username', $username);

        $tpl->SetVariable('lbl_email', Jaws::t('EMAIL'));
        $tpl->SetVariable('email', $old_email);

        $verifyKey = $this->app->users->UpdateEmailVerifyKey($user_id);
        if (Jaws_Error::IsError($verifyKey)) {
            return $verifyKey;
        } else {
            $tpl->SetBlock('Notification/Activation');
            $tpl->SetVariable('lbl_activation_link', $this::t('ACTIVATE_ACTIVATION_LINK'));
            $tpl->SetVariable(
                'activation_link',
                $this->gadget->urlMap(
                    'ReplaceUserEmail',
                    array('key' => $verifyKey),
                    array('absolute' => true)
                )
            );
            $tpl->ParseBlock('Notification/Activation');
        }

        $site_url  = $this->app->getSiteURL('/');
        $site_name = $this->gadget->registry->fetch('site_name', 'Settings');
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url',  $site_url);
        $tpl->SetVariable('thanks',    Jaws::t('THANKS'));

        $tpl->ParseBlock('Notification');
        $body = $tpl->Get();

        $subject = $this::t('EMAIL_REPLACEMENT_SUBJECT', $site_name);
        $mail = Jaws_Mail::getInstance();
        $mail->SetFrom();
        $mail->AddRecipient($new_email);
        $mail->SetSubject($subject);
        $mail->SetBody($this->gadget->plugin->parseAdmin($body));
        $mresult = $mail->send();
        if (Jaws_Error::IsError($mresult)) {
            return $mresult;
        }

        return true;
    }

}