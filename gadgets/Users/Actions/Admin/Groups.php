<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_Groups extends Users_Actions_Admin_Default
{
    /**
     * Get groups list
     *
     * @access  public
     * @return  JSON
     */
    function GetGroups()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );
        $groups = $this->app->users->GetGroups(
            0,
            null,
            $post['sortBy'],
            $post['limit'],
            $post['offset']
        );
        if (Jaws_Error::IsError($groups)) {
            return $this->gadget->session->response(
                $groups->getMessage(),
                RESPONSE_ERROR
            );
        }
        $groupsCount = $this->app->users->GetGroupsCount(0, null);
        if (Jaws_Error::IsError($groupsCount)) {
            return $this->gadget->session->response(
                $groupsCount->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $groupsCount,
                'records' => $groups
            )
        );
    }

    /**
     * Builds groups datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function GroupsDataGrid()
    {
        $uModel = new Jaws_User();
        $total = $uModel->GetGroupsCount();

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->pageBy(12);
        $datagrid->SetID('groups_datagrid');
        $col = Piwi::CreateWidget('Column', Jaws::t('TITLE'), null, false);
        $datagrid->AddColumn($col);
        $column1 = Piwi::CreateWidget('Column', Jaws::t('NAME'), null, false);
        $column1->SetStyle('width: 120px;');
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', Jaws::t('ACTIONS'), null, false);
        $column2->SetStyle('width: 120px;');
        $datagrid->AddColumn($column2);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');

        return $datagrid->Get();
    }


    /**
     * Builds the group management UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Groups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');
        // set default value of javascript variables
        $this->gadget->define('addGroup_title', $this::t('GROUPS_ADD'));
        $this->gadget->define('editGroup_title', $this::t('GROUPS_EDIT'));
        $this->gadget->define('editACL_title', $this::t('ACLS'));
        $this->gadget->define('editGroupUsers_title', $this::t('GROUPS_MEMBERS'));
        $this->gadget->define('incompleteGroupFields', $this::t('GROUPS_INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmGroupDelete', $this::t('GROUPS_CONFIRM_DELETE'));

        $tpl = $this->gadget->template->loadAdmin('Groups.html');
        $tpl->SetBlock('Groups');

        $tpl->SetVariable('menubar',         $this->MenuBar('Groups'));
        $tpl->SetVariable('groups_datagrid', $this->GroupsDataGrid());
        $tpl->SetVariable('workarea',  $this->GroupUI());

        $save =& Piwi::CreateWidget('Button',
                                    'save',
                                    Jaws::t('SAVE'),
                                    STOCK_SAVE);
        $save->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Users').saveGroup();");
        $tpl->SetVariable('save', $save->Get());

        $cancel =& Piwi::CreateWidget('Button',
                                      'cancel',
                                      Jaws::t('CANCEL'),
                                      STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Users').stopGroupAction();");
        $tpl->SetVariable('cancel', $cancel->Get());
        $tpl->ParseBlock('Groups');

        return $tpl->Get();
    }

    /**
     * Builds a form to edit group
     *
     * @access  public
     * @return  string  XHTML form
     */
    function GroupUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Group.html');
        $tpl->SetBlock('group');

        // name
        $name =& Piwi::CreateWidget('Entry', 'name');
        $name->SetID('name');
        $tpl->SetVariable('lbl_name', Jaws::t('NAME'));
        $tpl->SetVariable('name', $name->Get());

        // title
        $title =& Piwi::CreateWidget('Entry', 'title');
        $title->SetID('title');
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('title', $title->Get());

        // description
        $description =& Piwi::CreateWidget('TextArea', 'description');
        $description->SetID('description');
        $description->SetRows(5);
        $description->SetColumns(32);
        $tpl->SetVariable('lbl_description', Jaws::t('DESCRIPTION'));
        $tpl->SetVariable('description', $description->Get());

        // enabled
        $enabled =& Piwi::CreateWidget('Combo', 'enabled');
        $enabled->SetID('enabled');
        $enabled->AddOption(Jaws::t('NO'),  0);
        $enabled->AddOption(Jaws::t('YES'), 1);
        $enabled->SetDefault(1);
        $tpl->SetVariable('lbl_enabled', Jaws::t('ENABLED'));
        $tpl->SetVariable('enabled', $enabled->Get());

        $tpl->ParseBlock('group');
        return $tpl->Get();
    }

    /**
     * Builds the group-users form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function GroupUsersUI()
    {
        $tpl = $this->gadget->template->loadAdmin('GroupUsers.html');
        $tpl->SetBlock('group_users');
        $model = new Jaws_User();

        $group_users =& Piwi::CreateWidget('CheckButtons', 'group_users');
        $group_users->setColumns(1);
        $superadmin = $this->app->session->user->superadmin ? null : false;
        $users = $model->GetUsers(false, false, $superadmin);
        foreach ($users as $user) {
            $group_users->AddOption($user['nickname']. ' ('. $user['username']. ')',
                                    $user['id'],
                                    'user_'. $user['id']);
        }

        $tpl->SetVariable('lbl_group_users', $this::t('GROUPS_MARK_USERS'));
        $tpl->SetVariable('group_users', $group_users->Get());
        $tpl->ParseBlock('group_users');
        return $tpl->Get();
    }

}