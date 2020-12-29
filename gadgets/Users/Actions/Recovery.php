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
        $drivers = array_map('basename', glob(ROOT_JAWS_PATH . 'gadgets/Users/Account/*', GLOB_ONLYDIR));
        if (false === $dIndex = array_search(strtolower($authtype), array_map('strtolower', $drivers))) {
            $GLOBALS['log']->Log(
                JAWS_NOTICE,
                $authtype. ' login recovery driver doesn\'t exists, switched to default driver'
            );
            $authtype = 'Default';
        } else {
            $authtype = $drivers[$dIndex];
        }
        $authfile = ROOT_JAWS_PATH . "gadgets/Users/Account/$authtype/LoginForgot.php";
        if (!file_exists($authfile)) {
            Jaws_Error::Fatal($authtype. ' login recovery driver doesn\'t exists');
        }

        // set authentication type in session
        $this->gadget->session->auth = $authtype;

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

            // overwrite referrer to default login/register/recovery transfer gadget
            $defaultActionAttribute = 'default_action';
            $default_transfer_gadget = $this->gadget->registry->fetch('login_transfer_gadget_index');
            if (!empty($default_transfer_gadget)) {
                $defaultAction = Jaws_Gadget::getInstance($default_transfer_gadget)->$defaultActionAttribute;
                if (!Jaws_Error::IsError($defaultAction) && !empty($defaultAction)) {
                    $referrer = bin2hex(Jaws_Gadget::getInstance($default_transfer_gadget)->urlMap($defaultAction));
                }
            }
        }
        $this->gadget->session->referrer = $referrer;

        // load authentication method driver
        $classname = "Users_Account_{$authtype}_LoginForgot";
        $objAccount = new $classname($this->gadget);
        return $objAccount->LoginForgot(Jaws_XSS::filterURL(hex2bin($referrer), true, true));
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
        $authtype = $this->gadget->session->auth;
        if (empty($authtype)) {
            return Jaws_HTTPError::Get(401, '', 'Authentication type is not valid!');
        }

        if ($this->gadget->registry->fetch('password_recovery') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        // parse referrer url
        $referrer = Jaws_XSS::filterURL(hex2bin($this->gadget->session->referrer), true, true);

        $classname = "Users_Account_{$authtype}_LoginRecovery";
        $objAccount = new $classname($this->gadget);
        $recoveryData = $objAccount->LoginRecovery();
        if (!Jaws_Error::IsError($recoveryData)) {
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
                    'result'   => 200,
                    'status'   => true,
                )
            );
            // let everyone know a user has been logged in
            $this->gadget->event->shout('LoginUser', $recoveryData);
        }

        $default_authtype = $this->gadget->registry->fetch('authtype');
        return $objAccount->LoginRecoveryError(
            $recoveryData,
            ($authtype != $default_authtype)? $authtype : '',
            $referrer
        );
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
        $tpl->SetVariable('say_hello', $this::t('EMAIL_REPLACEMENT_HELLO', $uData['nickname']));
        $tpl->SetVariable('message', $this::t('FORGOT_MAIL_MESSAGE'));
        // recovery key
        $tpl->SetVariable('lbl_key', $this::t('FORGOT_RECOVERY_KEY'));
        $tpl->SetVariable('key', $rcvkey['text']);

        $tpl->SetVariable('lbl_username',   $this::t('USERS_USERNAME'));
        $tpl->SetVariable('username',       $uData['username']);
        $tpl->SetVariable('lbl_email',      Jaws::t('EMAIL'));
        $tpl->SetVariable('email',          $uData['email']);
        $tpl->SetVariable('lbl_mobile',     $this::t('CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('mobile',         $uData['mobile']);
        $tpl->SetVariable('lbl_ip',         Jaws::t('IP'));
        $tpl->SetVariable('ip',             $_SERVER['REMOTE_ADDR']);
        $tpl->SetVariable('thanks',         Jaws::t('THANKS'));
        $tpl->SetVariable('site-name',      $settings['site_name']);
        $tpl->SetVariable('site-url',       $site_url);
        $tpl->ParseBlock('Notification');
        $message = $tpl->Get();
        $subject = $this::t('FORGOT_REMEMBER', $settings['site_name']);

        // Notify
        $params = array();
        $params['name']    = 'UserRecovery';
        $params['key']     = $uData['id'];
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
        $params['verbose'] = $this->gadget->plugin->parse($message);
        $params['emails']  = array($uData['email']);
        $params['mobiles'] = array($uData['mobile']);
        $this->gadget->event->shout('Notify', $params);

        // update session login-key
        $this->gadget->session->rcvkey = $rcvkey;

        return true;
    }

}