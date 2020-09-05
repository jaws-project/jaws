<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_ACL extends Users_Actions_Admin_Default
{
    /**
     * Builds ACL UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ACLUI()
    {
        $tpl = $this->gadget->template->loadAdmin('ACL.html');
        $tpl->SetBlock('acl');

        $tpl->SetVariable('lbl_permissions', $this::t('ACLS').':');
        $tpl->SetVariable('lbl_components', $this::t('ACLS_COMPONENTS').':');
        $tpl->SetVariable('lbl_allow', $this::t('ACLS_ALLOW'));
        $tpl->SetVariable('lbl_deny', $this::t('ACLS_DENY'));
        $tpl->SetVariable('lbl_default', $this::t('ACLS_DEFAULT'));

        // Components
        $model = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $model->GetGadgetsList(null, true, true);
        $combo =& Piwi::CreateWidget('Combo', 'components');
        $combo->AddOption('', '');
        foreach ($gadgets as $gadget) {
            $combo->AddOption($gadget['title'], $gadget['name']);
        }
        $combo->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').getACL();");
        $tpl->SetVariable('components', $combo->Get());

        $tpl->ParseBlock('acl');
        return $tpl->Get();
    }
}