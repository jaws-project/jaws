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
        $this->gadget->define('lbl_title', Jaws::t('TITLE'));
        $this->gadget->define('lbl_name', Jaws::t('NAME'));
        $this->gadget->define('lbl_add', Jaws::t('ADD'));
        $this->gadget->define('lbl_delete', Jaws::t('DELETE'));
        $this->gadget->define('addGroup_title', $this::t('GROUPS_ADD'));
        $this->gadget->define('editGroup_title', $this::t('GROUPS_EDIT'));
        $this->gadget->define('editGroupUsers_title', $this::t('GROUPS_MEMBERS'));
        $this->gadget->define('incompleteGroupFields', $this::t('GROUPS_INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));

        $tpl = $this->gadget->template->load('ManageGroups.html');
        $tpl->SetBlock('Groups');

        $this->title = $this::t('GROUPS');
        $tpl->SetVariable('title', $this::t('GROUPS'));

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        $tpl->SetVariable('grid_header', $this::t('GROUPS'));
        $tpl->SetVariable('lbl_name', Jaws::t('NAME'));
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_description', Jaws::t('DESCRIPTION'));
        $tpl->SetVariable('lbl_enabled', Jaws::t('ENABLED'));

        $tpl->SetVariable('lbl_save', Jaws::t('SAVE'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('lbl_ok', Jaws::t('OK'));
        $tpl->SetVariable('lbl_yes', Jaws::t('YESS'));
        $tpl->SetVariable('lbl_no', Jaws::t('NOO'));
        $tpl->SetVariable('lbl_add', Jaws::t('ADD'));

        // Users
        $users = $this->gadget->model->load('User')->list(
            0, 0,
            array(
                'superadmin' => $this->app->session->user->superadmin? null : false
            )
        );
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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(401);
        }
        $this->gadget->CheckPermission('ManageGroups');
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        if (!$this->app->session->user->superadmin) {
            // check user group access
            $groups = $this->gadget->model->load('UserGroup')->getGrantedGroups($post['filters']['filter_term']);
            $total = count($groups);
        } else {
            $groups = $this->gadget->model->load('Group')->list(
                0, 0, 0,
                array(), array(),
                array('id' => true),
                $post['limit'],
                $post['offset']
            );
            if (Jaws_Error::IsError($groups)) {
                return array();
            }
            $total = $this->gadget->model->load('Groups')->listCount(0, 0, 0, array());
        }

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
        $id = (int)$this->gadget->request->fetch('id' , 'post');

        $gInfo = $this->gadget->model->load('Group')->get($id);
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
        $gData = $this->gadget->request->fetch('data:array', 'post');
        $gData['enabled'] = ($gData['enabled'] == 1) ? true : false;

        $res = $this->gadget->model->load('Group')->add($gData);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('GROUPS_CREATED', $gData['title']), RESPONSE_NOTICE);
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
        $post = $this->gadget->request->fetch(array('data:array', 'id'), 'post');
        $gData = $post['data'];
        $gData['enabled'] = ($gData['enabled'] == 1) ? true : false;

        $res = $this->gadget->model->load('Group')->update($post['id'], $gData);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('GROUPS_UPDATED', $gData['title']), RESPONSE_NOTICE);
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
        $gid = (int)$this->gadget->request->fetch('id', 'post');
        $groupinfo = $this->gadget->model->load('Group')->get((int)$gid);
        if (!$this->gadget->model->load('Group')->delete($gid)) {
            return $this->gadget->session->response($this::t('GROUPS_CANT_DELETE', $groupinfo['name']),
                RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('GROUPS_DELETED', $groupinfo['name']),
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
        $gid = $this->gadget->request->fetch('gid', 'post');
        $users = $this->gadget->model->load('User')->list(0, (int)$gid);
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
        $post = $this->gadget->request->fetch(array('gid', 'users:array'), 'post');
        $uModel = $this->gadget->model->loadAdmin('UsersGroup');
        $res = $uModel->AddUsersToGroup((int)$post['gid'], $post['users']);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('GROUPS_UPDATED_USERS'), RESPONSE_NOTICE);
        }
    }

}