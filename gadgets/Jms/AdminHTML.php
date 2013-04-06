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

        $tpl = new Jaws_Template('gadgets/Jms/templates/');
        $tpl->Load('AdminGadgets.html');
        $tpl->SetBlock('jms');

        $tpl->SetVariable('menubar', $this->Menubar('Gadgets'));
        $tpl->SetVariable('summaryUI', $this->GadgetsSummary());

        $tpl->SetVariable('lbl_outdated', _t('JMS_GADGETS_OUTDATED'));
        $tpl->SetVariable('outdated_desc', _t('JMS_GADGETS_OUTDATED_DESC'));
        $tpl->SetVariable('lbl_notinstalled', _t('JMS_GADGETS_NOTINSTALLED'));
        $tpl->SetVariable('notinstalled_desc', _t('JMS_GADGETS_NOTINSTALLED_DESC'));
        $tpl->SetVariable('lbl_installed', _t('JMS_GADGETS_INSTALLED'));
        $tpl->SetVariable('installed_desc', _t('JMS_GADGETS_INSTALLED_DESC'));
        $tpl->SetVariable('lbl_core', _t('JMS_GADGETS_CORE'));
        $tpl->SetVariable('core_desc', _t('JMS_GADGETS_CORE_DESC'));
        $tpl->SetVariable('lbl_update', _t('JMS_UPDATE'));
        $tpl->SetVariable('lbl_install', _t('JMS_INSTALL'));
        $tpl->SetVariable('lbl_uninstall', _t('JMS_UNINSTALL'));
        $tpl->SetVariable('confirmDisableGadget', _t('JMS_GADGETS_CONFIRM_DISABLE'));
        $tpl->SetVariable('confirmUninstallGadget', _t('JMS_GADGETS_CONFIRM_UNINSTALL'));

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
     * Builds gadgets summary UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GadgetsSummary()
    {
        $tpl = new Jaws_Template('gadgets/Jms/templates/');
        $tpl->Load('GadgetsSummary.html');
        $tpl->SetBlock('summary');
        $tpl->SetVariable('lbl_summary', _t('JMS_SUMMARY'));
        $tpl->SetVariable('lbl_outdated', _t('JMS_GADGETS_OUTDATED').':');
        $tpl->SetVariable('lbl_installed', _t('JMS_GADGETS_INSTALLED').':');
        $tpl->SetVariable('lbl_notinstalled', _t('JMS_GADGETS_NOTINSTALLED').':');
        $tpl->SetVariable('lbl_core', _t('JMS_GADGETS_CORE').':');
        $tpl->SetVariable('lbl_total', _t('JMS_GADGETS_TOTAL').':');
        $tpl->ParseBlock('summary');
        return $tpl->Get();
    }

    /**
     * Install requested gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  void
     */
    function InstallGadget($gadget = '')
    {
        $redirect = false;
        $this->gadget->CheckPermission('ManageGadgets');
        if (empty($gadget)) {
            $redirect = true;
            $request =& Jaws_Request::getInstance();
            $gadget = $request->get('comp', 'get');
        }

        $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($objGadget)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLE_FAILURE', $gadget), RESPONSE_ERROR);
        } else {
            $installer = $objGadget->load('Installer');
            $return = $installer->InstallGadget();
            if (Jaws_Error::IsError($return)) {
                $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLE_OK', $objGadget->GetTitle()), RESPONSE_NOTICE);
            }
        }

        if ($redirect) {
            Jaws_Header::Location(BASE_SCRIPT);
        }
    }

    /**
     * Upgrade requested gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  void
     */
    function UpgradeGadget($gadget = '')
    {
        $redirect = false;
        $this->gadget->CheckPermission('ManageGadgets');
        if (empty($gadget)) {
            $redirect = true;
            $request =& Jaws_Request::getInstance();
            $gadget = $request->get('comp', 'get');
        }

        if (!Jaws_Gadget::IsGadgetUpdated($gadget)) {
            $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
            $installer = $objGadget->load('Installer');
            $return = $installer->UpgradeGadget();
            if (Jaws_Error::IsError($return)) {
                $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_UPDATE_OK', $gadget), RESPONSE_NOTICE);
            }
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_UPDATE_NO_NEED', $gadget), RESPONSE_ERROR);
        }

        if ($redirect) {
            Jaws_Header::Location(BASE_SCRIPT);
        }
    }

    /**
     * UninstallGadget requested gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  void
     */
    function UninstallGadget($gadget = '')
    {
        $redirect = false;
        $this->gadget->CheckPermission('ManageGadgets');
        if (empty($gadget)) {
            $redirect = true;
            $request =& Jaws_Request::getInstance();
            $gadget = $request->get('comp', 'get');
        }

        $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($objGadget)) {
            $GLOBALS['app']->Session->PushLastResponse($objGadget->GetMessage(), RESPONSE_ERROR);
        } else {
            $installer = $objGadget->load('Installer');
            $return = $installer->UninstallGadget();
            if (Jaws_Error::IsError($return)) {
                $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_DISABLE_OK', $objGadget->GetTitle()), RESPONSE_NOTICE);
            }
        }

        if ($redirect) {
            Jaws_Header::Location(BASE_SCRIPT);
        }
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

            $tpl->SetVariable('lbl_location', _t('JMS_GADGETS_LOCATION').':');
            $tpl->SetVariable('location', $info->GetSection());

            // Requires
            $tpl->SetBlock('info/requires');
            $tpl->SetVariable('lbl_requires', _t('JMS_GADGETS_DEPENDENCIES').':');
            foreach ($info->GetRequirements() as $gadget) {
                $tpl->SetBlock('info/requires/item');
                $tpl->SetVariable('gadget', $gadget);
                $tpl->ParseBlock('info/requires/item');
            }
            $tpl->ParseBlock('info/requires');

            // ACL Rules
            $tpl->SetBlock('info/acls');
            $tpl->SetVariable('lbl_acl_rules', _t('JMS_GADGETS_ACL_RULES').':');
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
        $tpl->SetVariable('summaryUI', $this->PluginsSummary());

        $tpl->SetVariable('lbl_installed', _t('JMS_PLUGINS_INSTALLED'));
        $tpl->SetVariable('installed_desc', _t('JMS_PLUGINS_INSTALLED_DESC'));
        $tpl->SetVariable('lbl_notinstalled', _t('JMS_PLUGINS_NOTINSTALLED'));
        $tpl->SetVariable('notinstalled_desc', _t('JMS_PLUGINS_NOTINSTALLED_DESC'));
        $tpl->SetVariable('lbl_install', _t('JMS_INSTALL'));
        $tpl->SetVariable('lbl_uninstall', _t('JMS_UNINSTALL'));
        $tpl->SetVariable('pluginUsageDesc', _t('JMS_PLUGINS_USAGE_DESC'));
        $tpl->SetVariable('confirmUninstallPlugin', _t('JMS_PLUGINS_CONFIRM_UNINSTALL'));

        $button =& Piwi::CreateWidget('Button', 'btn_install', _t('JMS_INSTALL'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('install', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_uninstall', _t('JMS_UNINSTALL'), STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('uninstall', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_usage', _t('JMS_PLUGINS_USAGE'), STOCK_PREFERENCES);
        $button->AddEvent(ON_CLICK, 'javascript:pluginUsage();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('usage', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:savePluginUsage();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('save', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $button->AddEvent(ON_CLICK, 'javascript:cancel();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('cancel', $button->Get());

        $tpl->ParseBlock('jms');
        return $tpl->Get();
    }

    /**
     * Builds plugins summary UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PluginsSummary()
    {
        $tpl = new Jaws_Template('gadgets/Jms/templates/');
        $tpl->Load('PluginsSummary.html');
        $tpl->SetBlock('summary');
        $tpl->SetVariable('lbl_summary', _t('JMS_SUMMARY'));
        $tpl->SetVariable('lbl_installed', _t('JMS_PLUGINS_INSTALLED').':');
        $tpl->SetVariable('lbl_notinstalled', _t('JMS_PLUGINS_NOTINSTALLED').':');
        $tpl->SetVariable('lbl_total', _t('JMS_PLUGINS_TOTAL').':');
        $tpl->ParseBlock('summary');
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

        $tpl->SetVariable('image', 'gadgets/Jms/images/plugin.png');
        $tpl->SetVariable('lbl_version', _t('JMS_VERSION').':');
        $tpl->SetVariable('lbl_example', _t('JMS_PLUGINS_USAGE').':');
        $tpl->SetVariable('lbl_accesskey', _t('JMS_PLUGINS_ACCESSKEY').':');
        $tpl->SetVariable('lbl_friendly', _t('JMS_PLUGINS_FRIENDLY').':');

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