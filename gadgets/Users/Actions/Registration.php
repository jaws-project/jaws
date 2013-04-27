<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Registration extends Users_HTML
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
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Registered.html');
        $tpl->SetBlock('registered');
        $tpl->SetVariable('title', _t('USERS_REGISTER_REGISTERED'));

        switch ($this->gadget->registry->get('anon_activation')) {
            case 'admin':
                $message = _t('USERS_ACTIVATE_ACTIVATION_BY_ADMIN_MSG');
                break;
            case 'user':
                $message = _t('USERS_ACTIVATE_ACTIVATION_BY_USER_MSG');
                break;
            default:
                $message = _t('USERS_REGISTER_REGISTERED_MSG', $this->gadget->GetURLFor('LoginBox'));
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
        if ($this->gadget->registry->get('anon_register') !== 'true') {
            return parent::_404();
        }

        $result  = '';
        $request =& Jaws_Request::getInstance();
        $post = $request->get(
            array(
                'username', 'email', 'nickname', 'password', 'password_check',
                'fname', 'lname', 'gender', 'dob_year', 'dob_month', 'dob_day',
                'url'
            ),
            'post'
        );

        // validate url
        if (!preg_match('|^\S+://\S+\.\S+.+$|i', $post['url'])) {
            $post['url'] = '';
        }

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $resCheck = $mPolicy->CheckCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $result = $resCheck->getMessage();
        }

        if (empty($result)) {
            if ($post['password'] !== $post['password_check']) {
                $result = _t('USERS_USERS_PASSWORDS_DONT_MATCH');
            } else {
                $dob  = null;
                if (!empty($post['dob_year']) && !empty($post['dob_year']) && !empty($post['dob_year'])) {
                    $date = $GLOBALS['app']->loadDate();
                    $dob  = $date->ToBaseDate($post['dob_year'], $post['dob_month'], $post['dob_day']);
                    $dob  = date('Y-m-d H:i:s', $dob['timestamp']);
                }

                $uModel = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Registration');
                $result = $uModel->CreateUser($post['username'],
                                              $post['email'],
                                              $post['nickname'],
                                              $post['fname'],
                                              $post['lname'],
                                              $post['gender'],
                                              $dob,
                                              $post['url'],
                                              $post['password'],
                                              $this->gadget->registry->get('anon_group'));
                if ($result === true) {
                    Jaws_Header::Location($this->gadget->GetURLFor('Registered'));
                }
            }
        }

        $GLOBALS['app']->Session->PushSimpleResponse($result, 'Users.Register');

        // unset unnecessary registration data
        unset($post['password'],
              $post['password_check'],
              $post['random_password']);
        $GLOBALS['app']->Session->PushSimpleResponse($post, 'Users.Register.Data');

        Jaws_Header::Location($this->gadget->GetURLFor('Registration'));
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

        if ($this->gadget->registry->get('anon_register') !== 'true') {
            return parent::_404();
        }

        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Register.html');
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
        $tpl->SetVariable('gender_0',          _t('USERS_USERS_GENDER_0'));
        $tpl->SetVariable('gender_1',          _t('USERS_USERS_GENDER_1'));
        $tpl->SetVariable('gender_2',          _t('USERS_USERS_GENDER_2'));
        $tpl->SetVariable('lbl_dob',           _t('USERS_USERS_BIRTHDAY'));
        $tpl->SetVariable('dob_sample',        _t('USERS_USERS_BIRTHDAY_SAMPLE'));

        if ($post_data = $GLOBALS['app']->Session->PopSimpleResponse('Users.Register.Data')) {
            $tpl->SetVariable('username',  $post_data['username']);
            $tpl->SetVariable('email',     $post_data['email']);
            $tpl->SetVariable('url',       $post_data['url']);
            $tpl->SetVariable('nickname',  $post_data['nickname']);
            $tpl->SetVariable('fname',     $post_data['fname']);
            $tpl->SetVariable('lname',     $post_data['lname']);
            $tpl->SetVariable('dob_year',  $post_data['dob_year']);
            $tpl->SetVariable('dob_month', $post_data['dob_month']);
            $tpl->SetVariable('dob_day',   $post_data['dob_day']);
            $tpl->SetVariable("selected_gender_{$post_data['gender']}", 'selected="selected"');
        } else {
            $tpl->SetVariable('url', 'http://');
            $tpl->SetVariable("selected_gender_0", 'selected="selected"');
        }

        $tpl->SetVariable('register', _t('USERS_REGISTER'));
        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        if (false !== $captcha = $mPolicy->LoadCaptcha()) {
            $tpl->SetBlock('register/captcha');
            $tpl->SetVariable('captcha_lbl', $captcha['label']);
            $tpl->SetVariable('captcha_key', $captcha['key']);
            $tpl->SetVariable('captcha', $captcha['captcha']);
            if (!empty($captcha['entry'])) {
                $tpl->SetVariable('captcha_entry', $captcha['entry']);
            }
            $tpl->SetVariable('captcha_msg', $captcha['description']);
            $tpl->ParseBlock('register/captcha');
        }

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Users.Register')) {
            $tpl->SetBlock('register/response');
            $tpl->SetVariable('msg', $response);
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

        if ($this->gadget->registry->get('anon_register') !== 'true') {
            return parent::_404();
        }

        $request =& Jaws_Request::getInstance();
        $key = $request->get('key', 'get');

        $uModel = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Registration');
        $result = $uModel->ActivateUser($key);
        if (Jaws_Error::IsError($result)) {
            return _t('USERS_ACTIVATE_ACTIVATED_BY_ADMIN_MSG');
        }

        if ($result) {
            if ($this->gadget->registry->get('anon_activation') == 'user') {
                return _t('USERS_ACTIVATE_ACTIVATED_BY_USER_MSG', $this->gadget->GetURLFor('LoginBox'));
            } else {
                return _t('USERS_ACTIVATE_ACTIVATED_BY_ADMIN_MSG');
            }
        } else {
            return _t('USERS_ACTIVATION_KEY_NOT_VALID');
        }
    }

}