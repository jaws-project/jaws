<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Admin_Groups extends UsersAdminHTML
{
    /**
     * Prepares the datagrid view (XHTML of datagrid)
     *
     * @access  public
     * @return  string XHTML of datagrid
     */
    function GroupsDataGrid()
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $uModel = new Jaws_User();
        $total = $uModel->GetGroupsCount();

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->pageBy(12);
        $datagrid->SetID('groups_datagrid');
        $col = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $datagrid->AddColumn($col);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_NAME'), null, false);
        $column1->SetStyle('width: 120px;');
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column2->SetStyle('width: 120px;');
        $datagrid->AddColumn($column2);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');

        return $datagrid->Get();
    }

    /**
     * Prepares the list of groups
     *
     * @access  public
     * @param   int    $offset  Offset of data array
     * @return  array  Data
     */
    function GetGroups($enabled, $offset = null)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $uModel = new Jaws_User();
        $groups = $uModel->GetGroups($enabled, 'title', 12, $offset);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }

        $retData = array();
        foreach ($groups as $group) {
            $grpData = array();
            $grpData['title'] = $group['title'];
            $grpData['name']  = $group['name'];

            $actions = '';
            if ($this->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('GLOBAL_EDIT'),
                                            "javascript: editGroup(this, '".$group['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->GetPermission('ManageGroupACLs')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('USERS_ACLRULES'),
                                            "javascript: editGroupACL(this, '".$group['id']."');",
                                            'gadgets/Users/images/acls.png');
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('USERS_GROUPS_MEMBERS'),
                                            "javascript: editGroupUsers(this, '".$group['id']."');",
                                            'gadgets/Users/images/groups_mini.png');
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('USERS_GROUPS_DELETE'),
                                            "javascript: deleteGroup(this, '".$group['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }

            $grpData['actions'] = $actions;
            $retData[] = $grpData;
        }

        return $retData;
    }

    /**
     * Prepares the group management view
     *
     * @access  public
     * @return  string  XHTML of view
     */
    function Groups()
    {
        $this->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');

        $GLOBALS['app']->Layout->AddScriptLink('libraries/xtree/xtree.js');
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('AdminGroups.html');
        $tpl->SetBlock('Groups');

        $tpl->SetVariable('menubar',         $this->MenuBar('Groups'));
        $tpl->SetVariable('groups_datagrid', $this->GroupsDataGrid());
        $tpl->SetVariable('group_workarea',  $this->GroupUI());

        $save =& Piwi::CreateWidget('Button',
                                    'save',
                                    _t('GLOBAL_SAVE'),
                                    STOCK_SAVE);
        $save->AddEvent(ON_CLICK, "javascript: saveGroup();");
        $tpl->SetVariable('save', $save->Get());

        $cancel =& Piwi::CreateWidget('Button',
                                      'cancel',
                                      _t('GLOBAL_CANCEL'),
                                      STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "javascript: stopGroupAction();");
        $tpl->SetVariable('cancel', $cancel->Get());

        $tpl->SetVariable('addGroup_title',        _t('USERS_GROUPS_ADD'));
        $tpl->SetVariable('editGroup_title',       _t('USERS_GROUPS_EDIT'));
        $tpl->SetVariable('editGroupACL_title',    _t('USERS_ACLRULES'));
        $tpl->SetVariable('editGroupUsers_title',  _t('USERS_GROUPS_MEMBERS'));
        $tpl->SetVariable('incompleteGroupFields', _t('USERS_GROUPS_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('permissionsMsg',     _t('USERS_USERS_PERMISSIONS'));
        $tpl->SetVariable('confirmGroupDelete', _t('USERS_GROUPS_CONFIRM_DELETE'));
        $tpl->SetVariable('confirmResetACL',    _t('USERS_RESET_ACL_CONFIRM'));
        $tpl->SetVariable('permissionAllow',    _t('USERS_USERS_PERMISSION_ALLOW'));
        $tpl->SetVariable('permissionDeny',     _t('USERS_USERS_PERMISSION_DENY'));
        $tpl->SetVariable('permissionNone',     _t('USERS_USERS_PERMISSION_NONE'));
        $tpl->ParseBlock('Groups');

        return $tpl->Get();
    }

    /**
     * Show a form to edit a given group
     *
     * @access  public
     * @return  string HTML content
     */
    function GroupUI()
    {
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('AdminGroup.html');
        $tpl->SetBlock('group');

        // name
        $name =& Piwi::CreateWidget('Entry', 'name');
        $name->SetID('name');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('name', $name->Get());

        // title
        $title =& Piwi::CreateWidget('Entry', 'title');
        $title->SetID('title');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        // description
        $description =& Piwi::CreateWidget('TextArea', 'description');
        $description->SetID('description');
        $description->SetRows(5);
        $description->SetColumns(32);
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('description', $description->Get());

        // enabled
        $enabled =& Piwi::CreateWidget('Combo', 'enabled');
        $enabled->SetID('enabled');
        $enabled->AddOption(_t('GLOBAL_NO'),  0);
        $enabled->AddOption(_t('GLOBAL_YES'), 1);
        $enabled->SetDefault(1);
        $tpl->SetVariable('lbl_enabled', _t('GLOBAL_ENABLED'));
        $tpl->SetVariable('enabled', $enabled->Get());

        $tpl->ParseBlock('group');
        return $tpl->Get();
    }

    /**
     * Returns the group-users form
     *
     * @access  public
     * @return  string
     */
    function GroupUsersUI()
    {
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('AdminGroupUsers.html');
        $tpl->SetBlock('group_users');
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $model = new Jaws_User();

        $group_users =& Piwi::CreateWidget('CheckButtons', 'group_users');
        $group_users->setColumns(1);
        $superadmin = $GLOBALS['app']->Session->IsSuperAdmin() ? null : false;
        $users = $model->GetUsers(false, $superadmin);
        foreach ($users as $user) {
            $group_users->AddOption($user['nickname']. ' ('. $user['username']. ')',
                                    $user['id'],
                                    'user_'. $user['id']);
        }

        $tpl->SetVariable('lbl_group_users', _t('USERS_GROUPS_MARK_USERS'));
        $tpl->SetVariable('group_users', $group_users->Get());
        $tpl->ParseBlock('group_users');
        return $tpl->Get();
    }

}