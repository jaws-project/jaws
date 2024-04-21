<?php
/**
 * Settings Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Settings
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Actions_Admin_FTP extends Settings_Actions_Admin_Default
{
    /**
     * Display general/ftpserver settings form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function FTPSettings()
    {
        $this->gadget->CheckPermission('FTPSettings');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('FTP'));
        $tpl->SetVariable('legend', $this::t('FTP_SETTINGS'));

        $saveButton =& Piwi::CreateWidget('Button', 'save', Jaws::t('SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Settings').submitFTPSettingsForm();");
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // Use Enabled?
        $useFTP =& Piwi::CreateWidget('Combo', 'ftp_enabled');
        $useFTP->setID('ftp_enabled');
        $useFTP->AddOption(Jaws::t('NOO'),  'false');
        $useFTP->AddOption(Jaws::t('YESS'), 'true');
        $useFTP->SetDefault($this->gadget->registry->fetch('ftp_enabled'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'ftp_enabled');
        $tpl->SetVariable('label', Jaws::t('ENABLED'));
        $tpl->SetVariable('field', $useFTP->Get());
        $tpl->SetVariable('style', 'padding-bottom:8px;');
        $tpl->ParseBlock('settings/item');

        // FTP Host
        $tpl->SetBlock('settings/item');
        $ftpHost =& Piwi::CreateWidget('Entry', 'ftp_host', $this->gadget->registry->fetch('ftp_host'));
        $ftpHost->setID('ftp_host');
        $ftpHost->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'ftp_host');
        $tpl->SetVariable('label', $this::t('FTP_HOST'));
        $tpl->SetVariable('field', $ftpHost->Get());
        $tpl->SetVariable('style', 'padding-bottom: 0px;');
        $tpl->ParseBlock('settings/item');

        // FTP Port
        $tpl->SetBlock('settings/item');
        $ftpPort =& Piwi::CreateWidget('Entry', 'ftp_port', $this->gadget->registry->fetch('ftp_port'));
        $ftpPort->setID('ftp_port');
        $ftpPort->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'ftp_port');
        $tpl->SetVariable('label', $this::t('FTP_PORT'));
        $tpl->SetVariable('field', $ftpPort->Get());
        $tpl->ParseBlock('settings/item');

        // FTP mode (active/passive)
        $ftpMode =& Piwi::CreateWidget('Combo', 'ftp_mode');
        $ftpMode->setID('ftp_mode');
        $ftpMode->AddOption($this::t('FTP_MODE_ACTIVE'),  'active');
        $ftpMode->AddOption($this::t('FTP_MODE_PASSIVE'), 'passive');
        $ftpMode->SetDefault($this->gadget->registry->fetch('ftp_mode'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'ftp_mode');
        $tpl->SetVariable('label', $this::t('FTP_MODE'));
        $tpl->SetVariable('field', $ftpMode->Get());
        $tpl->ParseBlock('settings/item');

        // FTP Username
        $tpl->SetBlock('settings/item');
        $ftpUser =& Piwi::CreateWidget('Entry', 'ftp_user', $this->gadget->registry->fetch('ftp_user'));
        $ftpUser->setID('ftp_user');
        $tpl->SetVariable('field-name', 'ftp_user');
        $tpl->SetVariable('label', $this::t('FTP_USER'));
        $tpl->SetVariable('field', $ftpUser->Get());
        $tpl->ParseBlock('settings/item');

        // FTP Password
        $tpl->SetBlock('settings/item');
        $ftpPass =& Piwi::CreateWidget('PasswordEntry', 'ftp_pass', '');
        $ftpPass->setID('ftp_pass');
        $tpl->SetVariable('field-name', 'ftp_pass');
        $tpl->SetVariable('label', $this::t('FTP_PASS'));
        $tpl->SetVariable('field', $ftpPass->Get());
        $tpl->ParseBlock('settings/item');

        // FTP Root Path
        $tpl->SetBlock('settings/item');
        $ftpRoot =& Piwi::CreateWidget('Entry', 'ftp_root', $this->gadget->registry->fetch('ftp_root'));
        $ftpRoot->setID('ftp_root');
        $ftpRoot->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'ftp_root');
        $tpl->SetVariable('label', $this::t('FTP_ROOT'));
        $tpl->SetVariable('field', $ftpRoot->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

}