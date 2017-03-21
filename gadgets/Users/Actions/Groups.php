<?php
/**
 * Users  Gadget
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Actions_Groups extends Users_Actions_Default
{
    /**
     * Show GatewayManager interface
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Groups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->AjaxMe('index.js');
        $this->gadget->define('lbl_title', _t('GLOBAL_TITLE'));
        $this->gadget->define('lbl_name', _t('GLOBAL_NAME'));
        $this->gadget->define('lbl_add', _t('GLOBAL_ADD'));
        $this->gadget->define('lbl_delete', _t('GLOBAL_DELETE'));
        $this->gadget->define('addGroup_title', _t('USERS_GROUPS_ADD'));
        $this->gadget->define('editGroup_title', _t('USERS_GROUPS_EDIT'));
        $this->gadget->define('editGroupUsers_title', _t('USERS_GROUPS_MEMBERS'));
        $this->gadget->define('incompleteGroupFields', _t('USERS_GROUPS_INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmDelete', _t('GLOBAL_CONFIRM_DELETE'));

        $tpl = $this->gadget->template->load('ManageGroups.html');
        $tpl->SetBlock('Groups');

        $this->SetTitle(_t('USERS_GROUPS'));
        $tpl->SetVariable('title', _t('USERS_GROUPS'));

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        $tpl->SetVariable('grid_header', _t('USERS_GROUPS'));
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('lbl_enabled', _t('GLOBAL_ENABLED'));

        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_add', _t('GLOBAL_ADD'));

        // Users
        $uModel = new Jaws_User();
        $superadmin = $GLOBALS['app']->Session->IsSuperAdmin() ? null : false;
        $users = $uModel->GetUsers(false, $superadmin);
        if (!Jaws_Error::IsError($users)) {
            foreach ($users as $user) {
                $tpl->SetBlock('Groups/user');
                $tpl->SetVariable('id', $user['id']);
                $tpl->SetVariable('title', $user['nickname']. ' ('. $user['username']. ')');
                $tpl->ParseBlock('Groups/user');
            }
        }

        $tpl->ParseBlock('Groups');
        return $tpl->Get();
    }

    /**
     * Return group's regions list
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetGroups()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(401);
        }
        $this->gadget->CheckPermission('ManageGroups');
        $post = jaws()->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $uModel = new Jaws_User();
        $groups = $uModel->GetGroups(0, null, 'name', $post['limit'], $post['offset']);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }
        $total = $uModel->GetGroupsCount(0, null);

        return array(
            'status' => 'success',
            'total' => $total,
            'records' => $groups
        );
    }

    /**
     * Get a new info
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function GetGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $id = (int)jaws()->request->fetch('id' , 'post');

        $uModel = new Jaws_User();
        $gInfo = $uModel->GetGroup($id);
        if (Jaws_Error::IsError($profile)) {
            return array();
        }

        return $gInfo;
    }

    /**
     * Adds a new group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddGlobalGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $gData = jaws()->request->fetch('data:array', 'post');
        $gData['enabled'] = ($gData['enabled'] == 1) ? true : false;

        $uModel = new Jaws_User();
        $res = $uModel->AddGroup($gData);

        if (Jaws_Error::isError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_GROUPS_CREATED', $gData['title']), RESPONSE_NOTICE);
        }
    }

    /**
     * Updates group information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGlobalGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $post = jaws()->request->fetch(array('data:array', 'id'), 'post');
        $gData = $post['data'];
        $gData['enabled'] = ($gData['enabled'] == 1) ? true : false;

        $uModel = new Jaws_User();
        $res = $uModel->UpdateGroup($post['id'], $gData);
        if (Jaws_Error::isError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_GROUPS_UPDATED', $gData['title']), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete a group
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DeleteGlobalGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $gid = (int)jaws()->request->fetch('id', 'post');
        $uModel = new Jaws_User();
        $groupinfo = $uModel->GetGroup((int)$gid);
        if (!$uModel->DeleteGroup($gid)) {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_GROUPS_CANT_DELETE', $groupinfo['name']),
                RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_GROUPS_DELETED', $groupinfo['name']),
                RESPONSE_NOTICE);
        }
    }

    /**
     * Gets the group-users array
     *
     * @access  public
     * @return  array   List of users
     */
    function GetGroupUsers()
    {
        $gid = jaws()->request->fetch('gid', 'post');
        $uModel = new Jaws_User();
        $users = $uModel->GetUsers((int)$gid);
        if (Jaws_Error::IsError($users)) {
            return array();
        }

        return $users;
    }

    /**
     * Adds a group of users(by their IDs) to a certain group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddUsersToGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $post = jaws()->request->fetch(array('gid', 'users:array'), 'post');
        $uModel = $this->gadget->model->loadAdmin('UsersGroup');
        $res = $uModel->AddUsersToGroup((int)$post['gid'], $post['users']);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_GROUPS_UPDATED_USERS'), RESPONSE_NOTICE);
        }
    }

}