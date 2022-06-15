<?php
/**
 * Policy Admin Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Actions_Admin_AntiSpam extends Policy_Actions_Admin_Default
{

    /**
     * AntiSpam action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function AntiSpam()
    {
        $this->gadget->CheckPermission('AntiSpam');
        $this->AjaxMe('script.js');

        $model = $this->gadget->model->loadAdmin('AntiSpam');
        $tpl = $this->gadget->template->loadAdmin('AntiSpam.html');
        $tpl->SetBlock('AntiSpam');

        // Sidebar
        $tpl->SetVariable('sidebar', $this->SideBar('AntiSpam'));
        $tpl->SetVariable('legend_title', $this::t('ANTISPAM'));

        //Filter
        $filters =& Piwi::CreateWidget('Combo', 'filter');
        $filters->AddOption(Jaws::t('DISABLED'), 'DISABLED');
        $fs = $model->GetFilters();
        foreach ($fs as $f) {
            $filters->AddOption($f, $f);
        }
        $filters->SetDefault($this->gadget->registry->fetch('filter'));
        $tpl->SetVariable('lbl_filter', $this::t('ANTISPAM_FILTER'));
        $tpl->SetVariable('filter', $filters->Get());

        //Captcha
        $captcha =& Piwi::CreateWidget('Combo', 'default_captcha');
        $captcha->AddOption(Jaws::t('DISABLED'), 'DISABLED');
        $captcha->AddOption($this::t('ANTISPAM_CAPTCHA_ALWAYS'), 'ALWAYS');
        $captcha->AddOption($this::t('ANTISPAM_CAPTCHA_ANONYMOUS'), 'ANONYMOUS');
        $captchaValue = $this->gadget->registry->fetch('default_captcha_status');
        $captcha->SetDefault($captchaValue);
        $captcha->AddEvent(ON_CHANGE, "javascript:toggleCaptcha('default');");
        $tpl->SetVariable('lbl_default_captcha', $this::t('ANTISPAM_CAPTCHA'));
        $tpl->SetVariable('default_captcha', $captcha->Get());

        //Captcha driver
        $captchaDriver =& Piwi::CreateWidget('Combo', 'default_captcha_driver');
        $dCaptchas = $model->GetCaptchas();
        foreach ($dCaptchas as $dCaptcha) {
            $captchaDriver->AddOption($dCaptcha, $dCaptcha);
        }
        $captchaDriver->SetDefault($this->gadget->registry->fetch('default_captcha_driver'));
        if ($captchaValue === 'DISABLED') {
            $captchaDriver->SetEnabled(false);
        }
        $tpl->SetVariable('default_captcha_driver', $captchaDriver->Get());

        //Email Protector
        $useEmailProtector =& Piwi::CreateWidget('Combo', 'obfuscator');
        $useEmailProtector->AddOption(Jaws::t('DISABLED'), 'DISABLED');
        $os = $model->GetObfuscators();
        foreach ($os as $o) {
            $useEmailProtector->AddOption($o, $o);
        }
        $useEmailProtector->SetDefault($this->gadget->registry->fetch('obfuscator'));
        $tpl->SetVariable('lbl_obfuscator', $this::t('ANTISPAM_PROTECTEMAIL'));
        $tpl->SetVariable('obfuscator', $useEmailProtector->Get());

        // Blocked domains
        $blockedDomains =& Piwi::CreateWidget(
            'TextArea',
            'blocked_domains',
            trim($this->gadget->registry->fetch('blocked_domains'))
        );
        $blockedDomains->SetRows(10);
        $blockedDomains->setID('blocked_domains');
        $tpl->SetVariable('lbl_blocked_domains', $this::t('ANTISPAM_BLOCKED_DOMAINS'));
        $tpl->SetVariable('blocked_domains', $blockedDomains->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:Jaws_Gadget.getInstance(\'Policy\').saveAntiSpamSettings();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->ParseBlock('AntiSpam');
        return $tpl->Get();
    }
}