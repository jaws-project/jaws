<?php
/**
 * Groups Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Groups
 */
class Users_Actions_Admin_Groups extends Users_Actions_Admin_Default
{
    /**
     * Builds group administration UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Groups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));
        $this->gadget->define('datagridNoItems', Jaws::t('NOTFOUND'));
        $this->gadget->define('noMatchesMessage', Jaws::t('COMBO_NO_MATCH_MESSAGE'));
        $this->gadget->define('confirmGroupDelete', $this::t('USER_CONFIRM_DELETE'));
        $this->gadget->define('incompleteGroupFields', $this::t('MYACCOUNT_INCOMPLETE_FIELDS'));
        $this->gadget->define('wrongPassword', $this::t('MYACCOUNT_PASSWORDS_DONT_MATCH'));
        $this->gadget->define('LANGUAGE', array(
            'title'=> Jaws::t('TITLE'),
            'name'=> Jaws::t('NAME'),
            'enabled'=> Jaws::t('ENABLED'),
            'group_members'=> $this::t('GROUPS_MEMBERS'),
            'nickname'=> $this::t('USERS_NICKNAME'),
            'username'=> $this::t('USERS_USERNAME'),
            'yes'=> Jaws::t('YES'),
            'no'=> Jaws::t('NO'),
            'view'=> Jaws::t('VIEW'),
            'edit'=> Jaws::t('EDIT'),
            'delete'=> Jaws::t('DELETE'),
            'acls'=> $this::t('ACLS'),
            'acl_key_title'=> $this::t('ACLS_KEY_TITLE'),
            'acl'=> $this::t('ACL'),
            'components'=> $this::t('ACLS_COMPONENTS'),
            'acl_allow'=> $this::t('ACLS_ACCESS_YES'),
            'acl_deny'=> $this::t('ACLS_ACCESS_NO'),
            'add_group_title'=> $this::t('GROUPS_ADD'),
            'edit_group_title'=> $this::t('GROUPS_EDIT'),
        ));
        $assigns = array();
        $assigns['menubar'] =  empty($menubar)? $this->MenuBar('Groups') : $menubar;
        if ($this->gadget->registry->fetch('multi_domain') == 'true') {
            $assigns['domains'] = $this->gadget->model->load('Domains')->getDomains();
        }

        $assigns['components'] = Jaws_Gadget::getInstance('Components')
            ->model->load('Gadgets')
            ->GetGadgetsList(null, true, true);

        return $this->gadget->template->xLoadAdmin('Groups.html')->render($assigns);
    }

    /**
     * Prepares list of groups for datagrid
     *
     * @access  public
     * @return  array  Grid data
     */
    function GetGroups()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );
        $filters = array(
            'term' => $post['filters']['filter_term']
        );

        $uModel = $this->gadget->model->load('Groups');
        $groups = $uModel->getGroups(
            (int)@$post['filters']['filter_domain'],
            0,
            0,
            $filters,
            array(),
            $post['sortBy'],
            $post['limit'],
            $post['offset']
        );
        if (Jaws_Error::IsError($groups)) {
            return $this->gadget->session->response($groups->GetMessage(), RESPONSE_ERROR);
        }

        $groupsCount = $uModel->getGroupsCount(
            (int)@$post['filters']['filter_domain'],
            0,
            0,
            $filters
        );
        if (Jaws_Error::IsError($groupsCount)) {
            return $this->gadget->session->response($groupsCount->GetMessage(), RESPONSE_ERROR);
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
     *
     * Get a group info
     *
     * @access  public
     * @return  void
     */
    function GetGroup()
    {
        $gid = (int)$this->gadget->request->fetch('gid:integer', 'post');
        $gInfo = $this->app->users->GetGroup($gid);
        if (Jaws_Error::IsError($gInfo)) {
            return $this->gadget->session->response($gInfo->getMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response('', RESPONSE_NOTICE, $gInfo);
    }

    /**
     * Adds a new group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $data = $this->gadget->request->fetch('data:array', 'post');
        $res = $this->app->users->AddGroup($data);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($res->getMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            $this::t('GROUPS_CREATED', $data['title']),
            RESPONSE_NOTICE
        );
    }

    /**
     * Update a group info
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $post = $this->gadget->request->fetch(array('gid', 'data:array'), 'post');
        $res = $this->app->users->UpdateGroup((int)$post['gid'], $post['data']);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($res->getMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            $this::t('GROUPS_UPDATED', $post['data']['title']),
            RESPONSE_NOTICE
        );
    }

    /**
     * Deletes the group(s)
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteGroups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $gids = $this->gadget->request->fetch('gids:array', 'post');
        $gids = is_array($gids) ? $gids : array($gids);

        $errors = 0;
        foreach ($gids as $gid) {
            if (!$this->app->users->DeleteGroup($gid)) {
                $errors++;
                continue;
            }
        }

        return $this->gadget->session->response(
            $this::t('GROUPS_DELETED', count($gids) - $errors, count($gids)),
            RESPONSE_NOTICE
        );
    }

    /**
     * Gets the group-users data
     *
     * @access  public
     * @return  array   Groups data
     */
    function GetGroupUsers()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $model = $this->gadget->model->load('Users');
        $users = $model->getUsers(
            0,
            (int)$post['filters']['gid'],
            array(),
            array(),
            $post['sortBy'],
            $post['limit'],
            $post['offset']
        );
        if (Jaws_Error::IsError($users)) {
            return $this->gadget->session->response($users->getMessage(), RESPONSE_ERROR);
        }

        $usersCount = $model->getUsersCount(
            0,
            (int)$post['filters']['gid']
        );
        if (Jaws_Error::IsError($usersCount)) {
            return $this->gadget->session->response($usersCount->getMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $usersCount,
                'records' => $users
            )
        );
    }

    /**
     * Updates modified group ACL keys
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGroupACL()
    {
        $this->gadget->CheckPermission('ManageUserACLs');
        $post = $this->gadget->request->fetch(array('gid', 'component', 'acls:array'), 'post');
        $res = $this->app->acl->deleteByGroup((int)$post['gid'], $post['component']);
        if ($res) {
            $res = $this->app->acl->insertAll($post['acls'], $post['component'], 0, (int)$post['gid']);
        }
        if (!$res) {
            return $this->gadget->session->response($this::t('GROUP_ACL_NOT_UPDATED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            $this::t('GROUP_ACL_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Delete users from group
     *
     * @access  public
     * @return  array   Groups data
     */
    function DeleteUsersFromGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $post = $this->gadget->request->fetch(array('gid', 'userIds:array'), 'post');

        foreach ((array)$post['userIds'] as $uid) {
            $res = $this->app->users->DeleteUserFromGroup($uid, (int)$post['gid']);
            if (Jaws_Error::IsError($res)) {
                return $this->gadget->session->response($res->getMessage(), RESPONSE_ERROR);
            }
        }
        return $this->gadget->session->response($this::t('USERS_DELETED_FROM_GROUP'), RESPONSE_NOTICE);
    }


    /**
     * Delete a group ACLs key
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteGroupACLs()
    {
        $this->gadget->CheckPermission('ManageUserACLs');
        $post = $this->gadget->request->fetch(array('gid', 'acls:array'), 'post');
        if (empty((int)$post['gid'])) {
            return $this->gadget->session->response($this::t('GROUP_ACL_NOT_DELETED'), RESPONSE_ERROR);
        }

        foreach ((array)$post['acls'] as $acl) {
            if (empty($acl['component']) || empty($acl['key_name'])) {
                continue;
            }
            $res = $this->app->acl->deleteByGroup(
                (int)$post['gid'],
                $acl['component'],
                $acl['key_name'],
                $acl['subkey']
            );
            if (!$res) {
                return $this->gadget->session->response($this::t('GROUP_ACL_NOT_DELETED'), RESPONSE_ERROR);
            }
        }

        return $this->gadget->session->response(
            $this::t('GROUP_ACL_DELETED'),
            RESPONSE_NOTICE
        );
    }

}