<?php
/**
 * Policy Admin Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2020 Jaws Development Group
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
        $tpl->SetVariable('legend_title', _t('POLICY_ADVANCED_POLICIES'));

        // password complexity
        $default_regexs = array(
            '/^[[:print:]]{1,24}$/',
            '/(?=.*[[:lower:]|[:upper:]])(?=.*[[:digit:]])/',
            '/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])/',
            '/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])(?=.*[[:punct:]])/',
        );
        $complexity =& Piwi::CreateWidget('Combo', 'password_complexity');
        foreach ($default_regexs as $key => $value) {
            $complexity->AddOption(_t("POLICY_PASSWORD_COMPLEXITY_{$key}"), $value);
        }
        $db_regex = $this->gadget->registry->fetch('password_complexity');
        if (!in_array($db_regex, $default_regexs)) {
            $complexity->AddOption(_t('POLICY_PASSWORD_COMPLEXITY_4'), $db_regex);
        }
        $complexity->SetDefault($db_regex);
        $tpl->SetVariable('lbl_password_complexity', _t('POLICY_PASSWORD_COMPLEXITY'));
        $tpl->SetVariable('password_complexity', $complexity->Get());

        $badCount =& Piwi::CreateWidget('Combo', 'password_bad_count');
        $badCount->setID('password_bad_count');
        $badCount->AddOption(Jaws::t('TIMES', 1), '1');
        $badCount->AddOption(Jaws::t('TIMES', 3), '3');
        $badCount->AddOption(Jaws::t('TIMES', 5), '5');
        $badCount->AddOption(Jaws::t('TIMES', 7), '7');
        $badCount->SetDefault($this->gadget->registry->fetch('password_bad_count'));
        $tpl->SetVariable('lbl_password_bad_count', _t('POLICY_PASSWORD_BAD_COUNT'));
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
        $tpl->SetVariable('lbl_password_lockedout_time', _t('POLICY_PASSWORD_LOCKEDOUT_TIME'));
        $tpl->SetVariable('password_lockedout_time', $lockedout->Get());

        $maxAge =& Piwi::CreateWidget('Combo', 'password_max_age');
        $maxAge->setID('password_max_age');
        $maxAge->AddOption(_t('POLICY_PASSWORD_RESISTANT'), 0);
        $maxAge->AddOption(Jaws::t('DATE_DAYS',  1), 24);
        $maxAge->AddOption(Jaws::t('DATE_DAYS',  3), 3*24);
        $maxAge->AddOption(Jaws::t('DATE_WEEKS', 1), 7*24);
        $maxAge->AddOption(Jaws::t('DATE_WEEKS', 2), 14*24);
        $maxAge->AddOption(Jaws::t('DATE_MONTH', 1), 30*24);
        $maxAge->AddOption(Jaws::t('DATE_MONTH', 3), 90*24);
        $maxAge->AddOption(Jaws::t('DATE_MONTH', 6), 180*24);
        $maxAge->AddOption(Jaws::t('DATE_MONTH', 12), 365*24);
        $maxAge->SetDefault($this->gadget->registry->fetch('password_max_age'));
        $tpl->SetVariable('lbl_password_max_age', _t('POLICY_PASSWORD_MAX_AGE'));
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
        $tpl->SetVariable('lbl_password_min_length', _t('POLICY_PASSWORD_MIN_LEN'));
        $tpl->SetVariable('password_min_length', $minLen->Get());

        //Login captcha
        $captcha =& Piwi::CreateWidget('Combo', 'login_captcha');
        $captcha->AddOption(Jaws::t('DISABLED'), 'DISABLED');
        $captcha->AddOption(Jaws::t('ALWAYS'), '0');
        $captcha->AddOption(_t('POLICY_LOGIN_CAPTCHA_AFTER_WRONG', 1), '1');
        $captcha->AddOption(_t('POLICY_LOGIN_CAPTCHA_AFTER_WRONG', 2), '2');
        $captcha->AddOption(_t('POLICY_LOGIN_CAPTCHA_AFTER_WRONG', 3), '3');
        $captchaValue = $this->gadget->registry->fetch('login_captcha_status');
        $captcha->SetDefault($captchaValue);
        $captcha->AddEvent(ON_CHANGE, "javascript:toggleCaptcha('login');");
        $tpl->SetVariable('lbl_login_captcha', _t('POLICY_LOGIN_CAPTCHA'));
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
        $parsingLevel->AddOption(_t('POLICY_XSS_PARSING_NORMAL'),   'normal');
        $parsingLevel->AddOption(_t('POLICY_XSS_PARSING_PARANOID'), 'paranoid');
        $parsingLevel->SetDefault($this->gadget->registry->fetch('xss_parsing_level'));
        $tpl->SetVariable('lbl_xss_parsing_level', _t('POLICY_XSS_PARSING_LEVEL'));
        $tpl->SetVariable('xss_parsing_level', $parsingLevel->Get());

        $idleTimeout =& Piwi::CreateWidget('Combo', 'session_idle_timeout');
        $idleTimeout->setID('session_idle_timeout');
        $idleTimeout->AddOption(Jaws::t('DATE_MINUTES',  5),  5);
        $idleTimeout->AddOption(Jaws::t('DATE_MINUTES', 10), 10);
        $idleTimeout->AddOption(Jaws::t('DATE_MINUTES', 15), 15);
        $idleTimeout->AddOption(Jaws::t('DATE_MINUTES', 30), 30);
        $idleTimeout->AddOption(Jaws::t('DATE_HOURS',    1), 60);
        $idleTimeout->AddOption(Jaws::t('DATE_HOURS',    6), 360);
        $idleTimeout->AddOption(Jaws::t('DATE_DAYS',     1), 1440);
        $idleTimeout->AddOption(Jaws::t('DATE_WEEKS',    1), 10080);
        $idleTimeout->SetDefault($this->gadget->registry->fetch('session_idle_timeout'));
        $tpl->SetVariable('lbl_session_idle_timeout', _t('POLICY_SESSION_IDLE_TIMEOUT'));
        $tpl->SetVariable('session_idle_timeout', $idleTimeout->Get());

        $rememberTimeout =& Piwi::CreateWidget('Combo', 'session_remember_timeout');
        $rememberTimeout->setID('session_remember_timeout');
        $rememberTimeout->AddOption(Jaws::t('DATE_DAYS',   1),   24);
        $rememberTimeout->AddOption(Jaws::t('DATE_DAYS',   3),   72);
        $rememberTimeout->AddOption(Jaws::t('DATE_WEEKS',  1),  168);
        $rememberTimeout->AddOption(Jaws::t('DATE_WEEKS',  2),  336);
        $rememberTimeout->AddOption(Jaws::t('DATE_MONTH',  1),  720);
        $rememberTimeout->AddOption(Jaws::t('DATE_MONTH',  6), 4320);
        $rememberTimeout->AddOption(Jaws::t('DATE_MONTH', 12), 8640);
        $rememberTimeout->SetDefault($this->gadget->registry->fetch('session_remember_timeout'));
        $tpl->SetVariable('lbl_session_remember_timeout', _t('POLICY_SESSION_REMEMBER_TIMEOUT'));
        $tpl->SetVariable('session_remember_timeout', $rememberTimeout->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:saveAdvancedPolicies();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->ParseBlock('AdvancedPolicies');
        return $tpl->Get();
    }
}