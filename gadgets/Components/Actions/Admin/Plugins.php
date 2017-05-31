<?php
/**
 * Components Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
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

        $this->gadget->define('lbl_uninstall', _t('COMPONENTS_UNINSTALL'));
        $this->gadget->define('lbl_install', _t('COMPONENTS_INSTALL'));
        $this->gadget->define('confirmUninstallPlugin', _t('COMPONENTS_PLUGINS_CONFIRM_UNINSTALL'));

        $tpl = $this->gadget->template->loadAdmin('Plugins.html');
        $tpl->SetBlock('components');

        $tpl->SetVariable('menubar', $this->Menubar('Plugins'));
        $tpl->SetVariable('summary', $this->PluginsSummary());

        $tpl->SetVariable('lbl_installed', _t('COMPONENTS_PLUGINS_INSTALLED'));
        $tpl->SetVariable('installed_desc', _t('COMPONENTS_PLUGINS_INSTALLED_DESC'));
        $tpl->SetVariable('lbl_notinstalled', _t('COMPONENTS_PLUGINS_NOTINSTALLED'));
        $tpl->SetVariable('notinstalled_desc', _t('COMPONENTS_PLUGINS_NOTINSTALLED_DESC'));
        $tpl->SetVariable('lbl_info', _t('COMPONENTS_INFO'));
        $tpl->SetVariable('lbl_usage', _t('COMPONENTS_PLUGINS_USAGE'));
        $tpl->SetVariable('lbl_registry', _t('COMPONENTS_REGISTRY'));
        $tpl->SetVariable('lbl_acl', _t('COMPONENTS_ACL'));

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
        $tpl->SetVariable('lbl_summary', _t('COMPONENTS_SUMMARY'));
        $tpl->SetVariable('lbl_installed', _t('COMPONENTS_PLUGINS_INSTALLED').':');
        $tpl->SetVariable('lbl_notinstalled', _t('COMPONENTS_PLUGINS_NOTINSTALLED').':');
        $tpl->SetVariable('lbl_total', _t('COMPONENTS_PLUGINS_TOTAL').':');
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

        $tpl->SetVariable('lbl_version',   _t('COMPONENTS_VERSION').':');
        $tpl->SetVariable('lbl_example',   _t('COMPONENTS_PLUGINS_USAGE').':');
        $tpl->SetVariable('lbl_accesskey', _t('COMPONENTS_PLUGINS_ACCESSKEY').':');
        $tpl->SetVariable('lbl_friendly',  _t('COMPONENTS_PLUGINS_FRIENDLY').':');
        $tpl->SetVariable(
            'accesskey',
            method_exists($objPlugin, 'GetAccessKey')? $objPlugin->GetAccessKey() : _t('COMPONENTS_PLUGINS_NO_ACCESSKEY')
        );
        $tpl->SetVariable(
            'friendly',
            $objPlugin->friendly? _t('COMPONENTS_PLUGINS_FRIENDLY') : _t('COMPONENTS_PLUGINS_NOT_FRIENDLY')
        );
        $tpl->SetVariable(
            'example',
            $objPlugin->example? $objPlugin->example : _t('COMPONENTS_PLUGINS_NO_EXAMPLE')
        );
        $tpl->SetVariable('version', $objPlugin->version);

        $button =& Piwi::CreateWidget('Button', 'btn_install', _t('COMPONENTS_INSTALL'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('install', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_uninstall', _t('COMPONENTS_UNINSTALL'), STOCK_DELETE);
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

        $tpl->SetVariable('decription', _t('COMPONENTS_PLUGINS_USAGE_DESC'));
        $tpl->SetVariable('lbl_gadget', _t('COMPONENTS_PLUGINS_USAGE_GADGET'));
        $tpl->SetVariable('lbl_backend', _t('COMPONENTS_PLUGINS_USAGE_BACKEND'));
        $tpl->SetVariable('lbl_frontend', _t('COMPONENTS_PLUGINS_USAGE_FRONTEND'));

        $check =& Piwi::CreateWidget('CheckButtons', 'all');
        $check->AddOption('', 'backend');
        $check->AddEvent(ON_CLICK, 'usageCheckAll(this)');
        $tpl->SetVariable('all_backend', $check->Get());

        $check =& Piwi::CreateWidget('CheckButtons', 'all');
        $check->AddOption('', 'frontend');
        $check->AddEvent(ON_CLICK, 'usageCheckAll(this)');
        $tpl->SetVariable('all_frontend', $check->Get());

        $button =& Piwi::CreateWidget('Button', '', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:savePluginUsage();');
        $tpl->SetVariable('save', $button->Get());

        $button =& Piwi::CreateWidget('Button', '', _t('GLOBAL_RESET'), STOCK_REFRESH);
        $button->AddEvent(ON_CLICK, 'pluginUsage(true);');
        $tpl->SetVariable('reset', $button->Get());

        $tpl->ParseBlock('usage');
        return $tpl->Get();
    }

}