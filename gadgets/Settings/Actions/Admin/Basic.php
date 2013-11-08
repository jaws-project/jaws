<?php
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
class Settings_Actions_Admin_Basic extends Settings_Actions_Admin_Default
{
    /**
     * Displays general/basic settings form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function BasicSettings()
    {
        $this->gadget->CheckPermission('BasicSettings');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Basic'));
        $tpl->SetVariable('legend', _t('SETTINGS_BASIC_SETTINGS'));

        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript: submitBasicForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // site status
        $site_status =& Piwi::CreateWidget('Combo', 'site_status');
        $site_status->setID('site_status');
        $tpl->SetBlock('settings/item');
        $site_status->AddOption(_t('GLOBAL_DISABLED'), 'disabled');
        $site_status->AddOption(_t('GLOBAL_ENABLED'), 'enabled');
        $site_status->SetDefault($this->gadget->registry->fetch('site_status'));
        $tpl->SetVariable('field-name', 'site_status');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_STATUS'));
        $tpl->SetVariable('field', $site_status->Get());
        $tpl->ParseBlock('settings/item');

        // Site name
        $tpl->SetBlock('settings/item');
        $sitename =& Piwi::CreateWidget('Entry',
            'site_name',
            Jaws_XSS::defilter($this->gadget->registry->fetch('site_name')));
        $sitename->setID('site_name');
        $tpl->SetVariable('field-name', 'site_name');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_NAME'));
        $tpl->SetVariable('field', $sitename->Get());
        $tpl->ParseBlock('settings/item');

        // Site slogan
        $tpl->SetBlock('settings/item');
        $sitedesc =& Piwi::CreateWidget('Entry',
            'site_slogan',
            Jaws_XSS::defilter($this->gadget->registry->fetch('site_slogan')));
        $sitedesc->setID('site_slogan');
        $tpl->SetVariable('field-name', 'site_slogan');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_SLOGAN'));
        $tpl->SetVariable('field', $sitedesc->Get());
        $tpl->ParseBlock('settings/item');

        // site language
        $lang =& Piwi::CreateWidget('Combo', 'site_language');
        $lang->setID('site_language');
        $tpl->SetBlock('settings/item');
        $languages = Jaws_Utils::GetLanguagesList();
        foreach ($languages as $k => $v) {
            $lang->AddOption($v, $k);
        }
        $lang->SetDefault($this->gadget->registry->fetch('site_language'));
        $tpl->SetVariable('field-name', 'site_language');
        $tpl->SetVariable('label', _t('SETTINGS_DEFAULT_SITE_LANGUAGE'));
        $tpl->SetVariable('field', $lang->Get());
        $tpl->ParseBlock('settings/item');

        // admin language
        $lang =& Piwi::CreateWidget('Combo', 'admin_language');
        $lang->setID('admin_language');
        $tpl->SetBlock('settings/item');
        foreach ($languages as $k => $v) {
            $lang->AddOption($v, $k);
        }
        $lang->SetDefault($this->gadget->registry->fetch('admin_language'));
        $tpl->SetVariable('field-name', 'admin_language');
        $tpl->SetVariable('label', _t('SETTINGS_ADMIN_LANGUAGE'));
        $tpl->SetVariable('field', $lang->Get());
        $tpl->ParseBlock('settings/item');

        // Main gadget
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $installedgadgets = $cmpModel->GetGadgetsList(null, true, true, null, true);
        $gdt =& Piwi::CreateWidget('Combo', 'main_gadget');
        $gdt->setID('main_gadget');

        $tpl->SetBlock('settings/item');
        $gdt->AddOption(_t('GLOBAL_NOGADGET'),'');
        foreach ($installedgadgets as $g => $tg) {
            $gdt->AddOption($tg['title'], $g);
        }
        $gdt->SetDefault($this->gadget->registry->fetch('main_gadget'));
        $tpl->SetVariable('field-name', 'main_gadget');
        $tpl->SetVariable('label', _t('SETTINGS_MAIN_GADGET'));
        $tpl->SetVariable('field', $gdt->Get());
        $tpl->ParseBlock('settings/item');

        // Site email
        $tpl->SetBlock('settings/item');
        $siteemail =& Piwi::CreateWidget('Entry',
            'site_email',
            $this->gadget->registry->fetch('site_email'));
        $siteemail->setID('site_email');
        $tpl->SetVariable('field-name', 'site_email');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_EMAIL'));
        $tpl->SetVariable('field', $siteemail->Get());
        $tpl->ParseBlock('settings/item');

        // Site comment
        $tpl->SetBlock('settings/item');
        $sitecomment =& Piwi::CreateWidget('TextArea',
            'site_comment',
            Jaws_XSS::defilter($this->gadget->registry->fetch('site_comment')));
        $sitecomment->SetRows(4);
        $sitecomment->setID('site_comment');
        $tpl->SetVariable('field-name', 'site_comment');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_COMMENT'));
        $tpl->SetVariable('field', $sitecomment->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

}