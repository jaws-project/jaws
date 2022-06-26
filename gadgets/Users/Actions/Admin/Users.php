<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_Users extends Users_Actions_Admin_Default
{
    /**
     * Builds user administration UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Users()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $this->AjaxMe('script.js');
        $this->AjaxMe('script-users.js');
        $this->gadget->define('is_superadmin', $this->app->session->user->superadmin);
        $statusItems = array(
            0 => $this::t('USERS_STATUS_0'),
            1 => $this::t('USERS_STATUS_1'),
            2 => $this::t('USERS_STATUS_2'),
        );
        $this->gadget->define('statusItems', $statusItems);

        $assigns = array();
        $assigns['menubar'] =  empty($menubar)? $this->MenuBar('Users') : $menubar;
        $assigns['statusItems'] = $statusItems;
        $assigns['types'] = array(
            0 => $this::t('USERS_TYPE_NORMAL'),
            1 => $this::t('USERS_TYPE_SUPERADMIN'),
        );
        $assigns['expiry_date'] = $this->gadget->action->load('DatePicker')->xcalendar(array('name' => 'expiry_date'));
        $assigns['dob'] = $this->gadget->action->load('DatePicker')->xcalendar(array('name' => 'dob'));

        if ($this->gadget->registry->fetch('multi_domain') == 'true') {
            $assigns['domains'] = $this->gadget->model->load('Domains')->getDomains();
        }
        $assigns['components'] = Jaws_Gadget::getInstance('Components')
            ->model->load('Gadgets')
            ->GetGadgetsList(null, true, true);

        // province
        $zModel = Jaws_Gadget::getInstance('Settings')->model->load('Zones');
        $assigns['provinces'] = $zModel->GetProvinces(364);

        // usecrypt
        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $assigns['pubkey'] = $JCrypt->getPublic();
            $assigns['usecrypt_selected'] = empty($reqpost['pubkey']) || !empty($reqpost['usecrypt']);
//            $this->gadget->define('pubkey', $JCrypt->getPublic());
        }

        $assigns['is_superadmin'] = $this->app->session->user->superadmin;
        if (!$this->app->session->user->superadmin) {
            $assigns['granted_groups'] = $this->gadget->model->load('UserGroup')->getGrantedGroups();
        }

        return $this->gadget->template->xLoadAdmin('Users.html')->render($assigns);
    }

    /**
     * Prepares list of users for datagrid
     *
     * @access  public
     * @return  array  Grid data
     */
    function GetUsers()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );
        $filters = array();
        $filters['term'] = @$post['filters']['filter_term'];
        if (isset($post['filters']['filter_type'])) {
            if ((int)$post['filters']['filter_type'] === 0) {
                $filters['superadmin'] = false;
            } elseif ((int)$post['filters']['filter_type'] === 1) {
                $filters['superadmin'] = true;
            }
        }
        if (isset($post['filters']['filter_status']) && (int)$post['filters']['filter_status'] >= 0) {
            $filters['status'] = (int)$post['filters']['filter_status'];
        }

        // check group filter
        if (!$this->app->session->user->superadmin) {
            // check user group access
            $groups = $this->gadget->model->load('UserGroup')->getGrantedGroups($post['filters']['filter_term']);
            $grantedGroup = $groups[0]['id'];
            foreach ($groups as $group) {
                if ((int)$post['filters']['filter_group'] === $group['id']) {
                    $grantedGroup = $group['id'];
                }
            }
            $post['filters']['filter_group'] = $grantedGroup;
        }

        $users = $this->gadget->model->load('User')->list(
            (int)@$post['filters']['filter_domain'],
            (int)@$post['filters']['filter_group'],
            $filters,
            array(),
            $post['sortBy'],
            $post['limit'],
            $post['offset']
        );
        if (Jaws_Error::IsError($users)) {
            return $this->gadget->session->response($users->GetMessage(), RESPONSE_ERROR);
        }

        $usersCount = $this->gadget->model->load('User')->listCount(
            (int)@$post['filters']['filter_domain'],
            (int)@$post['filters']['filter_group'],
            $filters
        );
        if (Jaws_Error::IsError($usersCount)) {
            return $this->gadget->session->response($usersCount->GetMessage(), RESPONSE_ERROR);
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
     *
     * Get an user info
     *
     * @access  public
     * @return  void
     */
    function GetUser()
    {
        $post = $this->gadget->request->fetch(array('id:integer', 'account:bool', 'personal:bool'), 'post');
        $userInfo = $this->gadget->model->load('User')->get(
            (int)$post['id'],
            0,
            array('account' => (bool)$post['account'], 'personal' => (bool)$post['personal'])
        );
        if (Jaws_Error::IsError($userInfo)) {
            return $this->gadget->session->response($userInfo->getMessage(), RESPONSE_ERROR);
        }

        $objDate = Jaws_Date::getInstance();
        if (isset($userInfo['dob']) && !empty($userInfo['dob'])) {
            $userInfo['dob'] = $objDate->Format($userInfo['dob'], 'Y/m/d');
        }

        if (!empty($userInfo['expiry_date'])) {
            $userInfo['expiry_date'] = $objDate->Format($userInfo['expiry_date'], 'Y/m/d');
        }

        if (!isset($userInfo['avatar']) && empty($userInfo['avatar'])) {
            $userInfo['avatar'] = $this->app->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
        } else {
            $userInfo['avatar'] = $this->app->getDataURL(). 'avatar/'. $userInfo['avatar'];
        }

        return $this->gadget->session->response('', RESPONSE_NOTICE, $userInfo);
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
        $uData['concurrents'] = (int)$uData['concurrents'];
        $uData['superadmin'] = $uData['superadmin'] == 1;

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $uData['password'] = $JCrypt->decrypt($uData['password']);
        }

        $selectedGroup = (int)@$uData['group'];
        if (!$this->app->session->user->superadmin &&
            (empty($selectedGroup) || !$this->gadget->GetPermission('GroupManage', $selectedGroup))
        ) {
            return $this->gadget->session->response(
                'access denied to selected group',
                RESPONSE_ERROR
            );
        }

        unset($uData['group']);
        $uData['status'] = (int)$uData['status'];
        $uData['superadmin'] = $this->app->session->user->superadmin? (bool)$uData['superadmin'] : false;
        $res = $this->gadget->model->load('User')->add($uData);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($res->getMessage(), RESPONSE_ERROR);
        }

        if ($this->app->session->user->superadmin) {
            $guid = $this->gadget->registry->fetch('anon_group');
        } else {
            $guid = $selectedGroup;
        }

        if (!empty($guid)) {
            $this->gadget->model->load('UserGroup')->add($res, (int)$guid);
        }
        return $this->gadget->session->response($this::t('USERS_CREATED', $uData['username']), RESPONSE_NOTICE);
    }

    /**
     * Update user account information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateUser()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $post = $this->gadget->request->fetch(array('id', 'data:array'), 'post');
        $uData = $post['data'];

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $uData['password'] = $JCrypt->decrypt($uData['password']);
        }

        if ((int)$post['id'] == $this->app->session->user->id) {
            unset($uData['status'], $uData['superadmin'], $uData['expiry_date']);
        } else {
            $uData['status'] = (int)$uData['status'];
        }

        $userInfo = $this->gadget->model->load('User')->get((int)$post['id']);
        if (Jaws_Error::IsError($userInfo)) {
            return $this->gadget->session->response($userInfo->getMessage(), RESPONSE_ERROR);
        }
        if (empty($userInfo)) {
            return $this->gadget->session->response($this::t('USERS_USER_NOT_EXIST'), RESPONSE_ERROR);
        }

        $res = $this->gadget->model->load('User')->update((int)$post['id'], $uData);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($res->getMessage(), RESPONSE_ERROR);
        } else {
            // send activate notification
            if ($userInfo['status'] == 2 && $uData['status'] == 1) {
                $uRegistration = $this->gadget->action->load('Registration');
                $uRegistration->ActivateNotification($uData, $this->gadget->registry->fetch('anon_activation'));
            }
            return $this->gadget->session->response($this::t('USERS_UPDATED', $uData['username']), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete a user ACLs key
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteUserACLs()
    {
        $this->gadget->CheckPermission('ManageUserACLs');
        $post = $this->gadget->request->fetch(array('uid', 'acls:array'), 'post');
        if (empty((int)$post['uid'])) {
            return $this->gadget->session->response($this::t('USER_ACL_NOT_DELETED'), RESPONSE_ERROR);
        }

        foreach ((array)$post['acls'] as $acl) {
            if (empty($acl['component']) || empty($acl['key_name'])) {
                continue;
            }
            $res = $this->app->acl->deleteByUser(
                (int)$post['uid'],
                $acl['component'],
                $acl['key_name'],
                $acl['subkey']
            );
            if (!$res) {
                return $this->gadget->session->response($this::t('USER_ACL_NOT_DELETED'), RESPONSE_ERROR);
            }
        }

        return $this->gadget->session->response(
            $this::t('USER_ACL_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Updates modified user ACL keys
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateUserACL()
    {
        $this->gadget->CheckPermission('ManageUserACLs');
        $post = $this->gadget->request->fetch(array('uid', 'component', 'acls:array'), 'post');
        $res = $this->app->acl->deleteByUser((int)$post['uid'], $post['component']);
        if ($res) {
            $res = $this->app->acl->insertAll($post['acls'], $post['component'], (int)$post['uid']);
        }
        if (!$res) {
            return $this->gadget->session->response($this::t('USER_ACL_NOT_UPDATED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            $this::t('USER_ACL_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Gets the user-groups data
     *
     * @access  public
     * @return  array   Groups data
     */
    function GetUserGroups()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $groups = $this->gadget->model->load('Group')->list(
            0,
            0,
            (int)$post['filters']['uid'],
            array(),
            array('default' => true),
            $post['sortBy'],
            $post['limit'],
            $post['offset']
        );
        if (Jaws_Error::IsError($groups)) {
            return $this->gadget->session->response($groups->getMessage(), RESPONSE_ERROR);
        }

        $groupsCount = $this->gadget->model->load('Group')->listCount(
            0,
            0,
            (int)$post['filters']['uid']
        );
        if (Jaws_Error::IsError($groupsCount)) {
            return $this->gadget->session->response($groupsCount->getMessage(), RESPONSE_ERROR);
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
     * Add user to group
     *
     * @access  public
     * @return  array   Groups data
     */
    function AddUserToGroup()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $post = $this->gadget->request->fetch(array('uid', 'gid'), 'post');

        $res = $this->gadget->model->load('UserGroup')->add((int)$post['uid'], (int)$post['gid']);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($res->getMessage(), RESPONSE_ERROR);
        }
        return $this->gadget->session->response($this::t('USER_ADDED_TO_GROUP'), RESPONSE_NOTICE);
    }

    /**
     * Delete user from groups
     *
     * @access  public
     * @return  array   Groups data
     */
    function DeleteUserFromGroups()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $post = $this->gadget->request->fetch(array('uid', 'groupIds:array'), 'post');

        foreach ((array)$post['groupIds'] as $gid) {
            $res = $this->gadget->model->load('UserGroup')->delete((int)$post['uid'], $gid);
            if (Jaws_Error::IsError($res)) {
                return $this->gadget->session->response($res->getMessage(), RESPONSE_ERROR);
            }
        }
        return $this->gadget->session->response($this::t('USERS_DELETED_FROM_GROUP'), RESPONSE_NOTICE);
    }

    /**
     * Update user's personal info
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdatePersonal()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $post = $this->gadget->request->fetch(array('id', 'data:array'), 'post');
        $pData = $post['data'];

        $pData['dob'] = empty($pData['dob'])? null : $pData['dob'];
        if (!empty($pData['dob'])) {
            $objDate = Jaws_Date::getInstance();
            $pData['dob'] = $objDate->ToBaseDate(preg_split('/[- :]/', $pData['dob']), 'Y-m-d H:i:s');
            $pData['dob'] = $this->app->UserTime2UTC($pData['dob'], 'Y-m-d H:i:s');
        }

        // don't touch user's avatar
        if ($pData['avatar'] == 'false') {
            unset($pData['avatar']);
        }

        $res = $this->gadget->model->load('User')->updatePersonal((int)$post['id'], $pData);
        if ($res === false || Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($this::t('USERS_PERSONALINFO_NOT_UPDATED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response($this::t('USERS_PERSONALINFO_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Gets a user contact info
     *
     * @access  public
     * @return  array   Users list
     */
    function GetUserContact()
    {
        $uid = (int)$this->gadget->request->fetch('uid', 'post');
        $cInfo = $this->gadget->model->load('Contact')->getContact($uid);
        if (Jaws_Error::IsError($cInfo)) {
            return $this->gadget->session->response($cInfo->getMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response('', RESPONSE_NOTICE, $cInfo);
    }

    /**
     * Updates contacts information of the user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateUserContacts()
    {
        $post = $this->gadget->request->fetch(array('uid', 'data:array'), 'post');
        // unset invalid keys
        $invalids = array_diff(
            array_keys($post['data']),
            array(
                'title', 'name', 'tel_home', 'tel_work', 'tel_other', 'fax_home', 'fax_work', 'fax_other',
                'mobile_home', 'mobile_work', 'mobile_other', 'url_home', 'url_work', 'url_other',
                'email_home', 'email_work', 'email_other',
                'province_home', 'city_home', 'address_home', 'postal_code_home',
                'province_work', 'city_work', 'address_work', 'postal_code_work',
                'province_other', 'city_other', 'address_other', 'postal_code_other',
                'note'
            )
        );
        foreach ($invalids as $invalid) {
            unset($post['data'][$invalid]);
        }

        $res = $this->gadget->model->load('Contact')->UpdateContact(
            (int)$post['uid'],
            $post['data']
        );
        if ($res === false || Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($this::t('USERS_NOT_CONTACTINFO_UPDATED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response($this::t('USERS_CONTACTINFO_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Updates extra information of the user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateUserExtra()
    {
        $post = $this->gadget->request->fetch(array('uid', 'data:array'), 'post');
        // unset invalid keys
        $invalids = array_diff(
            array_keys($post['data']),
            array('mailquota', 'ftpquota')
        );
        foreach ($invalids as $invalid) {
            unset($post['data'][$invalid]);
        }

        $res = $this->gadget->model->load('Extra')->UpdateExtra(
            (int)$post['uid'],
            $post['data']
        );
        if ($res === false) {
            return $this->gadget->session->response($this::t('USERS_NOT_EXTRAINFO_UPDATED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response($this::t('USERS_EXTRAINFO_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Gets a user extra info
     *
     * @access  public
     * @return  array   extra attributes
     */
    function GetUserExtra()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $uid = (int)$this->gadget->request->fetch('uid', 'post');
        $extraInfo = $this->gadget->model->load('Extra')->GetUserExtra($uid);
        if (Jaws_Error::IsError($extraInfo)) {
            return $this->gadget->session->response($extraInfo->getMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response('', RESPONSE_NOTICE, $extraInfo);
    }

    /**
     * Deletes the user(s)
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteUsers()
    {
        $this->gadget->CheckPermission('ManageUsers');
        $uids = $this->gadget->request->fetch('uids:array', 'post');
        $uids = is_array($uids) ? $uids : array($uids);

        $errors = 0;
        foreach ($uids as $uid) {
            if ($uid == $this->app->session->user->id) {
                if (count($uids) === 1) {
                    return $this->gadget->session->response(
                        $this::t('USERS_CANT_DELETE_SELF'),
                        RESPONSE_ERROR
                    );
                }
                $errors++;
                continue;
            }

            $profile = $this->gadget->model->load('User')->get((int)$uid);
            if (!$this->app->session->user->superadmin && $profile['superadmin']) {
                $errors++;
                continue;
            }
            if (!$this->gadget->model->load('User')->delete((int)$uid)) {
                $errors++;
                continue;
            }
        }

        return $this->gadget->session->response(
            $this::t('USERS_DELETED', count($uids) - $errors, count($uids)),
            RESPONSE_NOTICE
        );
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
     * Logout user
     *
     * @access  public
     * @return  void
     */
    function Logout()
    {
        $this->app->session->logout();
        $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
        return Jaws_Header::Location($admin_script?: 'admin.php');
    }

}