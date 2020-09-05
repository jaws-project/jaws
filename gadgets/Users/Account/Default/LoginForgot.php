<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Default_LoginForgot extends Users_Account_Default
{
    /**
     * Builds the login forgot form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function LoginForgot($referrer = '')
    {
        $this->AjaxMe('index.js');
        // Load the template
        $tpl = $this->gadget->template->load('LoginForgot.html');
        $tpl->SetBlock('forgot');
        $tpl->SetVariable('title', _t('USERS_FORGOT_REMEMBER'));

        $response = $this->gadget->session->pop('Recovery.Response');
        if (!isset($response['data'])) {
            $reqpost = array(
                'domain'   => $this->gadget->registry->fetch('default_domain'),
                'rcvstep'  => 0,
                'account'  => '',
                'remember' => 0
            );
        } else {
            $reqpost = $response['data'];
        }

        if ($reqpost['rcvstep'] == 2) {
            $this->LoginForgotStep3($tpl, $reqpost);
        } else {
            $tpl->SetBlock('forgot/request');

            if (empty($reqpost['rcvstep'])) {
                $this->LoginForgotStep1($tpl, $reqpost);
            } else {
                $this->LoginForgotStep2($tpl, $reqpost);
            }

            //captcha
            $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $mPolicy->loadCaptcha($tpl, 'LoginBox', 'login');

            $tpl->SetVariable('recovery', Jaws::t('REQUEST'));
            $tpl->SetVariable('url_back', $referrer);
            $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));

            $tpl->ParseBlock('forgot/request');
        }

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('forgot');
        return $tpl->Get();
    }

    /**
     * Get HTML registration step 1 form
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function LoginForgotStep1(&$tpl, $reqpost)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/forgot_step_1");

        $tpl->SetVariable('domain',   $reqpost['domain']);
        $tpl->SetVariable('account',  $reqpost['account']);
        $tpl->SetVariable('remember', $reqpost['remember']);
        $tpl->SetVariable('lbl_account',  Jaws::t('EMAIL'));
        $tpl->SetVariable('lbl_remember', Jaws::t('REMEMBER_ME'));

        $tpl->ParseBlock("$block/forgot_step_1");
    }

    /**
     * Get HTML registration step 2 form
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function LoginForgotStep2(&$tpl, $reqpost)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/forgot_step_2");

        $tpl->SetVariable('username', isset($reqpost['username'])? $reqpost['username'] : '');
        $tpl->SetVariable('email',    isset($reqpost['email'])? $reqpost['email'] : '');
        $tpl->SetVariable('mobile',   isset($reqpost['mobile'])? $reqpost['mobile'] : '');
        $tpl->SetVariable('mobile',   isset($reqpost['remember'])? $reqpost['remember'] : '0');

        $tpl->SetVariable('lbl_username', Jaws::t('USERNAME'));
        $tpl->SetVariable('lbl_email',    Jaws::t('EMAIL'));
        $tpl->SetVariable('lbl_mobile',   _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_regkey',   _t('USERS_REGISTRATION_KEY'));
        $tpl->SetVariable('lbl_remember', Jaws::t('REMEMBER_ME'));

        $tpl->ParseBlock("$block/forgot_step_2");
    }

    /**
     * Get HTML registration step 3
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function LoginForgotStep3(&$tpl, $reqpost)
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