<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Actions_Recovery extends Jaws_Gadget_Action
{
    /**
     * Builds password recovery UI
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginForgot()
    {
        if ($this->app->session->logged()) {
            return Jaws_Header::Location('');
        }

        if ($this->gadget->registry->fetch('password_recovery') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        http_response_code(401);
        // get/check given registration driver type
        $authtype = $this->gadget->request->fetch('authtype');
        if (empty($authtype)) {
            $authtype = $this->gadget->registry->fetch('authtype');
        }
        $authtype = preg_replace('/[^[:alnum:]_\-]/', '', $authtype);
        $drivers = array_map('basename', glob(JAWS_PATH . 'gadgets/Users/Account/*', GLOB_ONLYDIR));
        if (false === $dIndex = array_search(strtolower($authtype), array_map('strtolower', $drivers))) {
            $GLOBALS['log']->Log(
                JAWS_LOG_NOTICE,
                $authtype. ' login recovery driver doesn\'t exists, switched to default driver'
            );
            $authtype = 'Default';
        } else {
            $authtype = $drivers[$dIndex];
        }
        $authfile = JAWS_PATH . "gadgets/Users/Account/$authtype/LoginForgot.php";
        if (!file_exists($authfile)) {
            Jaws_Error::Fatal($authtype. ' login recovery driver doesn\'t exists');
        }

        // set authentication type in session
        $this->app->session->setAttribute('auth', $authtype);

        // store referrer into session
        $referrer = $this->gadget->request->fetch('referrer');
        if (empty($referrer)) {
            $referrer = bin2hex(Jaws_Utils::getRequestURL());
        }
        $this->gadget->session->update('referrer', $referrer);

        // load authentication method driver
        $classname = "Users_Account_{$authtype}_LoginForgot";
        $objAccount = new $classname($this->gadget);
        return $objAccount->LoginForgot(Jaws_XSS::filterURL(hex2bin($referrer), true, true));
/*
        $response = $this->gadget->session->pop('LoginForgot');
        if (!isset($response['data'])) {
            $post = array(
                'step'  => 0,
                'email' => '',
                'key'   => '',
            );
        } else {
            $post = $response['data'];
            $post['step'] = (int)$post['step'];
        }

        // Load the template
        $tpl = $this->gadget->template->load('LoginForgot.html');
        $tpl->SetBlock('forgot');
        $tpl->SetVariable('step', (int)$post['step']);
        $tpl->SetVariable('title', _t('USERS_FORGOT_REMEMBER'));

        switch ($post['step']) {
            case 2:
                $tpl->SetBlock('forgot/success');
                $tpl->SetVariable(
                    'message',
                    _t('USERS_FORGOT_RECOVERY_SUCCESS', $this->gadget->urlMap('Login'))
                );
                $tpl->ParseBlock('forgot/success');
                break;

            case 1:
                $tpl->SetBlock('forgot/key');
                $tpl->SetVariable('lbl_key', _t('USERS_FORGOT_RECOVERY_KEY'));
                $tpl->SetVariable('key', $post['key']);
                $tpl->ParseBlock('forgot/key');
                // without break

            default:
                $tpl->SetBlock('forgot/email');
                $tpl->SetVariable('lbl_term', _t('USERS_FORGOT_TERM'));
                $tpl->SetVariable('email', $post['email']);
                if ($post['step']) {
                    $tpl->SetBlock('forgot/email/readonly');
                    $tpl->ParseBlock('forgot/email/readonly');
                }
                $tpl->ParseBlock('forgot/email');
                //captcha
                $tpl->SetBlock('forgot/captcha');
                $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                $mPolicy->loadCaptcha($tpl, 'forgot/captcha');
                $tpl->ParseBlock('forgot/captcha');
                // action
                $tpl->SetBlock('forgot/action');
                $tpl->SetVariable('remember', _t('GLOBAL_REQUEST'));
                $tpl->ParseBlock('forgot/action');
        }

        if ($response = $this->gadget->session->pop('LoginForgot')) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }
        $tpl->ParseBlock('forgot');
        return $tpl->Get();
*/
    }

    /**
     * Verifies if user/email/(captcha) are valid, if they are a mail
     * is sent to user with a secret(MD5) key
     *
     * @access  public
     * @return  void
     */
    function LoginRecovery()
    {
        // fetch authentication type from session
        $authtype = $this->app->session->getAttribute('auth');
        if (empty($authtype)) {
            return Jaws_HTTPError::Get(401, '', 'Authentication type is not valid!');
        }

        if ($this->gadget->registry->fetch('password_recovery') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        // parse referrer url
        $referrer = Jaws_XSS::filterURL(hex2bin($this->gadget->session->fetch('referrer')), true, true);

        $classname = "Users_Account_{$authtype}_LoginRecovery";
        $objAccount = new $classname($this->gadget);
        $recoveryData = $objAccount->LoginRecovery();
        if (Jaws_Error::IsError($recoveryData)) {
            $default_authtype = $this->gadget->registry->fetch('authtype');
            return $objAccount->LoginRecoveryError(
                $recoveryData,
                ($authtype != $default_authtype)? $authtype : '',
                bin2hex($referrer)
            );
        } else {
            // 201 http code for auto login
            http_response_code(201);

            // add required attributes for auto login into jaws
            $recoveryData['auth'] = $authtype;

            // create session & cookie
            $this->app->session->create($recoveryData, $recoveryData['remember']);
            // login event logging
            $this->gadget->event->shout(
                'Log',
                array(
                    'action'   => 'Login',
                    'auth'     => $recoveryData['auth'],
                    'domain'   => (int)$recoveryData['domain'],
                    'username' => $recoveryData['username'],
                    'priority' => JAWS_NOTICE,
                    'status'   => 200,
                )
            );
            // let everyone know a user has been logged in
            $this->gadget->event->shout('LoginUser', $recoveryData);

            $this->gadget->session->push(
                _t('USERS_REGISTRATION_ACTIVATED'),
                'Login.Response',
                RESPONSE_NOTICE
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('Account'));
/*
        $post = $this->gadget->request->fetch(array('step', 'email', 'key'), 'post');

        $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $htmlPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $this->gadget->session->push(
                $resCheck->GetMessage(),
                'LoginForgot',
                RESPONSE_ERROR,
                $post
            );
            return Jaws_Header::Location($this->gadget->urlMap('LoginForgot'));
        }

        if (empty($post['step'])) {
            $result = $this->gadget->model->load('Registration')->SendLoginRecoveryKey($post['email']);
            if (Jaws_Error::IsError($result)) {
                $this->gadget->session->push(
                    $result->GetMessage(),
                    'LoginForgot',
                    RESPONSE_ERROR,
                    $post
                );
            } else {
                $post['step'] = 1;
                $this->gadget->session->push(
                    _t('USERS_FORGOT_REQUEST_SENT'),
                    'LoginForgot',
                    RESPONSE_NOTICE,
                    $post
                );
            }
        } else {
            $result = $this->gadget->model->load('Account')->UpdatePassword($post['email'], $post['key']);
            if (Jaws_Error::IsError($result)) {
                $this->gadget->session->push(
                    $result->getMessage(),
                    'LoginForgot',
                    RESPONSE_ERROR,
                    $post
                );
            } else {
                $post['step'] = 2;
                $this->gadget->session->push(
                    _t('USERS_FORGOT_PASSWORD_CHANGED'),
                    'LoginForgot',
                    RESPONSE_NOTICE,
                    $post
                );
            }
        }

        return Jaws_Header::Location($this->gadget->urlMap('LoginForgot'));
*/
    }

    /**
     * Notify user recovery key
     * @access  public
     * @param   array   $uData  User data array
     * @return  bool    True
     */
    function NotifyRecoveryKey($uData)
    {
        // generate recovery key
        $rcvkey = array(
            'text' => Jaws_Utils::RandomText(5, array('number' => true)),
            'time' => time()
        );

        $site_url = $this->app->getSiteURL('/');
        $settings = $this->app->registry->fetchAll('Settings');

        $tpl = $this->gadget->template->load('LoginForgotNotification.html');
        $tpl->SetBlock('Notification');
        $tpl->SetVariable('say_hello', _t('USERS_EMAIL_REPLACEMENT_HELLO', $uData['nickname']));
        $tpl->SetVariable('message', _t('USERS_FORGOT_MAIL_MESSAGE'));
        // recovery key
        $tpl->SetVariable('lbl_key', _t('USERS_FORGOT_RECOVERY_KEY'));
        $tpl->SetVariable('key', $rcvkey['text']);

        $tpl->SetVariable('lbl_username',   _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username',       $uData['username']);
        $tpl->SetVariable('lbl_email',      _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email',          $uData['email']);
        $tpl->SetVariable('lbl_mobile',     _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('mobile',         $uData['mobile']);
        $tpl->SetVariable('lbl_ip',         _t('GLOBAL_IP'));
        $tpl->SetVariable('ip',             $_SERVER['REMOTE_ADDR']);
        $tpl->SetVariable('thanks',         _t('GLOBAL_THANKS'));
        $tpl->SetVariable('site-name',      $settings['site_name']);
        $tpl->SetVariable('site-url',       $site_url);
        $tpl->ParseBlock('Notification');
        $message = $tpl->Get();
        $subject = _t('USERS_FORGOT_REMEMBER', $settings['site_name']);

        // Notify
        $params = array();
        $params['key']     = crc32('Users.Recovery.Key' . $uData['id']);
        $params['title']   = $subject;
        $params['summary'] = _t(
            'USERS_FORGOT_LOGIN_SUMMARY',
            $uData['nickname'],
            $site_url,
            $uData['username'],
            $uData['email'],
            $uData['mobile'],
            $rcvkey['text']
        );

        $params['description'] = $this->gadget->plugin->parse($message);
        $params['emails']      = array($uData['email']);
        $params['mobiles']     = array($uData['mobile']);
        $this->gadget->event->shout('Notify', $params);

        // update session login-key
        $this->gadget->session->update('rcvkey', $rcvkey);

        return true;
    }

}