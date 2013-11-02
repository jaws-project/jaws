<?php
require_once JAWS_PATH. 'gadgets/Settings/Actions/Admin/Default.php';
/**
 * Settings Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Settings
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
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

        $tpl = $this->gadget->loadAdminTemplate('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('FTP'));
        $tpl->SetVariable('legend', _t('SETTINGS_FTP_SETTINGS'));

        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript: submitFTPSettingsForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // Use Enabled?
        $useFTP =& Piwi::CreateWidget('Combo', 'ftp_enabled');
        $useFTP->setID('ftp_enabled');
        $useFTP->AddOption(_t('GLOBAL_NO'),  'false');
        $useFTP->AddOption(_t('GLOBAL_YES'), 'true');
        $useFTP->SetDefault($this->gadget->registry->fetch('ftp_enabled'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'ftp_enabled');
        $tpl->SetVariable('label', _t('GLOBAL_ENABLED'));
        $tpl->SetVariable('field', $useFTP->Get());
        $tpl->SetVariable('style', 'padding-bottom:8px;');
        $tpl->ParseBlock('settings/item');

        // FTP Host
        $tpl->SetBlock('settings/item');
        $ftpHost =& Piwi::CreateWidget('Entry', 'ftp_host', $this->gadget->registry->fetch('ftp_host'));
        $ftpHost->setID('ftp_host');
        $ftpHost->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'ftp_host');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_HOST'));
        $tpl->SetVariable('field', $ftpHost->Get());
        $tpl->SetVariable('style', 'padding-bottom: 0px;');
        $tpl->ParseBlock('settings/item');

        // FTP Port
        $tpl->SetBlock('settings/item');
        $ftpPort =& Piwi::CreateWidget('Entry', 'ftp_port', $this->gadget->registry->fetch('ftp_port'));
        $ftpPort->setID('ftp_port');
        $ftpPort->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'ftp_port');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_PORT'));
        $tpl->SetVariable('field', $ftpPort->Get());
        $tpl->ParseBlock('settings/item');

        // FTP mode (active/passive)
        $ftpMode =& Piwi::CreateWidget('Combo', 'ftp_mode');
        $ftpMode->setID('ftp_mode');
        $ftpMode->AddOption(_t('SETTINGS_FTP_MODE_ACTIVE'),  'active');
        $ftpMode->AddOption(_t('SETTINGS_FTP_MODE_PASSIVE'), 'passive');
        $ftpMode->SetDefault($this->gadget->registry->fetch('ftp_mode'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'ftp_mode');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_MODE'));
        $tpl->SetVariable('field', $ftpMode->Get());
        $tpl->ParseBlock('settings/item');

        // FTP Username
        $tpl->SetBlock('settings/item');
        $ftpUser =& Piwi::CreateWidget('Entry', 'ftp_user', $this->gadget->registry->fetch('ftp_user'));
        $ftpUser->setID('ftp_user');
        $tpl->SetVariable('field-name', 'ftp_user');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_USER'));
        $tpl->SetVariable('field', $ftpUser->Get());
        $tpl->ParseBlock('settings/item');

        // FTP Password
        $tpl->SetBlock('settings/item');
        $ftpPass =& Piwi::CreateWidget('PasswordEntry', 'ftp_pass', '');
        $ftpPass->setID('ftp_pass');
        $tpl->SetVariable('field-name', 'ftp_pass');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_PASS'));
        $tpl->SetVariable('field', $ftpPass->Get());
        $tpl->ParseBlock('settings/item');

        // FTP Root Path
        $tpl->SetBlock('settings/item');
        $ftpRoot =& Piwi::CreateWidget('Entry', 'ftp_root', $this->gadget->registry->fetch('ftp_root'));
        $ftpRoot->setID('ftp_root');
        $ftpRoot->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'ftp_root');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_ROOT'));
        $tpl->SetVariable('field', $ftpRoot->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

}