<?php
/**
 * JMS (Jaws Management System) Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    JMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi ormar <dufuz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class JmsAdminHTML extends Jaws_GadgetHTML
{
    /**
     * Main method
     *
     * @access  public
     * @return  string  XHTML content of main
     */
    function Admin()
    {
        if ($this->GetPermission('ManageGadgets')) {
            return $this->ViewGadgets();
        }

        $this->CheckPermission('ManagePlugins');
        return $this->Plugins();
    }

    /**
     * Prepares the menubar
     *
     * @access  public
     * @param   string  $action  Selected action
     * @return  string  XHTML template content of menubar
     */
    function Menubar($action)
    {
        $actions = array('Gadgets', 'Plugins');
        if (!in_array($action, $actions)) {
            $action = 'Gadgets';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->GetPermission('ManageGadgets')) {
            $menubar->AddOption('Gadgets', _t('JMS_GADGETS'),
                                BASE_SCRIPT . '?gadget=Jms&amp;action=Admin', 'gadgets/Jms/images/gadgets.png');
        }
        if ($this->GetPermission('ManagePlugins')) {
            $menubar->AddOption('Plugins', _t('JMS_PLUGINS'),
                                BASE_SCRIPT . '?gadget=Jms&amp;action=Plugins', 'gadgets/Jms/images/plugins.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

    /**
     * Manages the gadgets settings
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewGadgets()
    {
        $this->CheckPermission('ManageGadgets');
        $this->AjaxMe('script.js');

        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');

        $tpl = new Jaws_Template('gadgets/Jms/templates/');
        $tpl->Load('Admin.html');
        $tpl->SetBlock('Jms');

        $tpl->SetVariable('confirmDisableComponent', _t('JMS_GADGETS_UNINSTALL_CONFIRM'));
        $tpl->SetVariable('confirmPurgeComponent', _t('JMS_GADGETS_PURGE_CONFIRM'));
        $tpl->SetVariable('confirmUninstallComponent', _t('JMS_GADGETS_UNINSTALL_CONFIRM'));

        $tpl->SetVariable('noAvailableData', _t('JMS_GADGETS_NOTHING'));
        $tpl->SetVariable('only_show_t', _t('JMS_ONLY_SHOW'));

        $gadgetsCombo =& Piwi::CreateWidget('Combo', 'gadgets_combo');
        $gadgetsCombo->SetSize(20);
        $gadgetsCombo->SetStyle('width: 200px; height: 350px;');
        $gadgetsCombo->AddEvent(ON_CHANGE, 'javascript: editGadget(this.value);');
        foreach ($model->GetGadgetsList(false, true, true) as $gadgetName => $gadgetProperties) {
            $gadgetsCombo->AddOption($gadgetProperties['name'], $gadgetName);
        }

        $onlyShow =& Piwi::CreateWidget('Combo', 'only_show');
        $onlyShow->SetID('only_show');
        $onlyShow->AddOption(_t('JMS_INSTALLED_COMPONENTS'), 'installed');
        $onlyShow->AddOption(_t('JMS_UNINSTALLED_COMPONENTS'), 'notinstalled');
        $onlyShow->AddOption(_t('JMS_OUTDATED_COMPONENTS'), 'outdated');
        $onlyShow->AddEvent(ON_CHANGE, 'javascript: updateView();');

        $buttons =& Piwi::CreateWidget('HBox');

        $purgeGadget =& Piwi::CreateWidget('Button', 'purge_button', _t('JMS_PURGE'), STOCK_DELETE);
        $purgeGadget->AddEvent(ON_CLICK, 'javascript: purgeComponent();');
        $purgeGadget->SetStyle('display: none');

        $uninstallGadget =& Piwi::CreateWidget('Button', 'uninstall_button', _t('JMS_UNINSTALL'), STOCK_REMOVE);
        $uninstallGadget->AddEvent(ON_CLICK, 'javascript: uninstallComponent();');
        $uninstallGadget->SetStyle('display: none');

        $installGadget =& Piwi::CreateWidget('Button', 'install_button', _t('JMS_INSTALL'), STOCK_SAVE);
        $installGadget->AddEvent(ON_CLICK, 'javascript: installComponent();');
        $installGadget->SetStyle('display: none');

        $updateGadget =& Piwi::CreateWidget('Button', 'update_button', _t('JMS_UPDATE'), STOCK_REFRESH);
        $updateGadget->AddEvent(ON_CLICK, 'javascript: updateComponent();');
        $updateGadget->SetStyle('display: none');

        $buttons->Add($purgeGadget);
        $buttons->Add($uninstallGadget);
        $buttons->Add($installGadget);
        $buttons->Add($updateGadget);

        $tpl->SetVariable('combo_components', $gadgetsCombo->get());
        $tpl->SetVariable('only_show', $onlyShow->Get());
        $tpl->SetVariable('buttons', $buttons->Get());
        $tpl->SetVariable('menubar', $this->Menubar('Gadgets'));
        $tpl->SetVariable('combo_name', 'gadgets_combo');
        $tpl->SetVariable('pluginsMode', 'false');
        $tpl->ParseBlock('Jms');

        return $tpl->Get();
    }

    /**
     * Prepares the HTML for managing plugins
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Plugins()
    {
        $this->CheckPermission('ManagePlugins');
        $this->AjaxMe('script.js');
        $GLOBALS['app']->Layout->AddScriptLink('libraries/xtree/xtree.js');

        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');

        $tpl = new Jaws_Template('gadgets/Jms/templates/');
        $tpl->Load('Admin.html');
        $tpl->SetBlock('Jms');

        $tpl->SetVariable('confirmUninstallComponent', _t('JMS_PLUGINS_UNINSTALL_CONFIRM'));
        $tpl->SetVariable('noAvailableData', _t('JMS_PLUGINS_NOTHING'));
        $tpl->SetVariable('only_show_t', _t('JMS_ONLY_SHOW'));
        $tpl->SetVariable('gadgetsMsg', _t('JMS_GADGETS'));
        $tpl->SetVariable('useAlways', _t('JMS_PLUGINS_ALWAYS'));

        $pluginsCombo =& Piwi::CreateWidget('Combo', 'plugins_combo');
        $pluginsCombo->SetSize(20);
        $pluginsCombo->SetStyle('width: 200px; height: 350px;');
        $pluginsCombo->AddEvent(ON_CHANGE, 'javascript: editPlugin(this.value);');
        foreach ($model->GetPluginsList(true) as $pluginName => $pluginProperties) {
            $pluginsCombo->AddOption($pluginProperties['name'], $pluginName);
        }

        $onlyShow =& Piwi::CreateWidget('Combo', 'only_show');
        $onlyShow->SetID('only_show');
        $onlyShow->AddOption(_t('JMS_INSTALLED_COMPONENTS'), 'installed');
        $onlyShow->AddOption(_t('JMS_UNINSTALLED_COMPONENTS'), 'notinstalled');
        $onlyShow->AddEvent(ON_CHANGE, 'javascript: updateView();');

        $buttons =& Piwi::CreateWidget('HBox');

        $uninstallPlugin =& Piwi::CreateWidget('Button', 'uninstall_button', _t('JMS_UNINSTALL'), STOCK_REMOVE);
        $uninstallPlugin->AddEvent(ON_CLICK, 'javascript: uninstallComponent();');
        $uninstallPlugin->SetStyle('display: none');

        $installPlugin =& Piwi::CreateWidget('Button', 'install_button', _t('JMS_INSTALL'), STOCK_SAVE);
        $installPlugin->AddEvent(ON_CLICK, 'javascript: installComponent();');
        $installPlugin->SetStyle('display: none');

        $pluginUsage =& Piwi::CreateWidget('Button', 'plugin_usage', _t('JMS_PLUGINS_USE_IN'), STOCK_PREFERENCES);
        $pluginUsage->AddEvent(ON_CLICK, 'javascript: pluginUsage();');
        $pluginUsage->SetStyle('display: none');

        $savePluginUsage =& Piwi::CreateWidget('Button', 'plugin_saveusage', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $savePluginUsage->AddEvent(ON_CLICK, 'javascript: savePluginUsage();');
        $savePluginUsage->SetStyle('display: none');

        $stopPluginUsage =& Piwi::CreateWidget('Button', 'plugin_stopusage', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $stopPluginUsage->AddEvent(ON_CLICK, 'javascript: stopPluginUsage();');
        $stopPluginUsage->SetStyle('display: none');

        $buttons->Add($uninstallPlugin);
        $buttons->Add($installPlugin);
        $buttons->Add($pluginUsage);
        $buttons->Add($stopPluginUsage);
        $buttons->Add($savePluginUsage);

        $tpl->SetVariable('combo_components', $pluginsCombo->get());
        $tpl->SetVariable('only_show', $onlyShow->Get());
        $tpl->SetVariable('buttons', $buttons->Get());
        $tpl->SetVariable('menubar', $this->Menubar('Plugins'));
        $tpl->SetVariable('combo_name', 'plugins_combo');
        $tpl->SetVariable('pluginsMode', 'true');
        $tpl->ParseBlock('Jms');

        return $tpl->Get();
    }

    /**
     * Enable a passed gadget by running
     * Jaws_GadgetHTML::EnableGadget method, then redirects to admin area
     *
     * @access  public
     */
    function EnableGadget()
    {
        $this->CheckPermission('ManageGadgets');

        require_once JAWS_PATH . 'include/Jaws/GadgetHTML.php';

        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('location', 'comp'), 'get');

        $gadget = $get['comp'];
        $gInfo = $GLOBALS['app']->loadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($gInfo)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $gadget), RESPONSE_ERROR);
        } else {
            $return = Jaws_GadgetHTML::EnableGadget($gadget);
            if (Jaws_Error::IsError($return)) {
                $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
            } elseif (!$return) {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $gInfo->getName()), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_OK', $gInfo->getName()), RESPONSE_NOTICE);
            }
        }
        Jaws_Header::Location(BASE_SCRIPT);
    }

    /**
     * Update a passed gadget by running
     * Jaws_GadgetHTML::UpgradeGadget method, then redirects to admin area
     *
     * @access  public
     */
    function UpdateGadget()
    {
        $this->CheckPermission('ManageGadgets');

        require_once JAWS_PATH . 'include/Jaws/GadgetHTML.php';

        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('location', 'comp'), 'get');

        $gadget = $get['comp'];
        if (!Jaws_GadgetHTML::IsGadgetUpdated($gadget)) {
            $return = Jaws_GadgetHTML::UpdateGadget($gadget);
            if (Jaws_Error::IsError($return)) {
                $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
            } elseif (!$return) {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_UPDATED_FAILURE', $gadget), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_UPDATED_OK', $gadget), RESPONSE_NOTICE);
            }
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_UPDATED_NO_NEED', $gadget), RESPONSE_ERROR);
        }
        Jaws_Header::Location(BASE_SCRIPT);
    }

    /**
     * Prepares the information view of a certain gadget
     *
     * @access  public
     * @param   string   $gadget  Gadget's name
     * @return  string   XHTML template of the view
     */
    function GetGadgetInfo($gadget)
    {
        $info = $GLOBALS['app']->LoadGadget($gadget, 'Info');

        $tpl = new Jaws_Template('gadgets/Jms/templates/');
        $tpl->Load('GadgetInfo.html');
        $tpl->SetBlock('info');

        if (Jaws_Error::IsError($info)) {
            $tpl->SetVariable('gadget', $gadget);
            $tpl->SetVariable('description', _t('JMS_GADGETS_NOT_EXISTS'));
        } else {
            $tpl->SetVariable('gadget', $info->GetName());
            $tpl->SetVariable('description', $info->GetDescription());
            // Requires
            $tpl->SetBlock('info/requires');
            $tpl->SetVariable('requires', _t('GLOBAL_GI_GADGET_REQUIREDGADGETS'));
            $tpl->SetVariable('gadget', _t('GLOBAL_GI_GADGET_GADGETNAME'));
            foreach ($info->GetRequirements() as $gadget) {
                $tpl->SetBlock('info/requires/item');
                $tpl->SetVariable('gadget', $gadget);
                $tpl->ParseBlock('info/requires/item');
            }
            $tpl->ParseBlock('info/requires');

            // Provides
            if (count($info->GetProvides()) > 0) {
                $tpl->SetBlock('info/provides');
                $tpl->SetVariable('provides', _t('GLOBAL_GI_GADGET_PROVIDES'));
                $tpl->SetVariable('description', _t('GLOBAL_GI_GADGET_DESCRIPTION'));
                $tpl->SetVariable('type', _t('GLOBAL_GI_GADGET_TYPE'));
                foreach ($info->GetProvides() as $service => $items) {
                    foreach ($items as $k => $v) {
                        $tpl->SetBlock('info/provides/item');
                        $tpl->SetVariable('description', $v['Description']);
                        $tpl->SetVariable('type', $service);
                        $tpl->ParseBlock('info/provides/item');
                    }
                }
                $tpl->ParseBlock('info/provides');
            }
        }
        $tpl->ParseBlock('info');

        return $tpl->Get();
    }

    /**
     * Prepares the information view of a certain plugin
     *
     * @access  public
     * @param   string   $plugin  Plugin's name
     * @return  string   XHTML template of the view
     */
    function GetPluginInfo($plugin)
    {
        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');

        $tpl = new Jaws_Template('gadgets/Jms/templates/');
        $tpl->Load('PluginInfo.html');
        $tpl->SetBlock('info');

        $info = $model->GetPluginInfo($plugin);

        $tpl->SetVariable('key', _t('JMS_KEY'));
        $tpl->SetVariable('value', _t('JMS_VALUE'));

        $tpl->SetVariable('example_t', _t('JMS_PLUGINS_USAGE'));
        $tpl->SetVariable('accesskey_t', _t('JMS_PLUGINS_ACCESSKEY'));
        $tpl->SetVariable('friendly_t', _t('JMS_PLUGINS_FRIENDLY'));
        $tpl->SetVariable('version_t', _t('JMS_VERSION'));

        $tpl->SetVariable('plugin', $info['name']);
        $tpl->SetVariable('description', $info['description']);
        $tpl->SetVariable('accesskey',
                          empty($info['accesskey']) ? _t('JMS_PLUGINS_NO_ACCESSKEY') : $info['accesskey']);
        $tpl->SetVariable('friendly',
                          ($info['friendly']) ? _t('JMS_PLUGINS_FRIENDLY') : _t('JMS_PLUGINS_NOT_FRIENDLY'));
        $tpl->SetVariable('example',
                          empty($info['example']) ? _t('JMS_PLUGINS_NO_EXAMPLE') : $info['example']);
        $tpl->SetVariable('version', $info['version']);

        $tpl->ParseBlock('info');

        return $tpl->Get();
    }

}