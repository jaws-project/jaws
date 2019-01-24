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
     * Registers the user
     *
     * @access  public
     * @return  void
     */
    function DoRegister()
    {
        if ($this->gadget->registry->fetch('anon_register') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        $result  = '';
        $post = $this->gadget->request->fetch(
            array(
                'domain', 'user', 'username', 'email', 'mobile', 'nickname', 'password', 'password_check',
                'fname', 'lname', 'gender', 'ssn', 'dob', 'url', 'step', 'key'
            ),
            'post'
        );

        try {
            // check captcha policy
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $resCheck = $htmlPolicy->checkCaptcha();
            if (Jaws_Error::IsError($resCheck)) {
                unset($post['password'], $post['password_check'], $post['random_password']);
                throw new Exception($resCheck->GetMessage());
            }

            if (empty($post['step'])) {
                // user creation
                if ($post['password'] !== $post['password_check']) {
                    unset($post['password'], $post['password_check'], $post['random_password']);
                    throw new Exception(_t('USERS_USERS_PASSWORDS_DONT_MATCH'));
                }

                // validate url
                if (!preg_match('|^\S+://\S+\.\S+.+$|i', $post['url'])) {
                    $post['url'] = '';
                }

                $dob = null;
                if (!empty($post['dob'])) {
                    $dob = Jaws_Date::getInstance()->ToBaseDate(explode('-', $post['dob']), 'Y-m-d');
                    $dob = $GLOBALS['app']->UserTime2UTC($dob, 'Y-m-d');
                }

                $result = $this->gadget->model->load('Registration')->CreateUser(
                    $post['domain'],
                    $post['username'],
                    $post['email'],
                    $post['mobile'],
                    $post['nickname'],
                    $post['fname'],
                    $post['lname'],
                    $post['gender'],
                    $post['ssn'],
                    $dob,
                    $post['url'],
                    $post['password'],
                    $this->gadget->registry->fetch('anon_group')
                );
                if (Jaws_Error::IsError($result)) {
                    unset($post['password'], $post['password_check'], $post['random_password']);
                    throw new Exception($result->getMessage());
                }

                // increase step
                $post = array(
                    'user' => $result,
                    'step' => 1,
                    'key'  => '',
                );
                $this->gadget->session->push(
                    _t('USERS_REGISTRATION_REGISTERED'),
                    'Registration',
                    RESPONSE_NOTICE,
                    $post
                );
            } else {
                // check verification key
                if ($this->gadget->model->load('Registration')->verifyKey($post['user'], $post['key'])) {
                    $post = array(
                        'user' => $post['user'],
                        'step' => 2,
                    );
                    $this->gadget->session->push(
                        _t('USERS_REGISTRATION_VERIFIED'),
                        'Registration',
                        RESPONSE_NOTICE,
                        $post
                    );
                } else {
                    $post = array(
                        'user' => $post['user'],
                        'step' => 1,
                        'key'  => $post['key'],
                    );
                    throw new Exception( _t('USERS_REGISTRATION_INVALID_KEY'));
                }
            }
        } catch (Exception $e) {
            $this->gadget->session->push(
                $e->getMessage(),
                'Registration',
                RESPONSE_ERROR,
                $post
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('Registration'));
    }

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

        $response = $this->gadget->session->pop('Registration');
        if (!isset($response['data'])) {
            $post = array(
                'domain' => 0,
                'user'   => 0,
                'step'   => 0,
                'key'    => '',
                'username' => '',
                'email' => '',
                'mobile' => '',
                'url' => '',
                'nickname' => '',
                'fname' => '',
                'lname' => '',
                'ssn' => '',
                'dob' => '',
                'gender' => 0
            );
        } else {
            $post = $response['data'];
            $post['step'] = (int)$post['step'];
        }

        // Load the template
        $tpl = $this->gadget->template->load('Registration.html');
        $tpl->SetBlock('registration');
        $tpl->SetVariable('user', $post['user']);
        $tpl->SetVariable('step', $post['step']);
        $tpl->SetVariable('title', _t('USERS_REGISTER'));

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        // fetch activation type
        $anon_activation = $this->gadget->registry->fetch('anon_activation');
        switch ($post['step']) {
            case 1:
                if ($anon_activation == 'user') {
                    // key
                    $tpl->SetBlock('registration/key');
                    $tpl->SetVariable('message', _t('USERS_REGISTRATION_ACTIVATION_REQUIRED_BY_USER'));
                    $tpl->SetVariable('lbl_key', _t('USERS_REGISTRATION_KEY'));
                    $tpl->SetVariable('key', $post['key']);
                    $tpl->ParseBlock('registration/key');
                    //captcha
                    $tpl->SetBlock('registration/captcha');
                    $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                    $mPolicy->loadCaptcha($tpl, 'registration/captcha');
                    $tpl->ParseBlock('registration/captcha');
                    // action
                    $tpl->SetBlock('registration/action');
                    $tpl->SetVariable('register', _t('USERS_REGISTER'));
                    $tpl->ParseBlock('registration/action');
                    break;
                } else {
                    $post['step'] = 2;
                    // don't break
                }

            case 2:
                $tpl->SetBlock('registration/success');
                // select suitable message
                switch ($anon_activation) {
                    case 'admin':
                        $message = _t('USERS_REGISTRATION_ACTIVATION_REQUIRED_BY_ADMIN');
                        break;
                    case 'user':
                        $message = _t('USERS_REGISTRATION_ACTIVATED_BY_USER', $this->gadget->urlMap('LoginBox'));
                        break;
                    default:
                        $message = _t('USERS_REGISTRATION_ACTIVATED_BY_AUTO', $this->gadget->urlMap('LoginBox'));
                }
                $tpl->SetVariable('message', $message);
                $tpl->ParseBlock('registration/success');
                break;

            default:
                $tpl->SetBlock('registration/request');
                $tpl->SetVariable('domain',    $post['domain']);
                $tpl->SetVariable('username',  $post['username']);
                $tpl->SetVariable('email',     $post['email']);
                $tpl->SetVariable('mobile',    $post['mobile']);
                $tpl->SetVariable('url',       $post['url']);
                $tpl->SetVariable('nickname',  $post['nickname']);
                $tpl->SetVariable('fname',     $post['fname']);
                $tpl->SetVariable('lname',     $post['lname']);
                $tpl->SetVariable('ssn',       $post['ssn']);
                $tpl->SetVariable('dob',       $post['dob']);
                $tpl->SetVariable("selected_gender_{$post['gender']}", 'selected="selected"');
                $tpl->SetVariable('lbl_account_info',  _t('USERS_ACCOUNT_INFO'));
                $tpl->SetVariable('lbl_username',      _t('USERS_USERS_USERNAME'));
                $tpl->SetVariable('validusernames',    _t('USERS_REGISTRATION_VALID_USERNAMES'));
                $tpl->SetVariable('lbl_email',         _t('GLOBAL_EMAIL'));
                $tpl->SetVariable('lbl_mobile',         _t('USERS_CONTACTS_MOBILE_NUMBER'));
                $tpl->SetVariable('lbl_url',           _t('GLOBAL_URL'));
                $tpl->SetVariable('lbl_nickname',       _t('USERS_USERS_NICKNAME'));
                $tpl->SetVariable('lbl_password',      _t('USERS_USERS_PASSWORD'));
                $tpl->SetVariable('sendpassword',      _t('USERS_USERS_SEND_AUTO_PASSWORD'));
                $tpl->SetVariable('lbl_checkpassword', _t('USERS_USERS_PASSWORD_VERIFY'));
                $tpl->SetVariable('lbl_personal_info', _t('USERS_PERSONAL_INFO'));
                $tpl->SetVariable('lbl_fname',         _t('USERS_USERS_FIRSTNAME'));
                $tpl->SetVariable('lbl_lname',         _t('USERS_USERS_LASTNAME'));
                $tpl->SetVariable('lbl_gender',        _t('USERS_USERS_GENDER'));
                $tpl->SetVariable('lbl_ssn',           _t('USERS_USERS_SSN'));
                $tpl->SetVariable('gender_0',          _t('USERS_USERS_GENDER_0'));
                $tpl->SetVariable('gender_1',          _t('USERS_USERS_GENDER_1'));
                $tpl->SetVariable('gender_2',          _t('USERS_USERS_GENDER_2'));
                $tpl->SetVariable('lbl_dob',           _t('USERS_USERS_BIRTHDAY'));
                $tpl->SetVariable('dob_sample',        _t('USERS_USERS_BIRTHDAY_SAMPLE'));
                $tpl->ParseBlock('registration/request');
                //captcha
                $tpl->SetBlock('registration/captcha');
                $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                $mPolicy->loadCaptcha($tpl, 'registration/captcha');
                $tpl->ParseBlock('registration/captcha');
                // action
                $tpl->SetBlock('registration/action');
                $tpl->SetVariable('register', _t('USERS_REGISTER'));
                $tpl->ParseBlock('registration/action');
        }

        $tpl->ParseBlock('registration');
        return $tpl->Get();
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

}