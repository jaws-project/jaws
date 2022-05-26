<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
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
        $model = $this->gadget->model->load('Group');
        $groups = $model->GetPollGroups(10, $offset);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }

        $newData = array();
        foreach($groups as $group) {
            $groupData = array();
            $groupData['title'] = $group['title'];
            if ($group['published'] == true) {
                $groupData['published'] = Jaws::t('YESS');
            } else {
                $groupData['published'] = Jaws::t('NOO');
            }
            $actions = '';
            if ($this->gadget->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget('Link', Jaws::t('EDIT'),
                    "javascript:editPollGroup(this, '" . $group['id'] . "');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', $this::t('GROUPS_POLLS_TITLE'),
                    "javascript:editPollGroupPolls(this, '" . $group['id'] . "');",
                    'gadgets/Poll/Resources/images/polls_mini.png');
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', Jaws::t('DELETE'),
                    "javascript:deletePollGroup(this, '". $group['id'] ."');",
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
        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('poll_groups');
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('pollgroups_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', Jaws::t('TITLE'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', Jaws::t('PUBLISHED'), null, false);
        $column2->SetStyle('width:56px; white-space:nowrap;');
        $grid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', Jaws::t('ACTIONS'), null, false);
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
        $this->gadget->define('incompleteGroupsFields',   $this::t('POLLS_INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmPollGroupDelete',   $this::t('GROUPS_CONFIRM_DELETE'));
        $this->gadget->define('addPollGroup_title',       $this::t('GROUPS_ADD_TITLE'));
        $this->gadget->define('editPollGroup_title',      $this::t('GROUPS_EDIT_TITLE'));
        $this->gadget->define('editPollGroupPolls_title', $this::t('GROUPS_POLLS_TITLE'));

        $tpl = $this->gadget->template->loadAdmin('PollGroups.html');
        $tpl->SetBlock('PollGroups');

        // Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('PollGroups'));
        $tpl->SetVariable('grid', $this->PollGroupsDatagrid());
        $tpl->SetVariable('pollgroup_ui', $this->PollGroupUI());

        $btnSave =& Piwi::CreateWidget('Button','btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:savePollGroup();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button','btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());
        $tpl->SetVariable('legend_title',             $this::t('GROUPS_ADD_TITLE'));

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
        $tpl = $this->gadget->template->loadAdmin('PollGroups.html');
        $tpl->SetBlock('PollGroupUI');

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->AddOption(Jaws::t('NOO'),  0);
        $published->AddOption(Jaws::t('YESS'), 1);
        $published->SetDefault(1);
        $tpl->SetVariable('lbl_published', Jaws::t('PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

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
        $tpl = $this->gadget->template->loadAdmin('PollGroups.html');
        $tpl->SetBlock('PollGroupPollsUI');

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetEnabled(false);
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $model = $this->gadget->model->load('Poll');
        $polls = $model->GetPolls();
        $pollsCombo =& Piwi::CreateWidget('CheckButtons', 'pg_polls_combo');
        foreach ($polls as $poll) {
            $pollsCombo->AddOption($poll['title'], $poll['id']);
        }
        $pollsCombo->SetColumns(1);
        $tpl->SetVariable('pg_polls_combo', $pollsCombo->Get());

        $tpl->ParseBlock('PollGroupPollsUI');
        return $tpl->Get();
    }

}