<?php
/**
 * Components Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
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

        $tpl = $this->gadget->template->loadAdmin('Gadgets.html');
        $tpl->SetBlock('components');

        $tpl->SetVariable('menubar', $this->Menubar('Gadgets'));
        $tpl->SetVariable('summary', $this->GadgetsSummary());

        $tpl->SetVariable('lbl_outdated', _t('COMPONENTS_GADGETS_OUTDATED'));
        $tpl->SetVariable('outdated_desc', _t('COMPONENTS_GADGETS_OUTDATED_DESC'));
        $tpl->SetVariable('lbl_notinstalled', _t('COMPONENTS_GADGETS_NOTINSTALLED'));
        $tpl->SetVariable('notinstalled_desc', _t('COMPONENTS_GADGETS_NOTINSTALLED_DESC'));
        $tpl->SetVariable('lbl_installed', _t('COMPONENTS_GADGETS_INSTALLED'));
        $tpl->SetVariable('installed_desc', _t('COMPONENTS_GADGETS_INSTALLED_DESC'));
        $tpl->SetVariable('lbl_core', _t('COMPONENTS_GADGETS_CORE'));
        $tpl->SetVariable('core_desc', _t('COMPONENTS_GADGETS_CORE_DESC'));
        $tpl->SetVariable('lbl_update', _t('COMPONENTS_UPDATE'));
        $tpl->SetVariable('lbl_enable', _t('COMPONENTS_ENABLE'));
        $tpl->SetVariable('lbl_install', _t('COMPONENTS_INSTALL'));
        $tpl->SetVariable('lbl_uninstall', _t('COMPONENTS_UNINSTALL'));
        $tpl->SetVariable('lbl_info', _t('COMPONENTS_INFO'));
        $tpl->SetVariable('lbl_registry', _t('COMPONENTS_REGISTRY'));
        $tpl->SetVariable('lbl_acl', _t('COMPONENTS_ACL'));
        $tpl->SetVariable('confirmDisableGadget', _t('COMPONENTS_GADGETS_CONFIRM_DISABLE'));
        $tpl->SetVariable('confirmUninstallGadget', _t('COMPONENTS_GADGETS_CONFIRM_UNINSTALL'));

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
        $tpl->SetVariable('lbl_summary', _t('COMPONENTS_SUMMARY'));
        $tpl->SetVariable('lbl_outdated', _t('COMPONENTS_GADGETS_OUTDATED').':');
        $tpl->SetVariable('lbl_disabled', _t('COMPONENTS_GADGETS_DISABLED').':');
        $tpl->SetVariable('lbl_installed', _t('COMPONENTS_GADGETS_INSTALLED').':');
        $tpl->SetVariable('lbl_notinstalled', _t('COMPONENTS_GADGETS_NOTINSTALLED').':');
        $tpl->SetVariable('lbl_core', _t('COMPONENTS_GADGETS_CORE').':');
        $tpl->SetVariable('lbl_total', _t('COMPONENTS_GADGETS_TOTAL').':');
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
        $objGadget = Jaws_Gadget::getInstance($gadget);
        if (Jaws_Error::IsError($objGadget)) {
            return $objGadget->getMessage();
        }

        $tpl = $this->gadget->template->loadAdmin('Gadget.html');
        $tpl->SetBlock('info');

        $tpl->SetVariable('gadget', $objGadget->title);
        $tpl->SetVariable('description', $objGadget->description);
        $tpl->SetVariable('image', "gadgets/$gadget/Resources/images/logo.png");

        $tpl->SetVariable('lbl_version', _t('GLOBAL_VERSION').':');
        $tpl->SetVariable('version', $objGadget->version);

        $tpl->SetVariable('lbl_jaws_version', _t('COMPONENTS_JAWS_VERSION').':');
        $tpl->SetVariable('jaws_version', $objGadget->GetRequiredJawsVersion());

        $tpl->SetVariable('lbl_section', _t('COMPONENTS_GADGETS_SECTION').':');
        $tpl->SetVariable('section', $objGadget->GetSection());

        // Requires
        $tpl->SetBlock('info/requires');
        $tpl->SetVariable('lbl_requires', _t('COMPONENTS_GADGETS_DEPENDENCIES').':');
        foreach ($objGadget->GetRequirements() as $rqGadget) {
            $tpl->SetBlock('info/requires/item');
            $tpl->SetVariable('gadget', $rqGadget);
            $tpl->ParseBlock('info/requires/item');
        }
        $tpl->ParseBlock('info/requires');

        // ACL Rules
        $tpl->SetBlock('info/acls');
        $tpl->SetVariable('lbl_acl_rules', _t('COMPONENTS_GADGETS_ACL_RULES').':');
        $acls = $GLOBALS['app']->ACL->fetchAll($gadget);
        if (!empty($acls)) {
            foreach ($acls as $key_name => $acl) {
                $tpl->SetBlock('info/acls/acl');
                $tpl->SetVariable('acl', $objGadget->acl->description($key_name, key($acl)));
                $tpl->ParseBlock('info/acls/acl');
            }
        }
        $tpl->ParseBlock('info/acls');

        $button =& Piwi::CreateWidget('Button', 'btn_update', _t('COMPONENTS_UPDATE'), STOCK_REFRESH);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('update', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_install', _t('COMPONENTS_INSTALL'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('install', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_uninstall', _t('COMPONENTS_UNINSTALL'), STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'javascript:setupComponent();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('uninstall', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_enable', _t('COMPONENTS_ENABLE'), STOCK_ADD);
        $button->AddEvent(ON_CLICK, 'javascript:enableGadget();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('enable', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_disable', _t('COMPONENTS_DISABLE'), STOCK_REMOVE);
        $button->AddEvent(ON_CLICK, 'javascript:disableGadget();');
        $button->SetStyle('display:none');
        $tpl->SetVariable('disable', $button->Get());

        $tpl->ParseBlock('info');
        return $tpl->Get();
    }

}