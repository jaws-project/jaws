<?php
/**
 * Users AJAX API
 *
 * @category   Ajax
 * @package    Users
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_AdminAjax extends Jaws_Gadget_Action
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
    function Users_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_Action($gadget);
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

        $objDate = $GLOBALS['app']->loadDate();
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

        $usrHTML = $this->gadget->loadAdminAction('Users');
        return $usrHTML->GetUsers($group, $superadmin, $status, $term, $orderBy, $offset);
    }

    /**
     * Gets list of online users
     *
     * @access  public
     * @return  array   Online users list
     */
    function GetOnlineUsers()
    {
        $usrHTML = $this->gadget->loadAdminAction('OnlineUsers');
        $filters = jaws()->request->fetchAll('post');
        return $usrHTML->GetOnlineUsers($filters);
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
        if ($this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true') {
            $JCrypt = new Jaws_Crypt();
            $JCrypt->Init();
            $uData['password'] = $JCrypt->decrypt($uData['password']);
            if (($uData['password'] === false) || Jaws_Error::isError($uData['password'])) {
                $uData['password'] = '';
            }
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

        if ($this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true') {
            $JCrypt = new Jaws_Crypt();
            $JCrypt->Init();
            $uData['password'] = $JCrypt->decrypt($uData['password']);
            if (($uData['password'] === false) || Jaws_Error::isError($uData['password'])) {
                unset($uData['password']);
            }
        }

        if ($uid == $GLOBALS['app']->Session->GetAttribute('user')) {
            unset($uData['status'], $uData['superadmin'], $uData['expiry_date']);
        } else {
            $uData['status'] = (int)$uData['status'];
            if (!$GLOBALS['app']->Session->IsSuperAdmin()) {
                unset($uData['status'], $uData['superadmin'], $uData['expiry_date']);
            }
        }

        $res = $this->_UserModel->UpdateUser($uid, $uData);
        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            // send activate notification
            if ($uData['prev_status'] == 2 && $uData['status'] == 1) {
                $uRegistration = $this->gadget->loadAction('Registration');
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
        $sIds = jaws()->request->fetchAll('post');
        // TODO : must added array of id to delete session method
        if ($GLOBALS['app']->Session->Delete($sIds)) {
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

        $mPolicy = Jaws_Gadget::getInstance('Policy')->loadAdminModel('IP');
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

        $mPolicy = Jaws_Gadget::getInstance('Policy')->loadAdminModel('Agent');
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
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(),
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
        $uModel = $this->gadget->loadAdminModel('UsersGroup');
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
        @list($method, $anon, $repetitive, $act, $group, $recover, $dashboard) = jaws()->request->fetchAll('post');
        $uModel = $this->gadget->loadAdminModel('Settings');
        $res = $uModel->SaveSettings($method, $anon, $repetitive, $act, $group, $recover, $dashboard);
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
        $html = $this->gadget->loadAdminAction('ACL');
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
        $default_acls = $GLOBALS['app']->ACL->fetchAll($comp);
        // set ACL keys description
        $info = Jaws_Gadget::getInstance($comp);
        foreach ($default_acls as $k => $acl) {
            $default_acls[$k]['key_desc'] = $info->acl->description($acl['key_name'], $acl['key_subkey']);
        }

        // fetch user/group ACLs
        $acls = ($action === 'UserACL')?
            $GLOBALS['app']->ACL->fetchAllByUser($id, $comp):
            $GLOBALS['app']->ACL->fetchAllByGroup($id, $comp);

        return array('default_acls' => $default_acls, 'custom_acls' => $acls);
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

        if ($this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true') {
            $JCrypt = new Jaws_Crypt();
            $JCrypt->Init();
            $uData['password'] = $JCrypt->decrypt($uData['password']);
            if (($uData['password'] === false) || Jaws_Error::isError($uData['password'])) {
                unset($uData['password']);
            }
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
        $gadget = $this->gadget->loadAdminAction('Users');
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
        $gadget = $this->gadget->loadAdminAction('Users');
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
        $gadget = $this->gadget->loadAdminAction('Users');
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
        $gadget = $this->gadget->loadAdminAction('Users');
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
            $objDate = $GLOBALS['app']->loadDate();
            $dob = $objDate->ToBaseDate(preg_split('/[- :]/', $dob), 'Y-m-d H:i:s');
            $dob = $GLOBALS['app']->UserTime2UTC($dob, 'Y-m-d H:i:s');
        }

        $res = $this->_UserModel->UpdatePersonal(
            $uid,
            array(
                'fname'   => $fname,
                'lname'   => $lname,
                'gender'  => $gender,
                'ssn'     => $ssn,
                'dob'     => $dob,
                'url'     => $url,
                'about'   => $about,
                'avatar'  => ($avatar == 'false')? null : $avatar,
                'privacy' => (bool)$privacy
            )
        );
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
        @list($uid, $country, $city, $address, $postalCode, $phoneNumber,
            $mobileNumber, $faxNumber
        ) = jaws()->request->fetchAll('post');
        $res = $this->_UserModel->UpdateContacts(
            $uid,
            array(
                'country' => $country,
                'city'    => $city,
                'address'   => $address,
                'postal_code' => $postalCode,
                'phone_number' => $phoneNumber,
                'mobile_number' => $mobileNumber,
                'fax_number' => $faxNumber
            )
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
        $grpHTML = $this->gadget->loadAdminAction('Groups');
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
        $grpHTML = $this->gadget->loadAdminAction('Groups');
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

}