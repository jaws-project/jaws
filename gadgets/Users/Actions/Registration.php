<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Registration extends Jaws_Gadget_Action
{
    /**
     * Tells the user the registation process is done
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Registered()
    {
        // Load the template
        $tpl = $this->gadget->template->load('Registered.html');
        $tpl->SetBlock('registered');
        $tpl->SetVariable('title', _t('USERS_REGISTER_REGISTERED'));

        switch ($this->gadget->registry->fetch('anon_activation')) {
            case 'admin':
                $message = _t('USERS_ACTIVATE_ACTIVATION_BY_ADMIN_MSG');
                break;
            case 'user':
                $message = _t('USERS_ACTIVATE_ACTIVATION_BY_USER_MSG');
                break;
            default:
                $message = _t('USERS_REGISTER_REGISTERED_MSG', $this->gadget->urlMap('LoginBox'));
        }

        $tpl->SetVariable('registered_msg', $message);
        $tpl->ParseBlock('registered');
        return $tpl->Get();
    }

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
        $post = jaws()->request->fetch(
            array(
                'username', 'email', 'nickname', 'password', 'password_check',
                'fname', 'lname', 'gender', 'ssn', 'dob_year', 'dob_month', 'dob_day',
                'url'
            ),
            'post'
        );

        // validate url
        if (!preg_match('|^\S+://\S+\.\S+.+$|i', $post['url'])) {
            $post['url'] = '';
        }

        $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $htmlPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $result = $resCheck->getMessage();
        }

        if (empty($result)) {
            if ($post['password'] !== $post['password_check']) {
                $result = _t('USERS_USERS_PASSWORDS_DONT_MATCH');
            } else {
                $dob  = null;
                if (!empty($post['dob_year']) && !empty($post['dob_year']) && !empty($post['dob_year'])) {
                    $date = Jaws_Date::getInstance();
                    $dob  = $date->ToBaseDate($post['dob_year'], $post['dob_month'], $post['dob_day']);
                    $dob  = date('Y-m-d H:i:s', $dob['timestamp']);
                }

                $uModel = $this->gadget->model->load('Registration');
                $result = $uModel->CreateUser(
                    $post['username'],
                    $post['email'],
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
                if ($result === true) {
                    Jaws_Header::Location($this->gadget->urlMap('Registered'));
                }
            }
        }

        // unset unnecessary registration data
        unset($post['password'], $post['password_check'], $post['random_password']);
        $GLOBALS['app']->Session->PushResponse(
            $result,
            'Users.Registration',
            RESPONSE_ERROR,
            $post
        );
        Jaws_Header::Location($this->gadget->urlMap('Registration'));
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
            Jaws_Header::Location('');
        }

        if ($this->gadget->registry->fetch('anon_register') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        // Load the template
        $tpl = $this->gadget->template->load('Register.html');
        $tpl->SetBlock('register');
        $tpl->SetVariable('title', _t('USERS_REGISTER'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $tpl->SetVariable('lbl_account_info',  _t('USERS_ACCOUNT_INFO'));
        $tpl->SetVariable('lbl_username',      _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('validusernames',    _t('USERS_REGISTER_VALID_USERNAMES'));
        $tpl->SetVariable('lbl_email',         _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('lbl_url',           _t('GLOBAL_URL'));
        $tpl->SetVariable('lbl_nickname',         _t('USERS_USERS_NICKNAME'));
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

        $response = $GLOBALS['app']->Session->PopResponse('Users.Registration');
        if (!isset($response['data'])) {
            $post_data = $response['data'];
            $tpl->SetVariable('username',  $post_data['username']);
            $tpl->SetVariable('email',     $post_data['email']);
            $tpl->SetVariable('url',       $post_data['url']);
            $tpl->SetVariable('nickname',  $post_data['nickname']);
            $tpl->SetVariable('fname',     $post_data['fname']);
            $tpl->SetVariable('lname',     $post_data['lname']);
            $tpl->SetVariable('ssn',       $post_data['ssn']);
            $tpl->SetVariable('dob_year',  $post_data['dob_year']);
            $tpl->SetVariable('dob_month', $post_data['dob_month']);
            $tpl->SetVariable('dob_day',   $post_data['dob_day']);
            $tpl->SetVariable("selected_gender_{$post_data['gender']}", 'selected="selected"');
        } else {
            $tpl->SetVariable('url', 'http://');
            $tpl->SetVariable("selected_gender_0", 'selected="selected"');
        }

        $tpl->SetVariable('register', _t('USERS_REGISTER'));

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl, 'register');

        if (!empty($response)) {
            $tpl->SetBlock('register/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('register/response');
        }

        $tpl->ParseBlock('register');
        return $tpl->Get();
    }

    /**
     * Activates the user
     *
     * @access  public
     * @return  string  Appropriate notice or error message
     */
    function ActivateUser()
    {
        if ($GLOBALS['app']->Session->Logged() && !$GLOBALS['app']->Session->IsSuperAdmin()) {
            Jaws_Header::Location('');
        }

        if ($this->gadget->registry->fetch('anon_register') !== 'true') {
            return Jaws_HTTPError::Get(404);
        }

        $key = jaws()->request->fetch('key', 'get');

        $jUser = new Jaws_User;
        $user = $jUser->GetUserByEmailVerifyKey($key);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return _t('USERS_ACTIVATION_KEY_NOT_VALID');
        }

        $result = $jUser->UpdateUser(
            $user['id'],
            array(
                'username' => $user['username'],
                'nickname' => $user['nickname'],
                'email'    => $user['email'],
                'status'   => 1
            )
        );

        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $anon_activation = $this->gadget->registry->fetch('anon_activation');
        $result = $this->ActivateNotification($user, $anon_activation);
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        if ($anon_activation == 'user') {
            return _t('USERS_ACTIVATE_ACTIVATED_BY_USER_MSG', $this->gadget->urlMap('LoginBox'));
        } else {
            return _t('USERS_ACTIVATE_ACTIVATED_BY_ADMIN_MSG');
        }
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
        $tpl->SetVariable('say_hello', _t('USERS_REGISTER_HELLO', $user['nickname']));
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
        $subject = _t('USERS_REGISTER_SUBJECT', $site_name);

        $mail = new Jaws_Mail;
        $mail->SetFrom();
        $mail->AddRecipient($user['email']);
        $mail->SetSubject($subject);
        $mail->SetBody($this->gadget->ParseText($body));
        return $mail->send();
    }

}