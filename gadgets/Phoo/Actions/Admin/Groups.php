<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_Groups extends Phoo_Actions_Admin_Default
{
    /**
     * Displays phoo groups administration panel
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Groups()
    {
        $this->gadget->CheckPermission('Groups');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Groups.html');
        $tpl->SetBlock('groups');

        // Header
        $tpl->SetVariable('menubar', $this->MenuBar('Groups'));

        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('addGroupTitle', _t('PHOO_GROUPS_ADD_GROUP'));
        $tpl->SetVariable('editGroupTitle', _t('PHOO_GROUPS_EDIT_GROUP'));
        $tpl->SetVariable('confirmGroupDelete', _t('PHOO_GROUPS_CONFIRM_DELETE'));
        $tpl->SetVariable('incompleteGroupFields', _t('PHOO_GROUPS_INCOMPLETE_GROUP_FIELDS'));
        $tpl->SetVariable('delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('save', _t('GLOBAL_SAVE'));
        $tpl->SetVariable('delete_icon', STOCK_DELETE);
        $tpl->SetVariable('save_icon', STOCK_SAVE);
        $tpl->SetVariable('cancel_icon', STOCK_CANCEL);

        //Fill the groups combo..
        $comboGroups =& Piwi::CreateWidget('Combo', 'groups_combo');
        $comboGroups->SetSize(14);
        $comboGroups->AddEvent(ON_CHANGE, 'javascript: editGroup(this.value);');
        $comboGroups->SetStyle('width: 100%;');
        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();
        foreach($groups as $group) {
            $comboGroups->AddOption($group['name'], $group['id']);
        }
        $tpl->SetVariable('combo', $comboGroups->Get());

        $tpl->ParseBlock('groups');
        return $tpl->Get();
    }
}