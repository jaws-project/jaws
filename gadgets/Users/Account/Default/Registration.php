<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Default_Registration extends Users_Account_Default
{
    /**
     * Builds the registration form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Registration()
    {
        $this->AjaxMe('index.js');
        // Load the template
        $tpl = $this->gadget->template->load('Registration.html');
        $tpl->SetBlock('registration');
        $tpl->SetVariable('title', _t('USERS_REGISTER'));

        $response = $this->gadget->session->pop('Registration.Response');
        if (!isset($response['data'])) {
            $reqpost = array(
                'domain'   => $this->gadget->registry->fetch('default_domain'),
                'regstep'  => 0,
                'username' => '',
                'email'    => '',
                'mobile'   => '',
                'nickname' => '',
                'fname'    => '',
                'lname'    => '',
                'ssn'      => '',
                'dob'      => '',
                'gender'   => 0,
                'remember' => 0
            );
        } else {
            $reqpost = $response['data'];
        }

        if ($reqpost['regstep'] == 2) {
            $this->RegistrationStep3($tpl, $reqpost);
        } else {
            $tpl->SetBlock('registration/request');

            if (empty($reqpost['regstep'])) {
                $this->RegistrationStep1($tpl, $reqpost);
            } else {
                $this->RegistrationStep2($tpl, $reqpost);
            }

            //captcha
            $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $mPolicy->loadCaptcha($tpl, 'LoginBox', 'login');

            $tpl->SetVariable('register', _t('USERS_REGISTER'));
            $tpl->ParseBlock('registration/request');
        }

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('registration');
        return $tpl->Get();
    }

    /**
     * Get HTML registration step 1 form
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function RegistrationStep1(&$tpl, $reqpost)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/reg_step_1");

        $tpl->SetVariable('domain',    $reqpost['domain']);
        $tpl->SetVariable('username',  $reqpost['username']);
        $tpl->SetVariable('email',     $reqpost['email']);
        $tpl->SetVariable('mobile',    $reqpost['mobile']);
        $tpl->SetVariable('nickname',  $reqpost['nickname']);
        $tpl->SetVariable('fname',     $reqpost['fname']);
        $tpl->SetVariable('lname',     $reqpost['lname']);
        $tpl->SetVariable('ssn',       $reqpost['ssn']);
        $tpl->SetVariable('dob',       $reqpost['dob']);
        $tpl->SetVariable('remember',  $reqpost['remember']);
        $tpl->SetVariable("selected_gender_{$reqpost['gender']}", 'selected="selected"');
        $tpl->SetVariable('lbl_account_info',  _t('USERS_ACCOUNT_INFO'));
        $tpl->SetVariable('lbl_username',      _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('validusernames',    _t('USERS_REGISTRATION_VALID_USERNAMES'));
        $tpl->SetVariable('lbl_email',         _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('lbl_mobile',        _t('USERS_CONTACTS_MOBILE_NUMBER'));
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
        $tpl->SetVariable('lbl_remember',      _t('GLOBAL_REMEMBER_ME'));

        $tpl->ParseBlock("$block/reg_step_1");
    }

    /**
     * Get HTML registration step 2 form
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function RegistrationStep2(&$tpl, $reqpost)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/reg_step_2");

        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('email',    isset($reqpost['email'])? $reqpost['email'] : '');
        $tpl->SetVariable('mobile',   isset($reqpost['mobile'])? $reqpost['mobile'] : '');
        $tpl->SetVariable('mobile',   isset($reqpost['remember'])? $reqpost['remember'] : '0');

        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('lbl_email',    _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('lbl_mobile',   _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_regkey',   _t('USERS_REGISTRATION_KEY'));
        $tpl->SetVariable('lbl_remember', _t('GLOBAL_REMEMBER_ME'));

        $tpl->ParseBlock("$block/reg_step_2");
    }

    /**
     * Get HTML registration step 3
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function RegistrationStep3(&$tpl, $reqpost)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/reg_step_3");
        $anon_activation = $this->gadget->registry->fetch('anon_activation');
        switch ($anon_activation) {
            case 'admin':
                $message = _t('USERS_REGISTRATION_ACTIVATION_REQUIRED_BY_ADMIN');
                break;

            case 'user':
                $message = _t('USERS_REGISTRATION_ACTIVATED_BY_USER');
                break;

            default:
                $message = _t('USERS_REGISTRATION_ACTIVATED_BY_AUTO');
                break;
        }
        
        $tpl->SetVariable('message', $message);
        $tpl->ParseBlock("$block/reg_step_3");
    }

}