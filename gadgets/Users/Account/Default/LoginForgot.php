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
    function LoginForgot($defaults = '', $referrer = '')
    {
        $this->AjaxMe('index.js');
        // Load the template
        $tpl = $this->gadget->template->load('LoginForgot.html');
        $tpl->SetBlock('forgot');
        $tpl->SetVariable('title', $this::t('FORGOT_REMEMBER'));

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
            $reqpost['rcvstep'] = (int)$reqpost['rcvstep'];
        }

        // redirect to home page if user logged and action called directly
        if ($this->app->session->user->logged && $reqpost['rcvstep'] != 4) {
            return Jaws_Header::Location('');
        }

        switch ($reqpost['rcvstep']) {
            case 2:
                $this->LoginForgotStep2($tpl, $reqpost, $referrer);
                break;

            case 3:
                $this->LoginForgotStep3($tpl, $reqpost, $referrer);
                break;

            case 4:
                $this->LoginForgotStep4($tpl, $reqpost, $referrer);
                break;

            default:
                $this->LoginForgotStep1($tpl, $reqpost, $referrer);
        }

        if (!empty($response['text'])) {
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
    private function LoginForgotStep1(&$tpl, $reqpost, $referrer)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/forgot_step_1");

        $tpl->SetVariable('domain',   $reqpost['domain']);
        $tpl->SetVariable('account',  $reqpost['account']);
        $tpl->SetVariable('remember', $reqpost['remember']);
        $tpl->SetVariable('lbl_account',  Jaws::t('ACCOUNT'));
        $tpl->SetVariable('lbl_account_hint',  Jaws::t('ACCOUNT_HINT'));
        $tpl->SetVariable('lbl_remember', Jaws::t('REMEMBER_ME'));

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl, 'login');

        $tpl->SetVariable('recovery', Jaws::t('REQUEST'));
        $tpl->SetVariable('url_back', $referrer);
        $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));

        $tpl->ParseBlock("$block/forgot_step_1");
    }

    /**
     * Get HTML registration step 2 form
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function LoginForgotStep2(&$tpl, $reqpost, $referrer)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/forgot_step_2");

        $tpl->SetVariable('lbl_account', Jaws::t('ACCOUNT'));
        $tpl->SetVariable('account', $reqpost['account']);
        $tpl->SetVariable('lbl_key', $this::t('REGISTRATION_KEY'));

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl, 'login');

        $tpl->SetVariable('recovery', Jaws::t('REQUEST'));
        $tpl->SetVariable('url_back', $referrer);
        $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));

        $tpl->ParseBlock("$block/forgot_step_2");
    }

    /**
     * Get HTML registration step 3 form
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function LoginForgotStep3(&$tpl, $reqpost, $referrer)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/forgot_step_3");

        $tpl->SetVariable('lbl_account', Jaws::t('ACCOUNT'));
        $tpl->SetVariable('account', $reqpost['account']);
        $tpl->SetVariable('lbl_password', Jaws::t('PASSWORD'));
        $tpl->SetVariable('lbl_remember', Jaws::t('REMEMBER_ME'));

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $tpl->SetBlock("$block/forgot_step_3/encryption");
            $tpl->SetVariable('pubkey', $JCrypt->getPublic());
            $tpl->ParseBlock("$block/forgot_step_3/encryption");

            // usecrypt
            $tpl->SetBlock("$block/forgot_step_3/usecrypt");
            $tpl->SetVariable('lbl_usecrypt', Jaws::t('LOGIN_SECURE'));
            if (empty($reqpost['pubkey']) || !empty($reqpost['usecrypt'])) {
                $tpl->SetBlock("$block/forgot_step_3/usecrypt/selected");
                $tpl->ParseBlock("$block/forgot_step_3/usecrypt/selected");
            }
            $tpl->ParseBlock("$block/forgot_step_3/usecrypt");
        }

        $tpl->SetVariable('recovery', Jaws::t('REQUEST'));
        $tpl->SetVariable('url_back', $referrer);
        $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));

        $tpl->ParseBlock("$block/forgot_step_3");
    }

    /**
     * Get HTML registration step 4
     *
     * @access  public
     * @return  string  XHTML template
     */
    private function LoginForgotStep4(&$tpl, $reqpost, $referrer)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/forgot_step_4");
        $tpl->SetVariable('message', $this::t('FORGOT_RECOVERY_SUCCESS'));
        $tpl->SetVariable('url_back', $referrer);
        $tpl->SetVariable('lbl_back', Jaws::t('BACK_TO', Jaws::t('PREVIOUSPAGE')));
        $tpl->ParseBlock("$block/forgot_step_4");
    }

}