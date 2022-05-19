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
        if ($this->gadget->registry->fetch('anon_register') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

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
                $authtype. ' registration driver doesn\'t exists, switched to default driver'
            );
            $authtype = 'Default';
        } else {
            $authtype = $drivers[$dIndex];
        }
        $authfile = ROOT_JAWS_PATH . "gadgets/Users/Account/$authtype/Login.php";
        if (!file_exists($authfile)) {
            Jaws_Error::Fatal($authtype. ' registration driver doesn\'t exists');
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
        $classname = "Users_Account_{$authtype}_Registration";
        $objAccount = new $classname($this->gadget);
        return $objAccount->Registration(Jaws_XSS::filterURL(hex2bin($referrer), true, true));
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
        $authtype = $this->gadget->session->auth;
        if (empty($authtype)) {
            return Jaws_HTTPError::Get(401, '', 'Authentication type is not valid!');
        }

        if ($this->gadget->registry->fetch('anon_register') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        // parse referrer url
        $referrer = Jaws_XSS::filterURL(hex2bin($this->gadget->session->referrer), true, true);

        $classname = "Users_Account_{$authtype}_Register";
        $objAccount = new $classname($this->gadget);
        $registerData = $objAccount->Register();
        if (!Jaws_Error::IsError($registerData)) {
            // add required attributes for auto login into jaws
            $registerData['auth'] = $authtype;

            // create session & cookie
            $this->app->session->create($registerData, $registerData['remember']);
            // login event logging
            $this->gadget->event->shout(
                'Log',
                array(
                    'action'   => 'Login',
                    'auth'     => $registerData['auth'],
                    'domain'   => (int)$registerData['domain'],
                    'username' => $registerData['username'],
                    'priority' => JAWS_NOTICE,
                    'result'   => 200,
                    'status'   => true,
                )
            );

            // let everyone know a user has been registered
            $this->gadget->event->shout('RegisterUser', $registerData);
            // let everyone know a user has been logged in
            $this->gadget->event->shout('LoginUser', $registerData);
        }

        $default_authtype = $this->gadget->registry->fetch('authtype');
        return $objAccount->RegisterError(
            $registerData,
            ($authtype != $default_authtype)? $authtype : '',
            $referrer
        );
    }

    /**
     * Activates the user
     *
     * @access  public
     * @return  string  Appropriate notice or error message
     */
    function ReplaceUserEmail()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(401);
        }

        $this->gadget->CheckPermission('EditUserEmail');
        $key = $this->gadget->request->fetch('key', 'get');

        $user = $this->app->users->GetUserByEmailVerifyKey($key);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return $this::t('ACTIVATION_KEY_NOT_VALID');
        }

        $result = $this->gadget->model->load('User')->editUser(
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

        return $this::t('EMAIL_REPLACEMENT_REPLACED');
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
        $site_url  = $this->app->getSiteURL('/');
        $site_name = $this->gadget->registry->fetch('site_name', 'Settings');

        $tpl = $this->gadget->template->load('UserNotification.txt');
        $tpl->SetBlock('Notification');
        $tpl->SetVariable('say_hello', $this::t('REGISTRATION_HELLO', $user['nickname']));
        $tpl->SetVariable('message', $this::t('ACTIVATE_ACTIVATED_MAIL_MSG'));
        if ($anon_activation == 'user') {
            $tpl->SetBlock('Notification/IP');
            $tpl->SetVariable('lbl_ip', Jaws::t('IP'));
            $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);
            $tpl->ParseBlock('Notification/IP');
        }

        $tpl->SetVariable('lbl_username', $this::t('USERS_USERNAME'));
        $tpl->SetVariable('username', $user['username']);

        $tpl->SetVariable('thanks', Jaws::t('THANKS'));
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url', $site_url);
        $tpl->ParseBlock('Notification');

        $body = $tpl->Get();
        $subject = $this::t('REGISTRATION_SUBJECT', $site_name);

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
            'text' => Jaws_Utils::RandomText(5, array('number' => true)),
            'time' => time()
        );

        $site_url = $this->app->getSiteURL('/');
        $settings = $this->app->registry->fetchAll('Settings');

        // Notify
        $params = array();
        $params['name']    = 'UserRegistration';
        $params['key']     = $uData['id'];
        $params['title']   = $this::t('REGISTRATION_USER_SUBJECT', $settings['site_name']);
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
        $params['verbose'] = $params['summary'];
        $params['variables'] = array(
            'say_hello'    => $this::t('REGISTRATION_HELLO', $uData['nickname']),
            'message'      => $this::t('REGISTRATION_ACTIVATION_REQUIRED_BY_USER'),
            'lbl_username' => $this::t('USERS_PASSWORD'),
            'username'     => $uData['username'],
            'lbl_password' => $this::t('USERS_PASSWORD'),
            'password'     => $uData['password'],
            'lbl_email'    => Jaws::t('EMAIL'),
            'email'        => $uData['email'],
            'lbl_mobile'   => $this::t('CONTACTS_MOBILE_NUMBER'),
            'mobile'       => $uData['mobile'],
            'lbl_key'      => $this::t('REGISTRATION_KEY'),
            'key'          => $regkey['text'],
            'lbl_ip'       => Jaws::t('IP'),
            'ip'           => $_SERVER['REMOTE_ADDR'],
            'thanks'       => Jaws::t('THANKS'),
            'site-name'    => $settings['site_name'],
            'site-url'     => $site_url
        );
        $params['emails']   = array($uData['email']);
        $params['mobiles']  = array($uData['mobile']);
        $params['template'] = 'UserRegister';
        $this->gadget->event->shout('Notify', $params);

        // update session login-key
        $this->gadget->session->regkey = $regkey;

        return true;
    }

}