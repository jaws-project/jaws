<?php
/**
 * Users  Gadget
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Actions_Users extends Users_Actions_Default
{
    /**
     * Show users management interface
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Users()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $this->AjaxMe('index.js');
        $this->gadget->define('lbl_nickname', $this::t('USERS_NICKNAME'));
        $this->gadget->define('lbl_username', $this::t('USERS_USERNAME'));
        $this->gadget->define('addUser_title', $this::t('USERS_ADD'));
        $this->gadget->define('editUser_title', $this::t('USERS_EDIT'));
        $this->gadget->define('updatePassword_title', $this::t('USERS_PASSWORD'));
        $this->gadget->define('deleteUser_title', $this::t('USERS_DELETE'));
        $this->gadget->define('editUserGroups_title', $this::t('USERS_GROUPS'));
        $this->gadget->define('incompleteUserFields', $this::t('MYACCOUNT_INCOMPLETE_FIELDS'));
        $this->gadget->define('wrongPassword', $this::t('MYACCOUNT_PASSWORDS_DONT_MATCH'));
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));

        $tpl = $this->gadget->template->load('Users.html');
        $tpl->SetBlock('Users');

        $this->SetTitle($this::t('USERS'));
        $tpl->SetVariable('title', $this::t('USERS'));

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $tpl->SetBlock('Users/encryption');
            $tpl->SetVariable('pubkey', $JCrypt->getPublic());
            $tpl->ParseBlock('Users/encryption');
        }

        $tpl->SetVariable('lbl_nickname', $this::t('USERS_NICKNAME'));
        $tpl->SetVariable('lbl_username', $this::t('USERS_USERNAME'));
        $tpl->SetVariable('lbl_email', Jaws::t('EMAIL'));
        $tpl->SetVariable('lbl_mobile', $this::t('CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_superadmin', $this::t('USERS_TYPE_SUPERADMIN'));
        $tpl->SetVariable('lbl_password', $this::t('USERS_PASSWORD'));
        $tpl->SetVariable('lbl_password_expired', $this::t('USERS_PASSWORD_EXPIRED'));
        $tpl->SetVariable('lbl_concurrents', $this::t('USERS_CONCURRENTS'));
        $tpl->SetVariable('lbl_expiry_date', $this::t('USERS_EXPIRY_DATE'));

        $tpl->SetVariable('lbl_status', Jaws::t('STATUS'));
        $statusItems = array(
            0 => $this::t('USERS_STATUS_0'),
            1 => $this::t('USERS_STATUS_1'),
            2 => $this::t('USERS_STATUS_2')
        );
        foreach ($statusItems as $val => $title) {
            $tpl->SetBlock('Users/status');
            $tpl->SetVariable('value', $val);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Users/status');
        }

        $tpl->SetVariable('lbl_save', Jaws::t('SAVE'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('lbl_ok', Jaws::t('OK'));
        $tpl->SetVariable('lbl_yes', Jaws::t('YES'));
        $tpl->SetVariable('lbl_no', Jaws::t('NO'));
        $tpl->SetVariable('lbl_add', Jaws::t('ADD'));
        $tpl->SetVariable('lbl_of', Jaws::t('OF'));
        $tpl->SetVariable('lbl_to', Jaws::t('TO'));
        $tpl->SetVariable('lbl_items', Jaws::t('ITEMS'));

        $tpl->SetVariable('addUser_title', $this::t('USERS_ADD'));
        $tpl->SetVariable('lbl_userGroups', $this::t('USERS_GROUPS'));

        // Groups
        $groups = $this->app->users->GetGroups(0, true, 'title');
        if (!Jaws_Error::IsError($groups)) {
            foreach ($groups as $group) {
                $tpl->SetBlock('Users/group');
                $tpl->SetVariable('id', $group['id']);
                $tpl->SetVariable('title', $group['title']);
                $tpl->ParseBlock('Users/group');
            }
        }

        // datagrid  filters
        $tpl->SetVariable('lbl_filter_group', $this::t('GROUPS_GROUP'));
        $tpl->SetVariable('lbl_filter_type', $this::t('USERS_TYPE'));
        $tpl->SetVariable('lbl_filter_status', Jaws::t('STATUS'));
        $tpl->SetVariable('lbl_filter_term', $this::t('USERS_SEARCH_TERM'));
        if (!Jaws_Error::IsError($groups)) {
            array_unshift($groups, array('id' => 0, 'title' => Jaws::t('ALL')));
            foreach ($groups as $group) {
                $tpl->SetBlock('Users/filterGroup');
                $tpl->SetVariable('value', $group['id']);
                $tpl->SetVariable('title', $group['title']);
                $tpl->ParseBlock('Users/filterGroup');
            }
        }

        $filterTypes = array(
            0 => Jaws::t('ALL'),
            1 => $this::t('USERS_TYPE_SUPERADMIN'),
            2 => $this::t('USERS_TYPE_NORMAL'),
        );
        foreach ($filterTypes as $key => $type) {
            $tpl->SetBlock('Users/filterType');
            $tpl->SetVariable('value', $key);
            $tpl->SetVariable('title', $type);
            $tpl->ParseBlock('Users/filterType');
        }

        $this->gadget->action->load('DatePicker')->calendar(
            $tpl,
            array('name' => 'expiry_date')
        );

        $filterTypes = array(
            -1 => Jaws::t('ALL'),
            0 => $this::t('USERS_STATUS_0'),
            1 => $this::t('USERS_STATUS_1'),
            2 => $this::t('USERS_STATUS_2'),
        );
        foreach ($filterTypes as $key => $type) {
            $tpl->SetBlock('Users/filterStatus');
            $tpl->SetVariable('value', $key);
            $tpl->SetVariable('title', $type);
            $tpl->ParseBlock('Users/filterStatus');
        }

        $tpl->ParseBlock('Users');
        return $tpl->Get();
    }

    /**
     * Return user's regions list
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetUsers()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(401);
        }
        $this->gadget->CheckPermission('ManageUsers');

        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $orderBy = array();
        if (isset($post['sortBy'])) {
            $orderBy = array($post['sortBy'] => $post['sortDirection'] == 'asc');
        }

        $group = !empty($post['filters']['group']) ? $post['filters']['group'] : false;
        $domain = !empty($post['filters']['domain']) ? $post['filters']['domain'] : false;
        $status = isset($post['filters']['status']) &&
            $post['filters']['status'] >= 0 ? $post['filters']['status'] : null;
        $term = !empty($post['filters']['term']) ? $post['filters']['term'] : null;
        $superadmin = null;
        if (!empty($post['filters']['type'])) {
            if ($post['filters']['type'] == 1) {
                $superadmin = true;
            } else {
                $superadmin = false;
            }
        }

        $users = $this->gadget->model->load('Users')->getUsers(
            $domain, $group,
            array(
                'term' => $term,
                'status' => $status,
                'superadmin' => $superadmin,
            ),
            array(
                'default' => true,
                'account' => true,
            ),
            $orderBy,
            $post['limit'], $post['offset']
        );
        if (Jaws_Error::IsError($users)) {
            return $this->gadget->session->response(
                $users->getMessage(),
                RESPONSE_ERROR
            );
        }
        $total = $this->gadget->model->load('Users')->getUsersCount(
            $domain, $group,
            array(
                'term' => $term,
                'status' => $status,
                'superadmin' => $superadmin,
            )
        );
        if (Jaws_Error::IsError($total)) {
            return $this->gadget->session->response(
                $total->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total'   => $total,
                'records' => $users
            )
        );
    }

    /**
     * Get a new info
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function GetUser()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $post = $this->gadget->request->fetch(array('id', 'account', 'personal') , 'post');

        $profile = $this->gadget->model->load('User')->getUser(
            (int)$post['id'],
            0,
            array(
                'default'  => true,
                'account'  => (bool)$post['account'],
                'personal' => (bool)$post['personal']
            )
        );
        if (Jaws_Error::IsError($profile)) {
            return array();
        }

        $objDate = Jaws_Date::getInstance();
        if ($post['account']) {
            if (!empty($profile['expiry_date'])) {
                $profile['expiry_date'] = $objDate->Format($profile['expiry_date'], 'Y/m/d');
            } else {
                $profile['expiry_date'] = '';
            }
        }

        if ($post['personal']) {
            if (empty($profile['avatar'])) {
                $profile['avatar'] = $this->app->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
            } else {
                $profile['avatar'] = $this->app->getDataURL(). 'avatar/'. $profile['avatar'];
            }

            if (!empty($profile['dob'])) {
                $profile['dob'] = $objDate->Format($profile['dob'], 'Y-m-d');
            } else {
                $profile['dob'] = '';
            }
        }

        return $profile;
    }

    /**
     * Adds a new user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddUser()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $uData = $this->gadget->request->fetch('data:array', 'post');

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $uData['password'] = $JCrypt->decrypt($uData['password']);
        }

        $uData['concurrents'] = (int)$uData['concurrents'];
        $uData['superadmin'] = ($uData['superadmin'] == 1) ? true : false;
        $uData['superadmin'] = $this->app->session->user->superadmin? (bool)$uData['superadmin'] : false;
        $uData['status'] = (int)$uData['status'];

        $res = $this->gadget->model->load('User')->addUser($uData);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $guid = $this->gadget->registry->fetch('anon_group');
            if (!empty($guid)) {
                $this->app->users->AddUserToGroup($res, (int)$guid);
            }
            return $this->gadget->session->response($this::t('USERS_CREATED', $uData['username']), RESPONSE_NOTICE);
        }
    }

    /**
     * Updates user information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateUser()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $post = $this->gadget->request->fetch(array('data:array', 'uid'), 'post');
        $uData = $post['data'];
        $uData['concurrents'] = (int)$uData['concurrents'];
        $uData['superadmin'] = ($uData['superadmin'] == 1) ? true : false;

        if ($post['uid'] == $this->app->session->user->id) {
            unset($uData['status'], $uData['superadmin'], $uData['expiry_date']);
        } else {
            $uData['status'] = (int)$uData['status'];
            if (!$this->app->session->user->superadmin) {
                unset($uData['status'], $uData['superadmin'], $uData['expiry_date']);
            }
        }

        $res = $this->gadget->model->load('User')->editUser($post['uid'], $uData);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
        } else {
            // send activate notification
            if ($uData['prev_status'] == 2 && $uData['status'] == 1) {
                $uRegistration = $this->gadget->action->load('Registration');
                $uRegistration->ActivateNotification($uData, $this->gadget->registry->fetch('anon_activation'));
            }
            return $this->gadget->session->response($this::t('USERS_UPDATED', $uData['username']), RESPONSE_NOTICE);
        }
    }

    /**
     * Updates user password
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateUserPassword()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $postedData = $this->gadget->request->fetch(array('uid', 'password', 'expired'), 'post');

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $postedData['password'] = $JCrypt->decrypt($postedData['password']);
        }

        $result = $this->gadget->model->load('User')->updatePassword(
            (int)$postedData['uid'],
            $postedData['password'],
            false,
            (bool)$postedData['expired']
        );
        if (Jaws_Error::isError($result)) {
            return $this->gadget->session->response($result->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response($this::t('USERS_PASSWORD_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Delete a user
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DeleteUser()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $uid = $this->gadget->request->fetch('id', 'post');
        if ($uid == $this->app->session->user->id) {
            return $this->gadget->session->response(
                $this::t('USERS_CANT_DELETE_SELF'),
                RESPONSE_ERROR
            );
        }

        $profile = $this->gadget->model->load('User')->getUser((int)$uid);
        if (!$this->app->session->user->superadmin && $profile['superadmin']) {
            return $this->gadget->session->response(
                $this::t('USERS_CANT_DELETE', $profile['username']),
                RESPONSE_ERROR
            );
        }

        $uModel = Jaws_User::getInstance();
        if (!$this->gadget->model->load('User')->deleteUser($uid)) {
            return $this->gadget->session->response(
                $this::t('USERS_CANT_DELETE', $profile['username']),
                RESPONSE_ERROR
            );
        } else {
            return $this->gadget->session->response(
                $this::t('USER_DELETED', $profile['username']),
                RESPONSE_NOTICE
            );
        }
    }

    /**
     * Gets the user-groups data
     *
     * @access  public
     * @return  array   Groups data
     */
    function GetUserGroups()
    {
        $this->gadget->CheckPermission('ManageGroups');

        $uid = $this->gadget->request->fetch('uid', 'post');
        $groups = $this->gadget->model->load('Groups')->getGroups(0, 0, (int)$uid);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }

        return array_column($groups, 'id');
    }

    /**
     * Adds a user to groups
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddUserToGroups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $post = $this->gadget->request->fetch(array('uid', 'groups:array'), 'post');
        $uModel = Jaws_User::getInstance();
        $oldGroups = $uModel->GetGroupsOfUser((int)$post['uid']);
        if (!Jaws_Error::IsError($oldGroups)) {
            $oldGroups = array_keys($oldGroups);
            foreach ($post['groups'] as $group) {
                if (false === $gIndex = array_search($group, $oldGroups)) {
                    $uModel->AddUserToGroup($post['uid'], $group);
                } else {
                    unset($oldGroups[$gIndex]);
                }
            }

            // delete remainder groups
            foreach ($oldGroups as $group) {
                $uModel->DeleteUserFromGroup($post['uid'], $group);
            }

            return $this->gadget->session->response($this::t('GROUPS_UPDATED_USERS'),
                RESPONSE_NOTICE);
        } else {
            return $this->gadget->session->response($oldGroups->GetMessage(),
                RESPONSE_ERROR);
        }
    }

}