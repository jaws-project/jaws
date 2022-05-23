<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Default_Account extends Users_Account_Default
{
    /**
     * Builds a simple form to update user account info(nickname, email)
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Account()
    {
        $this->AjaxMe('index.js');

        $assigns = array();
        $response = $this->gadget->session->pop('Account.Response');
        if (!isset($response['data'])) {
            $assigns = $this->gadget->model->load('User')->get(
                $this->app->session->user->id,
                $this->app->session->user->domain
            );
            $assigns['step'] = 2;
        } else {
            $assigns = $response['data'];
            $assigns['step'] = (int)$assigns['step'];
        }
        $assigns['response'] = $response;

        switch ($assigns['step']) {
            case 2:
                $this->AccountStep2($assigns);
                break;

            case 3:
                $this->AccountStep3($assigns);
                break;

            default:
                $this->AccountStep1($assigns);
        }

        return $this->gadget->template->xLoad('Account.html')->render($assigns);
    }

    /**
     * Builds a simple form to update user account info(nickname, email)
     *
     * @access  public
     * @return  string  XHTML form
     */
    function AccountStep1(&$assigns)
    {
    }

    /**
     * Builds a simple form to update user account info(nickname, email)
     *
     * @access  public
     * @return  void
     */
    function AccountStep2(&$assigns)
    {
        // Menu navigation
        $assigns['navigation'] = $this->gadget->action->load('MenuNavigation')->xnavigation();
        $assigns['base_script']       = BASE_SCRIPT;
        $assigns['username_disabled'] = !$this->gadget->GetPermission('EditUserName');
        $assigns['nickname_disabled'] = !$this->gadget->GetPermission('EditUserNickname');
        $assigns['email_disabled']    = !$this->gadget->GetPermission('EditUserEmail');
        $assigns['mobile_disabled']   = !$this->gadget->GetPermission('EditUserMobile');
        $assigns['avatar']            = $this->gadget->urlMap('Avatar', array('user'  => $assigns['username']));
    }

    /**
     * Builds a simple form to update user account info(nickname, email)
     *
     * @access  public
     * @return  string  XHTML form
     */
    function AccountStep3(&$assigns)
    {
    }

    /**
     * Updates user account information
     *
     * @access  public
     * @return  void
     */
    function UpdateAccount()
    {
        $postData = $this->gadget->request->fetch(
            array(
                'domain', 'username', 'nickname', 'email', 'mobile', 'step'
            ),
            'post'
        );
        $postData['step'] = (int)$postData['step'];

        // set default domain if not set
        if (is_null($postData['domain'])) {
            $postData['domain'] = (int)$this->gadget->registry->fetch('default_domain');
        }

        try {
            if ($postData['step'] == 2) {
                // check edit username permission
                if (empty($postData['username']) ||
                    !$this->gadget->GetPermission('EditUserName'))
                {
                    $postData['username'] = $this->app->session->user->username;
                }
                // check edit nickname permission
                if (empty($postData['nickname']) ||
                    !$this->gadget->GetPermission('EditUserNickname'))
                {
                    $postData['nickname'] = $this->app->session->user->nickname;
                }
                // check edit email permission
                if (empty($postData['email']) ||
                    !$this->gadget->GetPermission('EditUserEmail'))
                {
                    $postData['email'] = $this->app->session->user->email;
                }
                // check edit mobile permission
                if (empty($postData['mobile']) ||
                    !$this->gadget->GetPermission('EditUserMobile'))
                {
                    $postData['mobile'] = $this->app->session->user->mobile;
                }

                $result = $this->gadget->model->load('User')->updateAccount(
                    $this->app->session->user->id,
                    $postData
                );
                if (Jaws_Error::IsError($result)) {
                    throw new Exception($result->getMessage(), 404);
                }

                throw new Exception($this::t('MYACCOUNT_UPDATED'), 201);

//            } elseif () {
            }
        } catch (Exception $error) {
            unset($postData['password'], $postData['password_check']);
            $this->gadget->session->push(
                $error->getMessage(),
                ($error->getCode() == 201)? RESPONSE_NOTICE : RESPONSE_ERROR,
                'Account.Response',
                $postData
            );

            return Jaws_Error::raiseError($error->getMessage(), $error->getCode());
        }
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
            $result = $this->gadget->model->load('User')->updatePassword(
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