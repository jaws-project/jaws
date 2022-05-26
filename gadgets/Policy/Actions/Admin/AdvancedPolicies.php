<?php
/**
 * Policy Admin Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Actions_Admin_AdvancedPolicies extends Policy_Actions_Admin_Default
{
    /**
     * AdvancedPolicies action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function AdvancedPolicies()
    {
        $this->gadget->CheckPermission('AntiSpam');
        $this->AjaxMe('script.js');

        $model = $this->gadget->model->loadAdmin('AntiSpam');
        $tpl = $this->gadget->template->loadAdmin('AdvancedPolicies.html');
        $tpl->SetBlock('AdvancedPolicies');

        // Sidebar
        $tpl->SetVariable('sidebar', $this->SideBar('AdvancedPolicies'));
        $tpl->SetVariable('legend_title', $this::t('ADVANCED_POLICIES'));

        // password complexity
        $default_regexs = array(
            '/^[[:print:]]{1,24}$/',
            '/(?=.*[[:lower:]|[:upper:]])(?=.*[[:digit:]])/',
            '/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])/',
            '/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])(?=.*[[:punct:]])/',
        );
        $complexity =& Piwi::CreateWidget('Combo', 'password_complexity');
        foreach ($default_regexs as $key => $value) {
            $complexity->AddOption($this::t("PASSWORD_COMPLEXITY_{$key}"), $value);
        }
        $db_regex = $this->gadget->registry->fetch('password_complexity');
        if (!in_array($db_regex, $default_regexs)) {
            $complexity->AddOption($this::t('PASSWORD_COMPLEXITY_4'), $db_regex);
        }
        $complexity->SetDefault($db_regex);
        $tpl->SetVariable('lbl_password_complexity', $this::t('PASSWORD_COMPLEXITY'));
        $tpl->SetVariable('password_complexity', $complexity->Get());

        $badCount =& Piwi::CreateWidget('Combo', 'password_bad_count');
        $badCount->setID('password_bad_count');
        $badCount->AddOption(Jaws::t('TIMES', 1), '1');
        $badCount->AddOption(Jaws::t('TIMES', 3), '3');
        $badCount->AddOption(Jaws::t('TIMES', 5), '5');
        $badCount->AddOption(Jaws::t('TIMES', 7), '7');
        $badCount->SetDefault($this->gadget->registry->fetch('password_bad_count'));
        $tpl->SetVariable('lbl_password_bad_count', $this::t('PASSWORD_BAD_COUNT'));
        $tpl->SetVariable('password_bad_count', $badCount->Get());

        $lockedout =& Piwi::CreateWidget('Combo', 'password_lockedout_time');
        $lockedout->setID('password_lockedout_time');
        $lockedout->AddOption(Jaws::t('DISABLED'), '0');
        $lockedout->AddOption(Jaws::t('DATE_MINUTES',  1),  60);
        $lockedout->AddOption(Jaws::t('DATE_MINUTES',  3), 180);
        $lockedout->AddOption(Jaws::t('DATE_MINUTES',  5), 300);
        $lockedout->AddOption(Jaws::t('DATE_MINUTES', 10), 600);
        $lockedout->AddOption(Jaws::t('DATE_MINUTES', 15), 900);
        $lockedout->SetDefault($this->gadget->registry->fetch('password_lockedout_time'));
        $tpl->SetVariable('lbl_password_lockedout_time', $this::t('PASSWORD_LOCKEDOUT_TIME'));
        $tpl->SetVariable('password_lockedout_time', $lockedout->Get());

        $maxAge =& Piwi::CreateWidget('Combo', 'password_max_age');
        $maxAge->setID('password_max_age');
        $maxAge->AddOption($this::t('PASSWORD_RESISTANT'), 0);
        $maxAge->AddOption(Jaws::t('DATE_DAYS',  1), 24);
        $maxAge->AddOption(Jaws::t('DATE_DAYS',  3), 3*24);
        $maxAge->AddOption(Jaws::t('DATE_WEEKS', 1), 7*24);
        $maxAge->AddOption(Jaws::t('DATE_WEEKS', 2), 14*24);
        $maxAge->AddOption(Jaws::t('DATE_MONTH', 1), 30*24);
        $maxAge->AddOption(Jaws::t('DATE_MONTH', 3), 90*24);
        $maxAge->AddOption(Jaws::t('DATE_MONTH', 6), 180*24);
        $maxAge->AddOption(Jaws::t('DATE_MONTH', 12), 365*24);
        $maxAge->SetDefault($this->gadget->registry->fetch('password_max_age'));
        $tpl->SetVariable('lbl_password_max_age', $this::t('PASSWORD_MAX_AGE'));
        $tpl->SetVariable('password_max_age', $maxAge->Get());

        $minLen =& Piwi::CreateWidget('Combo', 'password_min_length');
        $minLen->setID('password_min_length');
        $minLen->AddOption('0',   0);
        $minLen->AddOption('3',   3);
        $minLen->AddOption('6',   6);
        $minLen->AddOption('8',   8);
        $minLen->AddOption('10', 10);
        $minLen->AddOption('15', 15);
        $minLen->SetDefault($this->gadget->registry->fetch('password_min_length'));
        $tpl->SetVariable('lbl_password_min_length', $this::t('PASSWORD_MIN_LEN'));
        $tpl->SetVariable('password_min_length', $minLen->Get());

        //Login captcha
        $captcha =& Piwi::CreateWidget('Combo', 'login_captcha');
        $captcha->AddOption(Jaws::t('DISABLED'), 'DISABLED');
        $captcha->AddOption(Jaws::t('ALWAYS'), '0');
        $captcha->AddOption($this::t('LOGIN_CAPTCHA_AFTER_WRONG', 1), '1');
        $captcha->AddOption($this::t('LOGIN_CAPTCHA_AFTER_WRONG', 2), '2');
        $captcha->AddOption($this::t('LOGIN_CAPTCHA_AFTER_WRONG', 3), '3');
        $captchaValue = $this->gadget->registry->fetch('login_captcha_status');
        $captcha->SetDefault($captchaValue);
        $captcha->AddEvent(ON_CHANGE, "javascript:toggleCaptcha('login');");
        $tpl->SetVariable('lbl_login_captcha', $this::t('LOGIN_CAPTCHA'));
        $tpl->SetVariable('login_captcha', $captcha->Get());

        //Login captcha driver
        $captchaDriver =& Piwi::CreateWidget('Combo', 'login_captcha_driver');
        $dCaptchas = $model->GetCaptchas();
        foreach ($dCaptchas as $dCaptcha) {
            $captchaDriver->AddOption($dCaptcha, $dCaptcha);
        }
        $captchaDriver->SetDefault($this->gadget->registry->fetch('login_captcha_driver'));
        if ($captchaValue === 'DISABLED') {
            $captchaDriver->SetEnabled(false);
        }
        $tpl->SetVariable('login_captcha_driver', $captchaDriver->Get());

        $parsingLevel =& Piwi::CreateWidget('Combo', 'xss_parsing_level');
        $parsingLevel->AddOption($this::t('XSS_PARSING_NORMAL'),   'normal');
        $parsingLevel->AddOption($this::t('XSS_PARSING_PARANOID'), 'paranoid');
        $parsingLevel->SetDefault($this->gadget->registry->fetch('xss_parsing_level'));
        $tpl->SetVariable('lbl_xss_parsing_level', $this::t('XSS_PARSING_LEVEL'));
        $tpl->SetVariable('xss_parsing_level', $parsingLevel->Get());

        $onlineTimeout =& Piwi::CreateWidget('Combo', 'session_online_timeout');
        $onlineTimeout->setID('session_online_timeout');
        $onlineTimeout->AddOption(Jaws::t('DATE_MINUTES',  5),  5);
        $onlineTimeout->AddOption(Jaws::t('DATE_MINUTES', 10), 10);
        $onlineTimeout->AddOption(Jaws::t('DATE_MINUTES', 15), 15);
        $onlineTimeout->AddOption(Jaws::t('DATE_MINUTES', 30), 30);
        $onlineTimeout->AddOption(Jaws::t('DATE_HOURS',    1), 60);
        $onlineTimeout->AddOption(Jaws::t('DATE_HOURS',    6), 360);
        $onlineTimeout->AddOption(Jaws::t('DATE_DAYS',     1), 1440);
        $onlineTimeout->AddOption(Jaws::t('DATE_WEEKS',    1), 10080);
        $onlineTimeout->SetDefault((int)$this->gadget->registry->fetch('session_online_timeout'));
        $tpl->SetVariable('lbl_session_online_timeout', $this::t('SESSION_ONLINE_TIMEOUT'));
        $tpl->SetVariable('session_online_timeout', $onlineTimeout->Get());

        $anonyRememberTimeout =& Piwi::CreateWidget('Combo', 'session_anony_remember_timeout');
        $anonyRememberTimeout->setID('session_anony_remember_timeout');
        $anonyRememberTimeout->AddOption(Jaws::t('DATE_MINUTES',  5),  5);
        $anonyRememberTimeout->AddOption(Jaws::t('DATE_MINUTES', 10), 10);
        $anonyRememberTimeout->AddOption(Jaws::t('DATE_MINUTES', 15), 15);
        $anonyRememberTimeout->AddOption(Jaws::t('DATE_MINUTES', 30), 30);
        $anonyRememberTimeout->AddOption(Jaws::t('DATE_HOURS',    1), 60);
        $anonyRememberTimeout->AddOption(Jaws::t('DATE_HOURS',    6), 360);
        $anonyRememberTimeout->AddOption(Jaws::t('DATE_DAYS',     1), 1440);
        $anonyRememberTimeout->AddOption(Jaws::t('DATE_WEEKS',    1), 10080);
        $anonyRememberTimeout->AddOption(Jaws::t('DATE_MONTH',    1), 43200);
        $anonyRememberTimeout->SetDefault($this->gadget->registry->fetch('session_anony_remember_timeout'));
        $tpl->SetVariable('lbl_session_anony_remember_timeout', $this::t('SESSION_ANONY_REMEMBER_TIMEOUT'));
        $tpl->SetVariable('session_anony_remember_timeout', $anonyRememberTimeout->Get());

        $loginRememberTimeout =& Piwi::CreateWidget('Combo', 'session_login_remember_timeout');
        $loginRememberTimeout->setID('session_login_remember_timeout');
        $loginRememberTimeout->AddOption(Jaws::t('DATE_HOURS',  1), 60);
        $loginRememberTimeout->AddOption(Jaws::t('DATE_HOURS',  6), 360);
        $loginRememberTimeout->AddOption(Jaws::t('DATE_DAYS',   1), 1440);
        $loginRememberTimeout->AddOption(Jaws::t('DATE_WEEKS',  1), 10080);
        $loginRememberTimeout->AddOption(Jaws::t('DATE_MONTH',  1), 43200);
        $loginRememberTimeout->AddOption(Jaws::t('DATE_MONTH',  6), 259200);
        $loginRememberTimeout->AddOption(Jaws::t('DATE_MONTH', 12), 518400);
        $loginRememberTimeout->SetDefault($this->gadget->registry->fetch('session_login_remember_timeout'));
        $tpl->SetVariable('lbl_session_login_remember_timeout', $this::t('SESSION_LOGIN_REMEMBER_TIMEOUT'));
        $tpl->SetVariable('session_login_remember_timeout', $loginRememberTimeout->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:Jaws_Gadget.getInstance(\'Policy\').saveAdvancedPolicies();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->ParseBlock('AdvancedPolicies');
        return $tpl->Get();
    }
}