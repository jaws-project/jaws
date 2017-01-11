<?php
/**
 * Users AJAX API
 *
 * @category   Ajax
 * @package    Users
 */
class Users_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * User model
     *
     * @var     object
     * @access  private
     */
    var $_UserModel;

    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Users_Actions_Admin_Ajax($gadget)
    {
        parent::__construct($gadget);
        $this->_UserModel = new Jaws_User();
    }

    /**
     * Gets users's profile
     *
     * @access  public
     * @return  array   User information
     */
    function GetUser()
    {
        @list($uid, $account, $personal, $contacts) = jaws()->request->fetchAll('post');
        $profile = $this->_UserModel->GetUser((int)$uid, $account, $personal, $contacts);
        if (Jaws_Error::IsError($profile)) {
            return array();
        }

        $objDate = Jaws_Date::getInstance();
        if ($account) {
            if (!empty($profile['expiry_date'])) {
                $profile['expiry_date'] = $objDate->Format($profile['expiry_date'], 'Y-m-d H:i:s');
            } else {
                $profile['expiry_date'] = '';
            }
        }

        if ($personal) {
            if (empty($profile['avatar'])) {
                $profile['avatar'] = $GLOBALS['app']->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
            } else {
                $profile['avatar'] = $GLOBALS['app']->getDataURL(). 'avatar/'. $profile['avatar'];
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
     * Gets a user contact info
     *
     * @access  public
     * @return  array   Users list
     */
    function GetUserContact()
    {
        $uid = (int)jaws()->request->fetch('uid', 'post');
        $jUser = new Jaws_User;
        return $jUser->GetUserContact($uid);
    }

    /**
     * Gets list of users according to the given criteria
     *
     * @access  public
     * @return  array   Users list
     */
    function GetUsers()
    {
        @list($group, $superadmin, $status, $term, $orderBy, $offset) = jaws()->request->fetchAll('post');
        $superadmin = ($superadmin == -1)? null : (bool)$superadmin;
        if (!$GLOBALS['app']->Session->IsSuperAdmin()) {
            $superadmin = false;
        }

        $group  = ($group  == -1)? false : (int)$group;
        $status = ($status == -1)? null  : (int)$status;
        if (!is_numeric($offset)) {
            $offset = null;
        }

        $usrHTML = $this->gadget->action->loadAdmin('Users');
        return $usrHTML->GetUsers($group, $superadmin, $status, $term, $orderBy, $offset);
    }

    /**
     * Gets number of users
     *
     * @access  public
     * @return  int     Number of users
     */
    function GetUsersCount()
    {
        @list($group, $superadmin, $status, $term) = jaws()->request->fetchAll('post');
        $superadmin = ($superadmin == -1)? null : (bool)$superadmin;
        if (!$GLOBALS['app']->Session->IsSuperAdmin()) {
            $superadmin = false;
        }

        $group  = ($group  == -1)? false : (int)$group;
        $status = ($status == -1)? null  : (int)$status;

        return $this->_UserModel->GetUsersCount($group, $superadmin, $status, $term);
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
        $uData = jaws()->request->fetchAll('post');
        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $uData['password'] = $JCrypt->decrypt($uData['password']);
        }

        $uData['status'] = (int)$uData['status'];
        $uData['superadmin'] = $GLOBALS['app']->Session->IsSuperAdmin()? (bool)$uData['superadmin'] : false;
        $res = $this->_UserModel->AddUser($uData);
        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(),
                                                       RESPONSE_ERROR);
        } else {
            $guid = $this->gadget->registry->fetch('anon_group');
            if (!empty($guid)) {
                $this->_UserModel->AddUserToGroup($res, (int)$guid);
            }
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_CREATED', $uData['username']),
                                                       RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
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
        $uData = jaws()->request->fetchAll('post');
        $uid = $uData['uid'];

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $uData['password'] = $JCrypt->decrypt($uData['password']);
        }

        if ($uid == $GLOBALS['app']->Session->GetAttribute('user')) {
            unset($uData['status'], $uData['superadmin'], $uData['expiry_date']);
        } else {
            $uData['status'] = (int)$uData['status'];
        }

        $res = $this->_UserModel->UpdateUser($uid, $uData);
        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            // send activate notification
            if ($uData['prev_status'] == 2 && $uData['status'] == 1) {
                $uRegistration = $this->gadget->action->load('Registration');
                $uRegistration->ActivateNotification($uData, $this->gadget->registry->fetch('anon_activation'));
            }
            $GLOBALS['app']->Session->PushLastResponse(
                _t('USERS_USERS_UPDATED', $uData['username']),
                RESPONSE_NOTICE
            );
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteUser()
    {
        $this->gadget->CheckPermission('ManageUsers');
        @list($uid) = jaws()->request->fetchAll('post');
        if ($uid == $GLOBALS['app']->Session->GetAttribute('user')) {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_CANT_DELETE_SELF'),
                                                       RESPONSE_ERROR);
        } else {
            $profile = $this->_UserModel->GetUser((int)$uid);
            if (!$GLOBALS['app']->Session->IsSuperAdmin() && $profile['superadmin']) {
                $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_CANT_DELETE', $profile['username']),
                                                           RESPONSE_ERROR);
                return $GLOBALS['app']->Session->PopLastResponse();
            }

            if (!$this->_UserModel->DeleteUser($uid)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_CANT_DELETE', $profile['username']),
                                                           RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USER_DELETED', $profile['username']),
                                                           RESPONSE_NOTICE);
            }
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a session
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteSession()
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $sid = jaws()->request->fetchAll('post');
        if ($GLOBALS['app']->Session->Delete($sid)) {
            $GLOBALS['app']->Session->PushLastResponse(
                _t('USERS_ONLINE_SESSION_DELETED'),
                RESPONSE_NOTICE
            );
        } else {
            $GLOBALS['app']->Session->PushLastResponse(
                _t('USERS_ONLINE_SESSION_NOT_DELETED'),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Block IP address
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function IPBlock()
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $this->gadget->CheckPermission('ManageIPs');

        $mPolicy = Jaws_Gadget::getInstance('Policy')->model->loadAdmin('IP');
        $sIds = jaws()->request->fetchAll('post');
        foreach ($sIds as $id) {
            $session = $GLOBALS['app']->Session->GetSession($id);

            if ($mPolicy->AddIPRange($session['ip'], null, true)) {
                $GLOBALS['app']->Session->PushLastResponse(
                    _t('POLICY_RESPONSE_IP_ADDED'),
                    RESPONSE_NOTICE
                );
            } else {
                $GLOBALS['app']->Session->PushLastResponse(
                    _t('POLICY_RESPONSE_IP_NOT_ADDED'),
                    RESPONSE_ERROR
                );
            }
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Block agent
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AgentBlock()
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $this->gadget->CheckPermission('ManageAgents');
        $sIds = jaws()->request->fetchAll('post');

        $mPolicy = Jaws_Gadget::getInstance('Policy')->model->loadAdmin('Agent');
        foreach ($sIds as $id) {
            $session = $GLOBALS['app']->Session->GetSession($id);

            if ($mPolicy->AddAgent($session['agent'], true)) {
                $GLOBALS['app']->Session->PushLastResponse(
                    _t('POLICY_RESPONSE_AGENT_ADDED'),
                    RESPONSE_NOTICE
                );
            } else {
                $GLOBALS['app']->Session->PushLastResponse(
                    _t('POLICY_RESPONSE_AGENT_NOT_ADDEDD'),
                    RESPONSE_ERROR
                );
            }
        }

        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($uid, $comp, $acls) = jaws()->request->fetchAll('post');
        $acls = jaws()->request->fetch('2:array', 'post');
        $res = $GLOBALS['app']->ACL->deleteByUser($uid, $comp);
        if ($res) {
            $res = $GLOBALS['app']->ACL->insertAll($acls, $comp, $uid);
        }

        if ($res) {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USER_ACL_UPDATED'),
                                                       RESPONSE_NOTICE);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USER_ACL_NOT_UPDATED'),
                                                       RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($gid, $comp, $acls) = jaws()->request->fetchAll('post');
        $acls = jaws()->request->fetch('2:array', 'post');
        $res = $GLOBALS['app']->ACL->deleteByGroup($gid, $comp);
        if ($res) {
            $res = $GLOBALS['app']->ACL->insertAll($acls, $comp, 0, $gid);
        }

        if ($res) {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_GROUP_ACL_UPDATED'),
                                                       RESPONSE_NOTICE);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_GROUP_ACL_NOT_UPDATED'),
                                                       RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($uid, $groups) = jaws()->request->fetchAll('post');
        $groups = jaws()->request->fetch('1:array', 'post');
        $oldGroups = $this->_UserModel->GetGroupsOfUser((int)$uid);
        if (!Jaws_Error::IsError($oldGroups)) {
            $oldGroups = array_keys($oldGroups);
            foreach ($groups as $group) {
                if (false === $gIndex = array_search($group, $oldGroups)) {
                    $this->_UserModel->AddUserToGroup($uid, $group);
                } else {
                    unset($oldGroups[$gIndex]);
                }
            }

            // delete remainder groups
            foreach ($oldGroups as $group) {
                $this->_UserModel->DeleteUserFromGroup($uid, $group);
            }

            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_GROUPS_UPDATED_USERS'),
                                                       RESPONSE_NOTICE);
        } else {
            $GLOBALS['app']->Session->PushLastResponse($oldGroups->GetMessage(),
                                                       RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($guid, $users) = jaws()->request->fetchAll('post');
        $users = jaws()->request->fetch('1:array', 'post');
        $uModel = $this->gadget->model->loadAdmin('UsersGroup');
        $res = $uModel->AddUsersToGroup($guid, $users);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_GROUPS_UPDATED_USERS'), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates User gadget settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function SaveSettings()
    {
        $this->gadget->CheckPermission('ManageProperties');
        @list($method, $anon, $act, $group, $recover) = jaws()->request->fetchAll('post');
        $uModel = $this->gadget->model->loadAdmin('Settings');
        $res = $uModel->SaveSettings($method, $anon, $act, $group, $recover);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns ACL UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function GetACLUI()
    {
        $this->gadget->CheckPermission('default');
        $html = $this->gadget->action->loadAdmin('ACL');
        return $html->ACLUI();
    }

    /**
     * Returns ACL keys of the component and user/group
     *
     * @access  public
     * @return  array   Array of default ACLs and the user/group ACLs
     */
    function GetACLKeys()
    {
        $this->gadget->CheckPermission('ManageUserACLs');
        @list($id, $comp, $action) = jaws()->request->fetchAll('post');
        // fetch default ACLs
        $default_acls = array();
        $result = $GLOBALS['app']->ACL->fetchAll($comp);
        if (!empty($result)) {
            // set ACL keys description
            $info = Jaws_Gadget::getInstance($comp);
            foreach ($result as $key_name => $acl) {
                foreach ($acl as $subkey => $value) {
                    $default_acls[] = array(
                        'key_name'   => $key_name,
                        'key_subkey' => $subkey,
                        'key_value'  => $value,
                        'key_desc'   => $info->acl->description($key_name, $subkey),
                    );
                }
            }
        }

        // fetch user/group ACLs
        $custom_acls = array();
        $result = ($action === 'UserACL')?
            $GLOBALS['app']->ACL->fetchAllByUser($id, $comp):
            $GLOBALS['app']->ACL->fetchAllByGroup($id, $comp);
        if (!empty($result)) {
            foreach ($result as $key_name => $acl) {
                foreach ($acl as $subkey => $value) {
                    $custom_acls[] = array(
                        'key_name'   => $key_name,
                        'key_subkey' => $subkey,
                        'key_value'  => $value,
                    );
                }
            }
        }

        return array('default_acls' => $default_acls, 'custom_acls' => $custom_acls);
    }

    /**
     * Updates my account
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateMyAccount()
    {
        $this->gadget->CheckPermission('EditUserName,EditUserNickname,EditUserEmail,EditUserPassword', false);
        $uData = jaws()->request->fetchAll('post');
        $uid   = $uData['uid'];
        // unset invalid keys
        $invalids = array_diff(array_keys($uData), array('username', 'nickname', 'email', 'password'));
        foreach ($invalids as $invalid) {
            unset($uData[$invalid]);
        }

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $uData['password'] = $JCrypt->decrypt($uData['password']);
        }

        $res = $this->_UserModel->UpdateUser($uid, $uData);
        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(),
                                                       RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_MYACCOUNT_UPDATED'),
                                                       RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets the user-groups form
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UserGroupsUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Users');
        return $gadget->UserGroupsUI();
    }

    /**
     * Gets the user-groups data
     *
     * @access  public
     * @return  array   Groups data
     */
    function GetUserGroups()
    {
        @list($uid) = jaws()->request->fetchAll('post');
        $groups = $this->_UserModel->GetGroupsOfUser((int)$uid);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }

        return array_keys($groups);
    }

    /**
     * Returns the UI of the personal information
     *
     * @access  public
     * @return  string  XHTML content
     */
    function PersonalUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Users');
        return $gadget->PersonalUI();
    }
    
    /**
     * Returns the UI of the preferences options
     *
     * @access  public
     * @return  string  XHTML content
     */
    function PreferencesUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Users');
        return $gadget->PreferencesUI();
    }

    /**
     * Returns the UI of the contacts options
     *
     * @access  public
     * @return  string  XHTML content
     */
    function ContactsUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Users');
        return $gadget->ContactsUI();
    }

    /**
     * Updates personal information of selected user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdatePersonal()
    {
        @list($uid, $fname, $lname, $gender, $ssn, $dob,
            $url, $about, $avatar, $privacy
        ) = jaws()->request->fetchAll('post');
        $dob = empty($dob)? null : $dob;
        if (!empty($dob)) {
            $objDate = Jaws_Date::getInstance();
            $dob = $objDate->ToBaseDate(preg_split('/[- :]/', $dob), 'Y-m-d H:i:s');
            $dob = $GLOBALS['app']->UserTime2UTC($dob, 'Y-m-d H:i:s');
        }

        $pData = array(
            'fname'   => $fname,
            'lname'   => $lname,
            'gender'  => $gender,
            'ssn'     => $ssn,
            'dob'     => $dob,
            'url'     => $url,
            'about'   => $about,
            'avatar'  => $avatar,
            'privacy' => (bool)$privacy
        );

        // don't touch user's avatar
        if ($avatar == 'false') {
            unset($pData['avatar']);
        }

        $res = $this->_UserModel->UpdatePersonal($uid, $pData);
        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_PERSONALINFO_NOT_UPDATED'),
                                                       RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_PERSONALINFO_UPDATED'),
                                                       RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates preferences options of the user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdatePreferences()
    {
        @list($uid, $lang, $theme, $editor, $timezone) = jaws()->request->fetchAll('post');
        if ($lang == '-default-') {
            $lang = null;
        }

        if ($theme == '-default-') {
            $theme = null;
        }

        if ($editor == '-default-') {
            $editor = null;
        }

        if ($timezone == '-default-') {
            $timezone = null;
        }

        $res = $this->_UserModel->UpdatePreferences(
            $uid,
            array(
                'language' => $lang, 
                'theme'    => $theme,
                'editor'   => $editor,
                'timezone' => $timezone
            )
        );
        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_NOT_ADVANCED_UPDATED'),
                                                       RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_ADVANCED_UPDATED'),
                                                       RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates contacts information of the user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateContacts()
    {
        $post = jaws()->request->fetch(array('uid', 'data:array'), 'post');
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

        $uModel = $this->gadget->model->load('Contacts');
        $res = $uModel->UpdateContact(
            (int)$post['uid'],
            $post['data']
        );
        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_NOT_CONTACTINFO_UPDATED'),
                                                       RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_USERS_CONTACTINFO_UPDATED'),
                                                       RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets information of a the group
     *
     * @access  public
     * @return  array   Group information
     */
    function GetGroup()
    {
        @list($guid) = jaws()->request->fetchAll('post');
        $group = $this->_UserModel->GetGroup((int)$guid);
        if (Jaws_Error::IsError($group)) {
            return array();
        }

        return $group;
    }

    /**
     * Gets list of groups
     *
     * @access  public
     * @return  array   Groups list
     */
    function GetGroups()
    {
        @list($offset) = jaws()->request->fetchAll('post');
        $grpHTML = $this->gadget->action->loadAdmin('Groups');
        return $grpHTML->GetGroups(null, $offset);
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
        @list($name, $title, $description, $enabled) = jaws()->request->fetchAll('post');
        $res = $this->_UserModel->AddGroup(
            array(
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'enabled' => (bool)$enabled
            )
        );

        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(
                _t('USERS_GROUPS_CREATED', $title),
                RESPONSE_NOTICE
            );
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($guid, $name, $title, $description, $enabled) = jaws()->request->fetchAll('post');
        $res = $this->_UserModel->UpdateGroup(
            $guid,
            array(
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'enabled' => (bool)$enabled
            )
        );
        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(
                _t('USERS_GROUPS_UPDATED', $title),
                RESPONSE_NOTICE
            );
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($guid) = jaws()->request->fetchAll('post');

        $currentUid = $GLOBALS['app']->Session->GetAttribute('user');
        $groupinfo = $this->_UserModel->GetGroup((int)$guid);
        if (!$this->_UserModel->DeleteGroup($guid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_GROUPS_CANT_DELETE', $groupinfo['name']),
                                                       RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('USERS_GROUPS_DELETED', $groupinfo['name']),
                                                       RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets the users-group form
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GroupUsersUI()
    {
        $grpHTML = $this->gadget->action->loadAdmin('Groups');
        return $grpHTML->GroupUsersUI();
    }

    /**
     * Gets the group-users array
     *
     * @access  public
     * @return  array   List of users
     */
    function GetGroupUsers()
    {
        @list($gid) = jaws()->request->fetchAll('post');
        $users = $this->_UserModel->GetUsers((int)$gid);
        if (Jaws_Error::IsError($users)) {
            return array();
        }

        return $users;
    }

    /**
     * Get cities
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function GetCities()
    {
        $province = jaws()->request->fetch('province', 'post');
        if (empty($province)) {
            $provinces = jaws()->request->fetch('provinces:array', 'post');
        } else {
            $provinces = array($province);
        }
        $model = $this->gadget->model->load('Contacts');
        $res = $model->GetCities($provinces);
        if (Jaws_Error::IsError($res) || $res === false) {
            return array();
        } else {
            return $res;
        }
    }

}