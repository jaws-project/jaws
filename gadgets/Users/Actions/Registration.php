<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_Registration extends Jaws_Gadget_Action
{
    /**
     * Builds the registration form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Registration()
    {
        if ($GLOBALS['app']->Session->Logged()) {
            return Jaws_Header::Location('');
        }

        if ($this->gadget->registry->fetch('anon_register') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        http_response_code(401);
        // 
        $authtype = $this->gadget->request->fetch('authtype');
        if (empty($authtype)) {
            $authtype = $this->gadget->registry->fetch('authtype');
        }
        $authtype = preg_replace('/[^[:alnum:]_\-]/', '', $authtype);
        $authfile = JAWS_PATH . "gadgets/Users/Account/$authtype/Registration.php";
        if (!file_exists($authfile)) {
            $GLOBALS['log']->Log(
                JAWS_LOG_NOTICE,
                $authtype. ' authentication driver doesn\'t exists, switched to default driver'
            );
            $authtype = 'Default';
        }
        // set authentication type in session
        $this->gadget->session->update('authtype', $authtype);

        // load authentication method driver
        $classname = "Users_Account_{$authtype}_Registration";
        $objAccount = new $classname($this->gadget);
        return $objAccount->Registration();
    }

    /**
     * Registers the user
     *
     * @access  public
     * @return  void
     */
    function Register()
    {
        // fetch authentication type from session
        $authtype = $this->gadget->session->fetch('authtype');
        if (empty($authtype)) {
            return Jaws_HTTPError::Get(401, '', 'Authentication type is not valid!');
        }

        if ($this->gadget->registry->fetch('anon_register') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        $classname = "Users_Account_{$authtype}_Register";
        $objAccount = new $classname($this->gadget);
        $registerData = $objAccount->Register();
        if (Jaws_Error::IsError($registerData)) {
            $default_authtype = $this->gadget->registry->fetch('authtype');
            return $objAccount->RegisterError(
                $registerData,
                ($authtype != $default_authtype)? $authtype : ''
            );
        } else {
            // add required attributes for auto login into jaws
            $registerData['authtype'] = $authtype;

            // create session & cookie
            $GLOBALS['app']->Session->Create($registerData, $registerData['remember']);
            // login event logging
            $GLOBALS['app']->Listener->Shout('Session', 'Log', array('Users', 'Login', JAWS_NOTICE));
            // let everyone know a user has been logged in
            $this->gadget->event->shout('LoginUser', $registerData);

            $this->gadget->session->push(
                _t('USERS_REGISTRATION_ACTIVATED'),
                'Login.Response',
                RESPONSE_NOTICE
            );
        }

        http_response_code(201);
        return Jaws_Header::Location($this->gadget->urlMap('Login'));
    }

    /**
     * Activates the user
     *
     * @access  public
     * @return  string  Appropriate notice or error message
     */
    function ReplaceUserEmail()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(401);
        }

        $this->gadget->CheckPermission('EditUserEmail');
        $key = $this->gadget->request->fetch('key', 'get');

        $jUser = new Jaws_User;
        $user = $jUser->GetUserByEmailVerifyKey($key);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return _t('USERS_ACTIVATION_KEY_NOT_VALID');
        }

        $result = $jUser->UpdateUser(
            $user['id'],
            array(
                'username'  => $user['username'],
                'nickname'  => $user['nickname'],
                'email'     => $user['new_email'],
                'new_email' => '',
                'status'    => 1
            )
        );
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return _t('USERS_EMAIL_REPLACEMENT_REPLACED');
    }

    /**
     * Mails activate notification to the user
     *
     * @access  public
     * @param   array   $user               User's attributes array
     * @param   string  $anon_activation    Anonymous activation type
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function ActivateNotification($user, $anon_activation)
    {
        $site_url  = $GLOBALS['app']->getSiteURL('/');
        $site_name = $this->gadget->registry->fetch('site_name', 'Settings');

        $tpl = $this->gadget->template->load('UserNotification.txt');
        $tpl->SetBlock('Notification');
        $tpl->SetVariable('say_hello', _t('USERS_REGISTRATION_HELLO', $user['nickname']));
        $tpl->SetVariable('message', _t('USERS_ACTIVATE_ACTIVATED_MAIL_MSG'));
        if ($anon_activation == 'user') {
            $tpl->SetBlock('Notification/IP');
            $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
            $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);
            $tpl->ParseBlock('Notification/IP');
        }

        $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username', $user['username']);

        $tpl->SetVariable('thanks', _t('GLOBAL_THANKS'));
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url', $site_url);
        $tpl->ParseBlock('Notification');

        $body = $tpl->Get();
        $subject = _t('USERS_REGISTRATION_SUBJECT', $site_name);

        $mail = Jaws_Mail::getInstance();
        $mail->SetFrom();
        $mail->AddRecipient($user['email']);
        $mail->SetSubject($subject);
        $mail->SetBody($this->gadget->plugin->parseAdmin($body));
        return $mail->send();
    }

    /**
     * Notify user registration key
     * @access  public
     * @param   array   $uData  User data array
     * @return  bool    True
     */
    function NotifyRegistrationKey($uData)
    {
        // generate registration key
        $regkey = array(
            'text' => Jaws_Utils::RandomText(5, true, false, true),
            'time' => time()
        );

        $site_url = $GLOBALS['app']->getSiteURL('/');
        $settings = $GLOBALS['app']->Registry->fetchAll('Settings');

        $tpl = $this->gadget->template->load('RegistrationNotification.html');
        $tpl->SetBlock('UserNotification');
        $tpl->SetVariable('say_hello', _t('USERS_REGISTRATION_HELLO', $uData['nickname']));
        $tpl->SetVariable('message', _t('USERS_REGISTRATION_ACTIVATION_REQUIRED_BY_USER'));
        // verify key
        $tpl->SetBlock('UserNotification/Activation');
        $tpl->SetVariable('lbl_key', _t('USERS_REGISTRATION_KEY'));
        $tpl->SetVariable('key', $regkey['text']);
        $tpl->ParseBlock('UserNotification/Activation');

        $tpl->SetVariable('lbl_username',   _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username',       $uData['username']);
        $tpl->SetVariable('lbl_password',   _t('USERS_USERS_PASSWORD'));
        $tpl->SetVariable('password',       $uData['password']);
        $tpl->SetVariable('lbl_email',      _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email',          $uData['email']);
        $tpl->SetVariable('lbl_mobile',     _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('mobile',         $uData['mobile']);
        $tpl->SetVariable('lbl_ip',         _t('GLOBAL_IP'));
        $tpl->SetVariable('ip',             $_SERVER['REMOTE_ADDR']);
        $tpl->SetVariable('thanks',         _t('GLOBAL_THANKS'));
        $tpl->SetVariable('site-name',      $settings['site_name']);
        $tpl->SetVariable('site-url',       $site_url);
        $tpl->ParseBlock('UserNotification');
        $message = $tpl->Get();
        $subject = _t('USERS_REGISTRATION_USER_SUBJECT', $settings['site_name']);

        // Notify
        $params = array();
        $params['key']     = crc32('Users.Registration.Key' . $uData['id']);
        $params['title']   = $subject;
        $params['summary'] = _t(
            'USERS_REGISTRATION_USER_SUMMARY',
            $uData['nickname'],
            $site_url,
            $uData['username'],
            $uData['password'],
            $uData['email'],
            $uData['mobile'],
            $regkey['text']
        );

        $params['description'] = $this->gadget->plugin->parse($message);
        $params['emails']      = array($uData['email']);
        $params['mobiles']     = array($uData['mobile']);
        $this->gadget->event->shout('Notify', $params);

        // update session login-key
        $this->gadget->session->update('regkey', $regkey);

        return true;
    }

}