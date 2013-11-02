<?php
require_once JAWS_PATH. 'gadgets/Poll/Actions/Admin/Default.php';
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Actions_Admin_Group extends Poll_Actions_Admin_Default
{
    /**
     * Prepares the data (an array) of polls
     *
     * @access  public
     * @param   int     $offset  Offset of data
     * @return  array   Data array
     */
    function GetPollGroups($offset = null)
    {
        $model = $this->gadget->loadModel('Group');
        $groups = $model->GetPollGroups(10, $offset);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }

        $newData = array();
        foreach($groups as $group) {
            $groupData = array();
            $groupData['question'] = $group['title'];
            if ($group['visible'] == 1) {
                $groupData['visible'] = _t('GLOBAL_YES');
            } else {
                $groupData['visible'] = _t('GLOBAL_NO');
            }
            $actions = '';
            if ($this->gadget->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                    "javascript: editPollGroup(this, '" . $group['id'] . "');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('POLL_GROUPS_POLLS_TITLE'),
                    "javascript: editPollGroupPolls(this, '" . $group['id'] . "');",
                    'gadgets/Poll/Resources/images/polls_mini.png');
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                    "javascript: deletePollGroup(this, '". $group['id'] ."');",
                    STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $groupData['actions'] = $actions;
            $newData[] = $groupData;
        }
        return $newData;
    }

    /**
     * Build the datagrid of polls
     *
     * @access  public
     * @return  string  XHTML of Datagrid
     */
    function PollGroupsDatagrid()
    {
        $model = $this->gadget->loadModel();
        $total = $model->TotalOfData('poll_groups');
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('pollgroups_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_VISIBLE'), null, false);
        $column2->SetStyle('width:56px; white-space:nowrap;');
        $grid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column3->SetStyle('width:60px; white-space:nowrap;');
        $grid->AddColumn($column3);

        return $grid->Get();
    }

    /**
     * Prepares the group management view
     *
     * @access  public
     * @return  string  XHTML of view
     */
    function PollGroups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->loadAdminTemplate('PollGroups.html');
        $tpl->SetBlock('PollGroups');

        // Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('PollGroups'));
        $tpl->SetVariable('grid', $this->PollGroupsDatagrid());
        $tpl->SetVariable('pollgroup_ui', $this->PollGroupUI());

        $btnSave =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript: savePollGroup();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript: stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('incompleteGroupsFields',   _t('POLL_POLLS_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmPollGroupDelete',   _t('POLL_GROUPS_CONFIRM_DELETE'));
        $tpl->SetVariable('addPollGroup_title',       _t('POLL_GROUPS_ADD_TITLE'));
        $tpl->SetVariable('editPollGroup_title',      _t('POLL_GROUPS_EDIT_TITLE'));
        $tpl->SetVariable('editPollGroupPolls_title', _t('POLL_GROUPS_POLLS_TITLE'));
        $tpl->SetVariable('legend_title',             _t('POLL_GROUPS_ADD_TITLE'));

        $tpl->ParseBlock('PollGroups');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given poll group
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollGroupUI()
    {
        $tpl = $this->gadget->loadAdminTemplate('PollGroups.html');
        $tpl->SetBlock('PollGroupUI');

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $visible =& Piwi::CreateWidget('Combo', 'visible');
        $visible->SetID('visible');
        $visible->AddOption(_t('GLOBAL_NO'),  0);
        $visible->AddOption(_t('GLOBAL_YES'), 1);
        $visible->SetDefault(1);
        $tpl->SetVariable('lbl_visible', _t('GLOBAL_VISIBLE'));
        $tpl->SetVariable('visible', $visible->Get());

        $tpl->ParseBlock('PollGroupUI');

        return $tpl->Get();
    }

    /**
     * Returns the poll-group management
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollGroupPollsUI()
    {
        $tpl = $this->gadget->loadAdminTemplate('PollGroups.html');
        $tpl->SetBlock('PollGroupPollsUI');

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetEnabled(false);
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $model = $this->gadget->loadModel('Poll');
        $polls = $model->GetPolls();
        $pollsCombo =& Piwi::CreateWidget('CheckButtons', 'pg_polls_combo');
        foreach ($polls as $poll) {
            $pollsCombo->AddOption($poll['question'], $poll['id']);
        }
        $pollsCombo->SetColumns(1);
        $tpl->SetVariable('pg_polls_combo', $pollsCombo->Get());

        $tpl->ParseBlock('PollGroupPollsUI');
        return $tpl->Get();
    }

}