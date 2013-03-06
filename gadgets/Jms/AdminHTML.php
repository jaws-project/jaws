<?php
/**
 * JMS (Jaws Management System) Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    JMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi ormar <dufuz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jms_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Main method
     *
     * @access  public
     * @return  string  XHTML content of main
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('ManageGadgets')) {
            return $this->Gadgets();
        }

        $this->gadget->CheckPermission('ManagePlugins');
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
        if ($this->gadget->GetPermission('ManageGadgets')) {
            $menubar->AddOption('Gadgets', _t('JMS_GADGETS'),
                                BASE_SCRIPT . '?gadget=Jms&amp;action=Gadgets', 'gadgets/Jms/images/gadgets.png');
        }
        if ($this->gadget->GetPermission('ManagePlugins')) {
            $menubar->AddOption('Plugins', _t('JMS_PLUGINS'),
                                BASE_SCRIPT . '?gadget=Jms&amp;action=Plugins', 'gadgets/Jms/images/plugins.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

    /**
     * Builds gadgets management UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Gadgets()
    {
        $this->gadget->CheckPermission('ManageGadgets');
        $this->AjaxMe('script.js');

        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');

        $tpl = new Jaws_Template('gadgets/Jms/templates/');
        $tpl->Load('AdminGadgets.html');
        $tpl->SetBlock('jms');

        $tpl->SetVariable('menubar', $this->Menubar('Gadgets'));

        $tpl->SetVariable('lbl_outdated_gadgets', _t('JMS_GADGETS_OUTDATED_GADGETS'));
        $tpl->SetVariable('lbl_notinstalled_gadgets', _t('JMS_GADGETS_NOTINSTALLED_GADGETS'));
        $tpl->SetVariable('lbl_installed_gadgets', _t('JMS_GADGETS_INSTALLED_GADGETS'));
        $tpl->SetVariable('outdated_desc', _t('JMS_GADGETS_OUTDATED_GADGETS_DESC'));
        $tpl->SetVariable('notinstalled_desc', _t('JMS_GADGETS_NOTINSTALLED_GADGETS_DESC'));
        $tpl->SetVariable('installed_desc', _t('JMS_GADGETS_INSTALLED_GADGETS_DESC'));

        $tpl->SetVariable('lbl_update', _t('JMS_UPDATE'));
        $tpl->SetVariable('lbl_install', _t('JMS_INSTALL'));
        $tpl->SetVariable('lbl_uninstall', _t('JMS_UNINSTALL'));

        $tpl->SetVariable('confirmDisableComponent', _t('JMS_GADGETS_CONFIRM_DISABLE'));
        $tpl->SetVariable('confirmUninstallComponent', _t('JMS_GADGETS_CONFIRM_UNINSTALL'));

        $button =& Piwi::CreateWidget('Button', 'btn_update', _t('JMS_UPDATE'), STOCK_REFRESH);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('update', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_install', _t('JMS_INSTALL'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('install', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_uninstall', _t('JMS_UNINSTALL'), STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('uninstall', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_disable', _t('JMS_DISABLE'), STOCK_REMOVE);
        $button->AddEvent(ON_CLICK, 'javascript:disableGadget();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('disable', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $button->AddEvent(ON_CLICK, 'javascript:cancel();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('cancel', $button->Get());

        $tpl->ParseBlock('jms');
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
        $this->gadget->CheckPermission('ManagePlugins');
        $this->AjaxMe('script.js');
        $GLOBALS['app']->Layout->AddScriptLink('libraries/xtree/xtree.js');

        $tpl = new Jaws_Template('gadgets/Jms/templates/');
        $tpl->Load('AdminPlugins.html');
        $tpl->SetBlock('jms');

        $tpl->SetVariable('menubar', $this->Menubar('Plugins'));
        $tpl->SetVariable('pluginsMode', 'true');

        $tpl->ParseBlock('jms');
        return $tpl->Get();

        $tpl->SetVariable('confirmUninstallComponent', _t('JMS_PLUGINS_UNINSTALL_CONFIRM'));
        $tpl->SetVariable('noAvailableData', _t('JMS_PLUGINS_NOTHING'));
        $tpl->SetVariable('only_show_t', _t('JMS_ONLY_SHOW'));
        $tpl->SetVariable('gadgetsMsg', _t('JMS_GADGETS'));
        $tpl->SetVariable('useAlways', _t('JMS_PLUGINS_ALWAYS'));

        $pluginsCombo =& Piwi::CreateWidget('Combo', 'plugins_combo');
        $pluginsCombo->SetSize(20);
        $pluginsCombo->SetStyle('width: 200px; height: 350px;');
        $pluginsCombo->AddEvent(ON_CHANGE, 'javascript: editPlugin(this.value);');
        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
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
        $tpl->SetVariable('combo_name', 'plugins_combo');
        $tpl->ParseBlock('Jms');

        return $tpl->Get();
    }

    /**
     * Enable a passed gadget by running
     * Jaws_Gadget_HTML::EnableGadget method, then redirects to admin area
     *
     * @access  public
     * @param   bool    $redirect   Redirect to root page
     * @return  void
     */
    function EnableGadget($gadget = '', $redirect = true)
    {
        $this->gadget->CheckPermission('ManageGadgets');
        if (empty($gadget)) {
            $request =& Jaws_Request::getInstance();
            $gadget = $request->get('comp', 'get');
        }

        $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($objGadget)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $gadget), RESPONSE_ERROR);
        } else {
            $installer = $objGadget->load('Installer');
            $return = $installer->InstallGadget();
            if (Jaws_Error::IsError($return)) {
                $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_OK', $objGadget->GetTitle()), RESPONSE_NOTICE);
            }
        }

        if ($redirect) {
            Jaws_Header::Location(BASE_SCRIPT);
        }
    }

    /**
     * Update a passed gadget by running
     * Jaws_Gadget_HTML::UpgradeGadget method, then redirects to admin area
     *
     * @access  public
     * @return  void
     */
    function UpdateGadget()
    {
        $this->gadget->CheckPermission('ManageGadgets');
        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('location', 'comp'), 'get');

        $gadget = $get['comp'];
        if (!Jaws_Gadget::IsGadgetUpdated($gadget)) {
            $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
            $installer = $objGadget->load('Installer');
            $return = $installer->UpgradeGadget();
            if (Jaws_Error::IsError($return)) {
                $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
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
            $tpl->SetVariable('gadget', $info->GetTitle());
            $tpl->SetVariable('description', $info->GetDescription());
            $tpl->SetVariable('image', "gadgets/$gadget/images/logo.png");

            $tpl->SetVariable('lbl_version', _t('GLOBAL_VERSION').':');
            $tpl->SetVariable('version', $info->GetVersion());

            $tpl->SetVariable('lbl_jaws_version', _t('JMS_JAWS_VERSION').':');
            $tpl->SetVariable('jaws_version', $info->GetRequiredJawsVersion());

            $tpl->SetVariable('lbl_location', _t('JMS_GADGET_LOCATION').':');
            $tpl->SetVariable('location', $info->GetSection());

            // Requires
            $tpl->SetBlock('info/requires');
            $tpl->SetVariable('lbl_requires', _t('JMS_DEPENDENCIES').':');
            foreach ($info->GetRequirements() as $gadget) {
                $tpl->SetBlock('info/requires/item');
                $tpl->SetVariable('gadget', $gadget);
                $tpl->ParseBlock('info/requires/item');
            }
            $tpl->ParseBlock('info/requires');

            // ACL Rules
            $tpl->SetBlock('info/acls');
            $tpl->SetVariable('lbl_acl_rules', _t('JMS_GADGETS_ACL').':');
            foreach (array_keys($info->GetACLs()) as $acl) {
                $tpl->SetBlock('info/acls/acl');
                $tpl->SetVariable('acl', end(explode('/', $acl)));
                $tpl->ParseBlock('info/acls/acl');
            }
            $tpl->ParseBlock('info/acls');
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