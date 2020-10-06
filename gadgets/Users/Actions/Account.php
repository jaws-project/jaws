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

        $response = $this->gadget->session->pop('Account');
        if (!isset($response['data'])) {
            $jUser = new Jaws_User;
            $account = $jUser->GetUser($this->app->session->user->id, true, true);
        } else {
            $account = $response['data'];
        }

        $assigns = array();
        $assigns = array_merge($assigns, $account);  // same as $assigns = $account
        // Menu navigation
        $assigns['navigation'] = $this->gadget->action->load('MenuNavigation')->xnavigation();

        $assigns['base_script']       = BASE_SCRIPT;
        $assigns['response']          = $response;
        $assigns['username_disabled'] = !$this->gadget->GetPermission('EditUserName');
        $assigns['nickname_disabled'] = !$this->gadget->GetPermission('EditUserNickname');
        $assigns['email_disabled']    = !$this->gadget->GetPermission('EditUserEmail');
        $assigns['mobile_disabled']   = !$this->gadget->GetPermission('EditUserMobile');
        // avatar
        if (empty($account['avatar'])) {
            $user_current_avatar = $this->app->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
        } else {
            $user_current_avatar = $this->app->getDataURL() . "avatar/" . $account['avatar'];
            $user_current_avatar .= !empty($account['last_update']) ? "?" . $account['last_update'] . "" : '';
        }
        $assigns['avatar'] = $user_current_avatar;

        return $this->gadget->template->xLoad('Account.html')->render($assigns);
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
        $post = $this->gadget->request->fetch(
            array('username', 'nickname', 'email', 'mobile'),
            'post'
        );

        // check edit username permission
        if (empty($post['username']) ||
            !$this->gadget->GetPermission('EditUserName'))
        {
            $post['username'] = $this->app->session->user->username;
        }
        // check edit nickname permission
        if (empty($post['nickname']) ||
            !$this->gadget->GetPermission('EditUserNickname'))
        {
            $post['nickname'] = $this->app->session->user->nickname;
        }
        // check edit email permission
        if (empty($post['email']) ||
            !$this->gadget->GetPermission('EditUserEmail'))
        {
            $post['email'] = $this->app->session->user->email;
        }

        // set new email
        $post['new_email'] = '';
        if ($post['email'] != $this->app->session->user->email) {
            $post['new_email'] = $post['email'];
            $post['email'] = $this->app->session->user->email;
        }

        // check edit mobile permission
        if (empty($post['mobile']) ||
            !$this->gadget->GetPermission('EditUserMobile'))
        {
            $post['mobile'] = $this->app->session->user->mobile;
        }

        $model  = $this->gadget->model->load('Account');
        $result = $model->UpdateAccount(
            $this->app->session->user->id,
            $post['username'],
            $post['nickname'],
            $post['email'],
            $post['new_email'],
            $post['mobile']
        );

        if (!Jaws_Error::IsError($result)) {
            $message = $this::t('MYACCOUNT_UPDATED');
            if (!empty($post['new_email'])) {
                $mResult = $this->ReplaceEmailNotification(
                    $this->app->session->user->id,
                    $post['username'],
                    $post['nickname'],
                    $post['new_email'],
                    $post['email'],
                    $post['mobile']
                );
                if (Jaws_Error::IsError($mResult)) {
                    $message = $message. "\n" . $mResult->getMessage();
                } else {
                    $message = $message. "\n" . $this::t('EMAIL_REPLACEMENT_SENT');
                }
            }
            $this->gadget->session->push(
                $message,
                RESPONSE_NOTICE,
                'Account'
            );
        } else {
            $this->gadget->session->push(
                $result->GetMessage(),
                RESPONSE_ERROR,
                'Account',
                $post
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('Account'));
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
        // load js file
        $this->AjaxMe('index.js');

        $response = $this->gadget->session->pop('Password');
        if (!isset($response['data'])) {
            $reqpost = array(
                'pubkey'   => '',
                'usecrypt' => false,
            );
        } else {
            $reqpost = $response['data'];
        }

        $assigns = array();
        $assigns['base_script'] = BASE_SCRIPT;
        $assigns['username']    = $this->app->session->user->username;
        $assigns['response']    = $response;
        // Menu navigation
        $assigns['navigation']  = $this->gadget->action->load('MenuNavigation')->xnavigation();

        // usecrypt
        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $assigns['pubkey'] = $JCrypt->getPublic();
            $assigns['usecrypt_selected'] = empty($reqpost['pubkey']) || !empty($reqpost['usecrypt']);
        }

        return $this->gadget->template->xLoad('Password.html')->render($assigns);
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
        $postedData = $this->gadget->request->fetch(
            array('password', 'old_password', 'usecrypt', 'pubkey'),
            'post'
        );

        $JCrypt = Jaws_Crypt::getInstance();
        if ($postedData['usecrypt'] && !Jaws_Error::IsError($JCrypt)) {
            $new_password = $JCrypt->decrypt($postedData['password']);
            $old_password = $JCrypt->decrypt($postedData['old_password']);
        } else {
            $new_password = Jaws_XSS::defilter($postedData['password']);
            $old_password = Jaws_XSS::defilter($postedData['old_password']);
        }
        unset($postedData['password'], $postedData['old_password']);

        // compare old/new passwords
        if ($new_password === $old_password) {
            $this->gadget->session->push(
                $this::t('USERS_PASSWORDS_OLD_EQUAL'),
                RESPONSE_ERROR,
                'Password',
                $postedData
            );
        } else {
            // trying change password
            $result = $this->app->users->UpdatePassword(
                $this->app->session->user->id,
                $new_password,
                $old_password
            );
            if (!Jaws_Error::IsError($result)) {
                $this->gadget->session->push(
                    $this::t('USERS_PASSWORD_UPDATED'),
                    RESPONSE_NOTICE,
                    'Password',
                    $postedData
                );
            } else {
                $this->gadget->session->push(
                    $result->GetMessage(),
                    RESPONSE_ERROR,
                    'Password',
                    $postedData
                );
            }
        }

        return Jaws_Header::Location($this->gadget->urlMap('Password'));
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

        $jUser = new Jaws_User;
        $verifyKey = $jUser->UpdateEmailVerifyKey($user_id);
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