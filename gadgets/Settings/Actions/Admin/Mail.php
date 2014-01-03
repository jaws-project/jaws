<?php
/**
 * Settings Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Settings
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Actions_Admin_Mail extends Settings_Actions_Admin_Default
{
    /**
     * Displays general/mailserver settings form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function MailSettings()
    {
        $this->gadget->CheckPermission('MailSettings');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Mail'));
        $tpl->SetVariable('legend', _t('SETTINGS_MAIL_SETTINGS'));

        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript: submitMailSettingsForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // Mailer
        $mailer =& Piwi::CreateWidget('Combo', 'mailer');
        $mailer->setID('mailer');
        $mailer->AddOption(_t('GLOBAL_DISABLED'), 'DISABLED');
        $mailer->AddOption('PHP mail()', 'phpmail');
        $mailer->AddOption('sendmail',   'sendmail');
        $mailer->AddOption('SMTP',       'smtp');
        $mailer->AddEvent(ON_CHANGE, 'javascript: changeMailer();');
        $mailer->SetDefault($this->gadget->registry->fetch('mailer'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'mailer');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_MAILER'));
        $tpl->SetVariable('field', $mailer->Get());
        $tpl->SetVariable('style', 'padding-bottom: 8px;');
        $tpl->ParseBlock('settings/item');

        // Site email
        $tpl->SetBlock('settings/item');
        $siteEmail =& Piwi::CreateWidget('Entry', 'gate_email', $this->gadget->registry->fetch('gate_email'));
        $siteEmail->setID('gate_email');
        $tpl->SetVariable('field-name', 'gate_email');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_GATE_EMAIL'));
        $tpl->SetVariable('field', $siteEmail->Get());
        $tpl->SetVariable('style', 'padding-bottom: 0px;');
        $tpl->ParseBlock('settings/item');

        // Email title
        $tpl->SetBlock('settings/item');
        $emailName =& Piwi::CreateWidget('Entry', 'gate_title', $this->gadget->registry->fetch('gate_title'));
        $emailName->setID('gate_title');
        $tpl->SetVariable('field-name', 'gate_title');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_GATE_TITLE'));
        $tpl->SetVariable('field', $emailName->Get());
        $tpl->ParseBlock('settings/item');

        // SMTP Verification
        $smtpVrfy =& Piwi::CreateWidget('Combo', 'smtp_vrfy');
        $smtpVrfy->setID('smtp_vrfy');
        $smtpVrfy->AddOption(_t('GLOBAL_NO'),  'false');
        $smtpVrfy->AddOption(_t('GLOBAL_YES'), 'true');
        $smtpVrfy->SetDefault($this->gadget->registry->fetch('smtp_vrfy'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'smtp_vrfy');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_VRFY'));
        $tpl->SetVariable('field', $smtpVrfy->Get());
        $tpl->SetVariable('style', 'padding-bottom: 8px;');
        $tpl->ParseBlock('settings/item');

        // sendmail path
        $tpl->SetBlock('settings/item');
        $sendmailPath =& Piwi::CreateWidget('Entry', 'sendmail_path', $this->gadget->registry->fetch('sendmail_path'));
        $sendmailPath->setID('sendmail_path');
        $tpl->SetVariable('field-name', 'sendmail_path');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SENDMAIL_PATH'));
        $tpl->SetVariable('field', $sendmailPath->Get());
        $tpl->SetVariable('style', 'padding-bottom: 8px;');
        $tpl->ParseBlock('settings/item');

        // SMTP Host
        $tpl->SetBlock('settings/item');
        $smtpHost =& Piwi::CreateWidget('Entry', 'smtp_host', $this->gadget->registry->fetch('smtp_host'));
        $smtpHost->setID('smtp_host');
        $tpl->SetVariable('field-name', 'smtp_host');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_HOST'));
        $tpl->SetVariable('field', $smtpHost->Get());
        $tpl->SetVariable('style', 'padding-bottom: 0px;');
        $tpl->ParseBlock('settings/item');

        // SMTP Port
        $tpl->SetBlock('settings/item');
        $smtpPort =& Piwi::CreateWidget('Entry', 'smtp_port', $this->gadget->registry->fetch('smtp_port'));
        $smtpPort->setID('smtp_port');
        $smtpPort->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'smtp_port');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_PORT'));
        $tpl->SetVariable('field', $smtpPort->Get());
        $tpl->ParseBlock('settings/item');

        // SMTP Auth
        $smtpAuth =& Piwi::CreateWidget('Combo', 'smtp_auth');
        $smtpAuth->setID('smtp_auth');
        $smtpAuth->AddOption(_t('GLOBAL_NO'),  'false');
        $smtpAuth->AddOption(_t('GLOBAL_YES'), 'true');
        $smtpAuth->SetDefault($this->gadget->registry->fetch('smtp_auth'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'smtp_auth');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_AUTH'));
        $tpl->SetVariable('field', $smtpAuth->Get());
        $tpl->ParseBlock('settings/item');

        // SMTPAuth Username
        $tpl->SetBlock('settings/item');
        $smtpUser =& Piwi::CreateWidget('Entry', 'smtp_user', $this->gadget->registry->fetch('smtp_user'));
        $smtpUser->setID('smtp_user');
        $smtpUser->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'smtp_user');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_USER'));
        $tpl->SetVariable('field', $smtpUser->Get());
        $tpl->ParseBlock('settings/item');

        // SMTPAuth Password
        $tpl->SetBlock('settings/item');
        $smtpPass =& Piwi::CreateWidget('PasswordEntry', 'smtp_pass', '');
        $smtpPass->setID('smtp_pass');
        $smtpPass->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'smtp_pass');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_PASS'));
        $tpl->SetVariable('field', $smtpPass->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

}