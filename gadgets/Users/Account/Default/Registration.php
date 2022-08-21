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
    function Registration($defaults = '', $referrer = '')
    {
        $this->AjaxMe('index.js');
        // Load the template
        $tpl = $this->gadget->template->load('Registration.html');
        $tpl->SetBlock('registration');
        $tpl->SetVariable('title', $this::t('REGISTER'));

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
            $reqpost['regstep'] = (int)$reqpost['regstep'];
        }

        // redirect to home page if user logged and action called directly
        if ($this->app->session->user->logged && $reqpost['regstep'] != 3) {
            return Jaws_Header::Location('');
        }

        switch ($reqpost['regstep']) {
            case 2:
                $this->RegistrationStep2($tpl, $reqpost, $referrer);
                break;

            case 3:
                $this->RegistrationStep3($tpl, $reqpost, $referrer);
                break;

            default:
                $this->RegistrationStep1($tpl, $reqpost, $referrer);
        }

        if (!empty($response['text'])) {
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
    private function RegistrationStep1(&$tpl, $reqpost, $referrer)
    {
        http_response_code(401);

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
        $tpl->SetVariable('lbl_account_info',  $this::t('ACCOUNT_INFO'));
        $tpl->SetVariable('lbl_username',      $this::t('USERS_USERNAME'));
        $tpl->SetVariable('validusernames',    $this::t('REGISTRATION_VALID_USERNAMES'));
        $tpl->SetVariable('lbl_email',         Jaws::t('EMAIL'));
        $tpl->SetVariable('lbl_mobile',        $this::t('CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_url',           Jaws::t('URL'));
        $tpl->SetVariable('lbl_nickname',       $this::t('USERS_NICKNAME'));
        $tpl->SetVariable('lbl_password',      $this::t('USERS_PASSWORD'));
        $tpl->SetVariable('sendpassword',      $this::t('USERS_SEND_AUTO_PASSWORD'));
        $tpl->SetVariable('lbl_checkpassword', $this::t('USERS_PASSWORD_VERIFY'));
        $tpl->SetVariable('lbl_personal_info', $this::t('PERSONAL_INFO'));
        $tpl->SetVariable('lbl_fname',         $this::t('USERS_FIRSTNAME'));
        $tpl->SetVariable('lbl_lname',         $this::t('USERS_LASTNAME'));
        $tpl->SetVariable('lbl_gender',        $this::t('USERS_GENDER'));
        $tpl->SetVariable('lbl_ssn',           $this::t('USERS_SSN'));
        $tpl->SetVariable('gender_0',          $this::t('USERS_GENDER_0'));
        $tpl->SetVariable('gender_1',          $this::t('USERS_GENDER_1'));
        $tpl->SetVariable('gender_2',          $this::t('USERS_GENDER_2'));
        $tpl->SetVariable('lbl_dob',           $this::t('USERS_BIRTHDAY'));
        $tpl->SetVariable('dob_sample',        $this::t('USERS_BIRTHDAY_SAMPLE'));
        $tpl->SetVariable('lbl_remember',      Jaws::t('REMEMBER_ME'));

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl);

        $tpl->SetVariable('register', $this::t('REGISTER'));
        $tpl->SetVariable('url_back', $referrer);
        $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));

        $tpl->ParseBlock("$block/reg_step_1");
    }

    /**
     * Get HTML registration step 2 form
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function RegistrationStep2(&$tpl, $reqpost, $referrer)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/reg_step_2");

        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('email',    isset($reqpost['email'])? $reqpost['email'] : '');
        $tpl->SetVariable('mobile',   isset($reqpost['mobile'])? $reqpost['mobile'] : '');
        $tpl->SetVariable('mobile',   isset($reqpost['remember'])? $reqpost['remember'] : '0');

        $tpl->SetVariable('lbl_username', Jaws::t('USERNAME'));
        $tpl->SetVariable('lbl_email',    Jaws::t('EMAIL'));
        $tpl->SetVariable('lbl_mobile',   $this::t('CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_regkey',   $this::t('REGISTRATION_KEY'));
        $tpl->SetVariable('lbl_remember', Jaws::t('REMEMBER_ME'));

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl);

        $tpl->SetVariable('register', $this::t('REGISTER'));
        $tpl->SetVariable('url_back', $referrer);
        $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));

        $tpl->ParseBlock("$block/reg_step_2");
    }

    /**
     * Get HTML registration step 3
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function RegistrationStep3(&$tpl, $reqpost, $referrer)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/reg_step_3");
        $anon_activation = $this->gadget->registry->fetch('anon_activation');
        switch ($anon_activation) {
            case 'admin':
                $message = $this::t('REGISTRATION_ACTIVATION_REQUIRED_BY_ADMIN');
                break;

            case 'user':
                $message = $this::t('REGISTRATION_ACTIVATED_BY_USER');
                break;

            default:
                $message = $this::t('REGISTRATION_ACTIVATED_BY_AUTO');
                break;
        }
        
        $tpl->SetVariable('message', $message);
        $tpl->ParseBlock("$block/reg_step_3");
    }

}