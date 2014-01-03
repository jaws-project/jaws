<?php
/**
 * StaticPage Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Actions_Admin_Group extends StaticPage_Actions_Admin_Default
{
    /**
     * Builds the administration UI for groups
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Groups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Groups.html');
        $tpl->SetBlock('Groups');

        // Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Groups'));

        // Grid
        $tpl->SetVariable('grid', $this->GroupsDataGrid());

        $entry =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE').':');
        $tpl->SetVariable('title', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $entry->SetStyle('direction:ltr;');
        $tpl->SetVariable('lbl_fast_url', _t('STATICPAGE_FASTURL').':');
        $tpl->SetVariable('fast_url', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'meta_keys', '');
        $tpl->SetVariable('lbl_meta_keys', _t('GLOBAL_META_KEYWORDS').':');
        $tpl->SetVariable('meta_keys', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'meta_desc', '');
        $tpl->SetVariable('lbl_meta_desc', _t('GLOBAL_META_DESCRIPTION').':');
        $tpl->SetVariable('meta_desc', $entry->Get());

        $combo =& Piwi::CreateWidget('Combo', 'visible');
        $combo->AddOption(_t('GLOBAL_NO'),  'false');
        $combo->AddOption(_t('GLOBAL_YES'), 'true');
        $combo->SetDefault('true');
        $tpl->SetVariable('visible', $combo->Get());
        $tpl->SetVariable('lbl_visible', _t('GLOBAL_VISIBLE').':');

        $btnSave =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript: saveGroup();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript: stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('legend_title',         _t('STATICPAGE_GROUP_ADD'));
        $tpl->SetVariable('add_group_title',      _t('STATICPAGE_GROUP_ADD'));
        $tpl->SetVariable('edit_group_title',     _t('STATICPAGE_GROUP_EDIT'));
        $tpl->SetVariable('confirm_group_delete', _t('STATICPAGE_GROUP_CONFIRM_DELETE'));
        $tpl->SetVariable('incomplete_fields',    _t('STATICPAGE_GROUP_INCOMPLETE_FIELDS'));

        $tpl->ParseBlock('Groups');
        return $tpl->Get();
    }

    /**
     * Builds the groups data grid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function GroupsDataGrid()
    {
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('groups_datagrid');
        //$grid->TotalRows(25);
        $grid->pageBy(10);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $column1->SetStyle('white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column2->SetStyle('width:40px;');
        $grid->AddColumn($column2);
        $grid->SetStyle('margin-top: 0px; width: 100%;');

        return $grid->Get();
    }

    /**
     * Prepares data for groups data grid
     *
     * @access  public
     * @param   int     $offset  Start offset of the result boundaries
     * @return  array   Grid data
     */
    function GetGroupsGrid($offset)
    {
        $model = $this->gadget->model->load('Group');

        $groups = $model->GetGroups(null, 10, $offset);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }
        $result = array();
        foreach ($groups as $group) {
            if (!$this->gadget->GetPermission('AccessGroup', $group['id'])) {
                continue;
            }
            $groupData = array();

            $groupData['title']  = ($group['visible'])? $group['title'] : '<font color="#aaa">'.$group['title'].'</font>';;

            $actions = '';
            if ($this->gadget->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                    "javascript: editGroup(this, '".$group['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                    "javascript: deleteGroup(this, '".$group['id']."');",
                    STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $groupData['actions'] = $actions;
            $result[] = $groupData;
        }

        return $result;
    }
}