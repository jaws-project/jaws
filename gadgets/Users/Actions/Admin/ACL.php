<?php
require_once JAWS_PATH. 'gadgets/Users/Actions/Admin/Default.php';
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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
        $tpl = $this->gadget->loadAdminTemplate('ACL.html');
        $tpl->SetBlock('acl');

        $tpl->SetVariable('lbl_permissions', _t('USERS_ACLS').':');
        $tpl->SetVariable('lbl_components', _t('USERS_ACLS_COMPONENTS').':');
        $tpl->SetVariable('lbl_allow', _t('USERS_ACLS_ALLOW'));
        $tpl->SetVariable('lbl_deny', _t('USERS_ACLS_DENY'));
        $tpl->SetVariable('lbl_default', _t('USERS_ACLS_DEFAULT'));

        // Components
        $model = Jaws_Gadget::getInstance('Components')->loadModel('Gadgets');
        $gadgets = $model->GetGadgetsList(null, true, true);
        $combo =& Piwi::CreateWidget('Combo', 'components');
        $combo->AddOption('', '');
        foreach ($gadgets as $gadget) {
            $combo->AddOption($gadget['title'], $gadget['name']);
        }
        $combo->AddEvent(ON_CHANGE, 'getACL();');
        $tpl->SetVariable('components', $combo->Get());

        $tpl->ParseBlock('acl');
        return $tpl->Get();
    }
}