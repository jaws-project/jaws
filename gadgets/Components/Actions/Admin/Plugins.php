<?php
/**
 * Components Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Components_Actions_Admin_Plugins extends Components_Actions_Admin_Default
{
    /**
     * Builds plugins management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Plugins()
    {
        $this->gadget->CheckPermission('ManagePlugins');
        $this->AjaxMe('script.js');

        $this->gadget->export('lbl_uninstall', $this::t('UNINSTALL'));
        $this->gadget->export('lbl_install', $this::t('INSTALL'));
        $this->gadget->export('confirmUninstallPlugin', $this::t('PLUGINS_CONFIRM_UNINSTALL'));

        $tpl = $this->gadget->template->loadAdmin('Plugins.html');
        $tpl->SetBlock('components');

        $tpl->SetVariable('menubar', $this->Menubar('Plugins'));
        $tpl->SetVariable('summary', $this->PluginsSummary());

        $tpl->SetVariable('lbl_installed', $this::t('PLUGINS_INSTALLED'));
        $tpl->SetVariable('installed_desc', $this::t('PLUGINS_INSTALLED_DESC'));
        $tpl->SetVariable('lbl_notinstalled', $this::t('PLUGINS_NOTINSTALLED'));
        $tpl->SetVariable('notinstalled_desc', $this::t('PLUGINS_NOTINSTALLED_DESC'));
        $tpl->SetVariable('lbl_info', $this::t('INFO'));
        $tpl->SetVariable('lbl_usage', $this::t('PLUGINS_USAGE'));
        $tpl->SetVariable('lbl_registry', $this::t('REGISTRY'));
        $tpl->SetVariable('lbl_acl', $this::t('ACL'));

        $button =& Piwi::CreateWidget('Button', 'btn_close', 'X ');
        $button->AddEvent(ON_CLICK, 'javascript:closeUI();');
        $tpl->SetVariable('close', $button->Get());

        $tpl->ParseBlock('components');
        return $tpl->Get();
    }

    /**
     * Builds plugins summary UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function PluginsSummary()
    {
        $tpl = $this->gadget->template->loadAdmin('PluginsSummary.html');
        $tpl->SetBlock('summary');
        $tpl->SetVariable('lbl_summary', $this::t('SUMMARY'));
        $tpl->SetVariable('lbl_installed', $this::t('PLUGINS_INSTALLED').':');
        $tpl->SetVariable('lbl_notinstalled', $this::t('PLUGINS_NOTINSTALLED').':');
        $tpl->SetVariable('lbl_total', $this::t('PLUGINS_TOTAL').':');
        $tpl->ParseBlock('summary');
        return $tpl->Get();
    }

    /**
     * Builds UI for the plugin information
     *
     * @access  public
     * @param   string   $plugin  Plugin's name
     * @return  string   XHTML UI
     */
    function PluginInfo($plugin)
    {
        $objPlugin = Jaws_Plugin::getInstance($plugin, false);
        if (Jaws_Error::IsError($objPlugin)) {
            return $objPlugin->getMessage();
        }

        $tpl = $this->gadget->template->loadAdmin('Plugin.html');
        $tpl->SetBlock('info');

        $tpl->SetVariable('lbl_version',   $this::t('VERSION').':');
        $tpl->SetVariable('lbl_example',   $this::t('PLUGINS_USAGE').':');
        $tpl->SetVariable('lbl_accesskey', $this::t('PLUGINS_ACCESSKEY').':');
        $tpl->SetVariable('lbl_friendly',  $this::t('PLUGINS_FRIENDLY').':');
        $tpl->SetVariable(
            'accesskey',
            method_exists($objPlugin, 'GetAccessKey')? $objPlugin->GetAccessKey() : $this::t('PLUGINS_NO_ACCESSKEY')
        );
        $tpl->SetVariable(
            'friendly',
            $objPlugin->friendly? $this::t('PLUGINS_FRIENDLY') : $this::t('PLUGINS_NOT_FRIENDLY')
        );
        $tpl->SetVariable(
            'example',
            $objPlugin::t('EXAMPLE')
        );
        $tpl->SetVariable('version', $objPlugin->version);

        $button =& Piwi::CreateWidget('Button', 'btn_install', $this::t('INSTALL'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('install', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_uninstall', $this::t('UNINSTALL'), STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('uninstall', $button->Get());

        $tpl->ParseBlock('info');
        return $tpl->Get();
    }

    /**
     * Builds plugin usage UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function PluginUsage()
    {
        $tpl = $this->gadget->template->loadAdmin('PluginUsage.html');
        $tpl->SetBlock('usage');

        $tpl->SetVariable('decription', $this::t('PLUGINS_USAGE_DESC'));
        $tpl->SetVariable('lbl_gadget', $this::t('PLUGINS_USAGE_GADGET'));
        $tpl->SetVariable('lbl_backend', $this::t('PLUGINS_USAGE_BACKEND'));
        $tpl->SetVariable('lbl_frontend', $this::t('PLUGINS_USAGE_FRONTEND'));

        $check =& Piwi::CreateWidget('CheckButtons', 'all');
        $check->AddOption('', 'backend');
        $check->AddEvent(ON_CLICK, 'usageCheckAll(this)');
        $tpl->SetVariable('all_backend', $check->Get());

        $check =& Piwi::CreateWidget('CheckButtons', 'all');
        $check->AddOption('', 'frontend');
        $check->AddEvent(ON_CLICK, 'usageCheckAll(this)');
        $tpl->SetVariable('all_frontend', $check->Get());

        $button =& Piwi::CreateWidget('Button', '', Jaws::t('SAVE'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:savePluginUsage();');
        $tpl->SetVariable('save', $button->Get());

        $button =& Piwi::CreateWidget('Button', '', Jaws::t('RESET'), STOCK_REFRESH);
        $button->AddEvent(ON_CLICK, 'pluginUsage(true);');
        $tpl->SetVariable('reset', $button->Get());

        $tpl->ParseBlock('usage');
        return $tpl->Get();
    }

}