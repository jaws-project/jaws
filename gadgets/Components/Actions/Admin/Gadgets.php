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
class Components_Actions_Admin_Gadgets extends Components_Actions_Admin_Default
{
    /**
     * Builds gadgets management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Gadgets()
    {
        $this->gadget->CheckPermission('ManageGadgets');
        $this->AjaxMe('script.js');

        $this->gadget->define('lbl_update', $this::t('UPDATE'));
        $this->gadget->define('lbl_enable', $this::t('ENABLE'));
        $this->gadget->define('lbl_install', $this::t('INSTALL'));
        $this->gadget->define('lbl_uninstall', $this::t('UNINSTALL'));
        $this->gadget->define('confirmUninstallGadget', $this::t('GADGETS_CONFIRM_UNINSTALL'));
        $this->gadget->define('confirmDisableGadget', $this::t('GADGETS_CONFIRM_DISABLE'));

        $tpl = $this->gadget->template->loadAdmin('Gadgets.html');
        $tpl->SetBlock('components');

        $tpl->SetVariable('menubar', $this->Menubar('Gadgets'));
        $tpl->SetVariable('summary', $this->GadgetsSummary());

        $tpl->SetVariable('lbl_outdated', $this::t('GADGETS_OUTDATED'));
        $tpl->SetVariable('outdated_desc', $this::t('GADGETS_OUTDATED_DESC'));
        $tpl->SetVariable('lbl_notinstalled', $this::t('GADGETS_NOTINSTALLED'));
        $tpl->SetVariable('notinstalled_desc', $this::t('GADGETS_NOTINSTALLED_DESC'));
        $tpl->SetVariable('lbl_installed', $this::t('GADGETS_INSTALLED'));
        $tpl->SetVariable('installed_desc', $this::t('GADGETS_INSTALLED_DESC'));
        $tpl->SetVariable('lbl_core', $this::t('GADGETS_CORE'));
        $tpl->SetVariable('core_desc', $this::t('GADGETS_CORE_DESC'));
        $tpl->SetVariable('lbl_info', $this::t('INFO'));
        $tpl->SetVariable('lbl_registry', $this::t('REGISTRY'));
        $tpl->SetVariable('lbl_acl', $this::t('ACL'));

        $button =& Piwi::CreateWidget('Button', 'btn_close', 'X ');
        $button->AddEvent(ON_CLICK, 'javascript:closeUI();');
        $tpl->SetVariable('close', $button->Get());

        $tpl->ParseBlock('components');
        return $tpl->Get();
    }

    /**
     * Builds gadgets summary UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function GadgetsSummary()
    {
        $tpl = $this->gadget->template->loadAdmin('GadgetsSummary.html');
        $tpl->SetBlock('summary');
        $tpl->SetVariable('lbl_summary', $this::t('SUMMARY'));
        $tpl->SetVariable('lbl_outdated', $this::t('GADGETS_OUTDATED').':');
        $tpl->SetVariable('lbl_disabled', $this::t('GADGETS_DISABLED').':');
        $tpl->SetVariable('lbl_installed', $this::t('GADGETS_INSTALLED').':');
        $tpl->SetVariable('lbl_notinstalled', $this::t('GADGETS_NOTINSTALLED').':');
        $tpl->SetVariable('lbl_core', $this::t('GADGETS_CORE').':');
        $tpl->SetVariable('lbl_total', $this::t('GADGETS_TOTAL').':');
        $tpl->ParseBlock('summary');
        return $tpl->Get();
    }

    /**
     * Builds UI for the gadget information
     *
     * @access  public
     * @param   string   $gadget  Gadget's name
     * @return  string   XHTML template of the view
     */
    function GadgetInfo($gadget)
    {
        $objGadget = Jaws_Gadget::getInstance($gadget, false);
        if (Jaws_Error::IsError($objGadget)) {
            return $objGadget->getMessage();
        }

        $tpl = $this->gadget->template->loadAdmin('Gadget.html');
        $tpl->SetBlock('info');

        $tpl->SetVariable('gadget', $objGadget->title);
        $tpl->SetVariable('description', $objGadget->description);
        $tpl->SetVariable('image', "gadgets/$gadget/Resources/images/logo.png");

        $tpl->SetVariable('lbl_version', Jaws::t('VERSION').':');
        $tpl->SetVariable('version', $objGadget->version);

        $tpl->SetVariable('lbl_jaws_version', $this::t('JAWS_VERSION').':');
        $tpl->SetVariable('jaws_version', $objGadget->GetRequiredJawsVersion());

        $tpl->SetVariable('lbl_section', $this::t('GADGETS_SECTION').':');
        $tpl->SetVariable('section', $objGadget->GetSection());

        // Requires
        $tpl->SetBlock('info/requires');
        $tpl->SetVariable('lbl_requires', $this::t('GADGETS_DEPENDENCIES').':');
        foreach ($objGadget->requirement as $rqGadget) {
            $tpl->SetBlock('info/requires/item');
            $tpl->SetVariable('gadget', $rqGadget);
            $tpl->ParseBlock('info/requires/item');
        }
        $tpl->ParseBlock('info/requires');

        // ACL Rules
        $tpl->SetBlock('info/acls');
        $tpl->SetVariable('lbl_acl_rules', $this::t('GADGETS_ACL_RULES').':');
        $acls = $this->app->acl->fetchAll($gadget);
        if (!empty($acls)) {
            foreach ($acls as $key_name => $acl) {
                foreach ($acl as $subkey => $value) {
                    $tpl->SetBlock('info/acls/acl');
                    $tpl->SetVariable('acl', $objGadget->acl->description($key_name, $subkey));
                    $tpl->ParseBlock('info/acls/acl');
                }
            }
        }
        $tpl->ParseBlock('info/acls');

        $button =& Piwi::CreateWidget('Button', 'btn_update', $this::t('UPDATE'), STOCK_REFRESH);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('update', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_install', $this::t('INSTALL'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('install', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_uninstall', $this::t('UNINSTALL'), STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('uninstall', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_enable', $this::t('ENABLE'), STOCK_ADD);
        $button->AddEvent(ON_CLICK, 'javascript:enableGadget();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('enable', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_disable', $this::t('DISABLE'), STOCK_REMOVE);
        $button->AddEvent(ON_CLICK, 'javascript:disableGadget();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('disable', $button->Get());

        $tpl->ParseBlock('info');
        return $tpl->Get();
    }

}