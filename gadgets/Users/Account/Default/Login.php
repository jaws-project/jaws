<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Default_Login extends Users_Account_Default
{
    /**
     * Builds the login box
     *
     * @access  public
     * @param   string  $referrer   Referrer page url
     * @return  string  XHTML content
     */
    function Login($defaults = '', $referrer = '')
    {
        return (JAWS_SCRIPT == 'index')? $this->IndexLogin($referrer) : $this->AdminLogin($referrer);
    }

    /**
     * Builds the front-end login box
     *
     * @access  public
     * @param   string  $referrer   Referrer page url
     * @return  array   $assigns    processed data for user-interface
     */
    function loginInterface($referrer)
    {
        $assigns = array();
        $bad_try_count = 0;
        $user = $this->gadget->session->temp_login_user;
        $response = $this->gadget->session->pop('Login.Response');
        if (!isset($response['data'])) {
            $assigns['domain'] = $this->gadget->registry->fetch('default_domain');
            $assigns['username'] = '';
            $assigns['loginstep'] = 'user';
            $assigns['remember'] = '';
            $assigns['usecrypt'] = 1;
            $assigns['referrer'] = $referrer;
        } else {
            $assigns = $response['data'];
            $assigns['response'] = array(
                'text' => $response['text'],
                'type' => $response['type'],
            );
            if (!empty($user)) {
                $assigns['domain'] = $user['domain'];
                $assigns['username'] = $user['username'];

                $bad_try_cache_key = Jaws_Cache::key("loginstep.{$assigns['loginstep']}.{$user['username']}");
                $bad_try_count = (int)$this->app->cache->get($bad_try_cache_key);
            }
        }

        // we need this for enabling captch for IPs trying fetch user's name/email/mobile
        if ($assigns['loginstep'] == 'user') {
            $addr = Jaws_Utils::GetRemoteAddress();
            $ipAddr = $addr['public']? $addr['client'] : $addr['proxy'];
            $bad_try_cache_key = Jaws_Cache::key('loginstep.user.'. $ipAddr);
            $bad_try_count = (int)$this->app->cache->get($bad_try_cache_key);
        }

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $assigns['pubkey'] = $JCrypt->getPublic();
        }

        // domain
        if ($this->gadget->registry->fetch('multi_domain') == 'true') {
            $assigns['domains'] = $this->gadget->model->load('Domains')->getDomains();
            if (Jaws_Error::IsError($assigns['domains'])) {
                $assigns['domains'] = array();
            }
        }

        // captcha
        $assigns['captcha'] = Jaws_Gadget::getInstance('Policy')
            ->action
            ->load('Captcha')
            ->xloadCaptcha('login');

        $max_captcha_login_bad_count = (int)$this->gadget->registry->fetch('login_captcha_status', 'Policy');
        if ($bad_try_count >= $max_captcha_login_bad_count) {
            $assigns['captcha']['enabled'] = true;
        }

        return $assigns;
    }

    /**
     * Builds the front-end login box
     *
     * @access  public
     * @param   string  $referrer   Referrer page url
     * @return  string  XHTML content
     */
    function IndexLogin($referrer)
    {
        $this->AjaxMe('index.js');
        $assigns = $this->loginInterface($referrer);

        return $this->gadget->template->xLoad('Login.html')->render($assigns);
    }

    /**
     * Builds the backend login box
     *
     * @access  public
     * @param   string  $referrer   Referrer page url
     * @return  string  XHTML content
     */
    function AdminLogin($referrer)
    {
        $this->AjaxMe('script.js');
        // Init layout
        $this->app->layout->Load('gadgets/Users/Templates/Admin', 'LoginLayout.html');
        $ltpl =& $this->app->layout->_Template;
        $ltpl->SetVariable('admin-script', BASE_SCRIPT);
        $ltpl->SetVariable('control-panel', Jaws::t('CONTROLPANEL'));

        $assigns = $this->loginInterface($referrer);
        $ltpl->SetVariable(
            'login-interface',
            $this->gadget->template->xloadAdmin('Login.html')->render($assigns)
        );

        return $this->app->layout->Get();
    }

}