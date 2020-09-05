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
    function __construct($gadget)
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
        @list($uid, $account, $personal, $contacts) = $this->gadget->request->fetchAll('post');
        $profile = $this->_UserModel->GetUser((int)$uid, $account, $personal, $contacts);
        if (Jaws_Error::IsError($profile)) {
            return array();
        }

        $objDate = Jaws_Date::getInstance();
        if ($account) {
            if (!empty($profile['expiry_date'])) {
                $profile['expiry_date'] = $objDate->Format($profile['expiry_date'], 'Y/m/d');
            } else {
                $profile['expiry_date'] = '';
            }
        }

        if ($personal) {
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
     * Gets a user contact info
     *
     * @access  public
     * @return  array   Users list
     */
    function GetUserContact()
    {
        $uid = (int)$this->gadget->request->fetch('uid', 'post');
        $jUser = new Jaws_User;
        return $jUser->GetUserContact($uid);
    }

    /**
     * Gets a user extra info
     *
     * @access  public
     * @return  array   extra attributes
     */
    function GetUserExtra()
    {
        $uid = (int)$this->gadget->request->fetch('uid', 'post');
        return $this->gadget->model->load('Extra')->GetUserExtra($uid);
    }

    /**
     * Gets list of users according to the given criteria
     *
     * @access  public
     * @return  array   Users list
     */
    function GetUsers()
    {
        @list($group, $domain, $superadmin, $status, $term, $orderBy, $offset) = $this->gadget->request->fetchAll('post');
        $superadmin = ($superadmin == -1)? null : (bool)$superadmin;
        if (!$this->app->session->user->superadmin) {
            $superadmin = false;
        }

        $group  = ($group  == -1)? false : (int)$group;
        $status = ($status == -1)? null  : (int)$status;
        if (!is_numeric($offset)) {
            $offset = null;
        }

        $usrHTML = $this->gadget->action->loadAdmin('Users');
        return $usrHTML->GetUsers($group, $domain, $superadmin, $status, $term, $orderBy, $offset);
    }

    /**
     * Gets number of users
     *
     * @access  public
     * @return  int     Number of users
     */
    function GetUsersCount()
    {
        @list($group, $domain, $superadmin, $status, $term) = $this->gadget->request->fetchAll('post');
        $superadmin = ($superadmin == -1)? null : (bool)$superadmin;
        if (!$this->app->session->user->superadmin) {
            $superadmin = false;
        }

        $group  = ($group  == -1)? false : (int)$group;
        $status = ($status == -1)? null  : (int)$status;

        return $this->_UserModel->GetUsersCount($group, $domain, $superadmin, $status, $term);
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
        $uData['superadmin'] = ($uData['superadmin'] == 1) ? true : false;

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $uData['password'] = $JCrypt->decrypt($uData['password']);
        }

        $uData['status'] = (int)$uData['status'];
        $uData['superadmin'] = $this->app->session->user->superadmin? (bool)$uData['superadmin'] : false;
        $res = $this->_UserModel->AddUser($uData);
        if (Jaws_Error::isError($res)) {
            $this->gadget->session->push(
                $res->getMessage(),
                RESPONSE_ERROR
            );
        } else {
            $guid = $this->gadget->registry->fetch('anon_group');
            if (!empty($guid)) {
                $this->_UserModel->AddUserToGroup($res, (int)$guid);
            }
            $this->gadget->session->push(
                $this::t('USERS_CREATED', $uData['username']),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
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
        $uid = $post['uid'];
        $uData = $post['data'];

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $uData['password'] = $JCrypt->decrypt($uData['password']);
        }

        if ($uid == $this->app->session->user->id) {
            unset($uData['status'], $uData['superadmin'], $uData['expiry_date']);
        } else {
            $uData['status'] = (int)$uData['status'];
        }

        $res = $this->_UserModel->UpdateUser($uid, $uData);
        if (Jaws_Error::isError($res)) {
            $this->gadget->session->push(
                $res->getMessage(),
                RESPONSE_ERROR
            );
        } else {
            // send activate notification
            if ($uData['prev_status'] == 2 && $uData['status'] == 1) {
                $uRegistration = $this->gadget->action->load('Registration');
                $uRegistration->ActivateNotification($uData, $this->gadget->registry->fetch('anon_activation'));
            }
            $this->gadget->session->push(
                $this::t('USERS_UPDATED', $uData['username']),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
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
        @list($uid) = $this->gadget->request->fetchAll('post');
        if ($uid == $this->app->session->user->id) {
            $this->gadget->session->push(
                $this::t('USERS_CANT_DELETE_SELF'),
                RESPONSE_ERROR
            );
        } else {
            $profile = $this->_UserModel->GetUser((int)$uid);
            if (!$this->app->session->user->superadmin && $profile['superadmin']) {
                $this->gadget->session->push(
                    $this::t('USERS_CANT_DELETE', $profile['username']),
                    RESPONSE_ERROR
                );
                return $this->gadget->session->pop();
            }

            if (!$this->_UserModel->DeleteUser($uid)) {
                $this->gadget->session->push(
                    $this::t('USERS_CANT_DELETE', $profile['username']),
                    RESPONSE_ERROR
                );
            } else {
                $this->gadget->session->push(
                    $this::t('USER_DELETED', $profile['username']),
                    RESPONSE_NOTICE
                );
            }
        }
        return $this->gadget->session->pop();
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
        $sid = $this->gadget->request->fetchAll('post');
        if ($this->app->session->delete($sid)) {
            $this->gadget->session->push(
                $this::t('ONLINE_SESSION_DELETED'),
                RESPONSE_NOTICE
            );
        } else {
            $this->gadget->session->push(
                $this::t('ONLINE_SESSION_NOT_DELETED'),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->pop();
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
        $sIds = $this->gadget->request->fetchAll('post');
        foreach ($sIds as $id) {
            $session = $this->app->session->getSession($id);

            if ($mPolicy->AddIPRange($session['ip'], null, true)) {
                $this->gadget->session->push(
                    Jaws_Gadget::t('POLICY.RESPONSE_IP_ADDED'),
                    RESPONSE_NOTICE
                );
            } else {
                $this->gadget->session->push(
                    Jaws_Gadget::t('POLICY.RESPONSE_IP_NOT_ADDED'),
                    RESPONSE_ERROR
                );
            }
        }

        return $this->gadget->session->pop();
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
        $sIds = $this->gadget->request->fetchAll('post');

        $mPolicy = Jaws_Gadget::getInstance('Policy')->model->loadAdmin('Agent');
        foreach ($sIds as $id) {
            $session = $this->app->session->getSession($id);

            if ($mPolicy->AddAgent($session['agent'], true)) {
                $this->gadget->session->push(
                    Jaws_Gadget::t('POLICY.RESPONSE_AGENT_ADDED'),
                    RESPONSE_NOTICE
                );
            } else {
                $this->gadget->session->push(
                    Jaws_Gadget::t('POLICY.RESPONSE_AGENT_NOT_ADDEDD'),
                    RESPONSE_ERROR
                );
            }
        }

        return $this->gadget->session->pop();
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
        @list($uid, $comp, $acls) = $this->gadget->request->fetchAll('post');
        $acls = $this->gadget->request->fetch('2:array', 'post');
        $res = $this->app->acl->deleteByUser($uid, $comp);
        if ($res) {
            $res = $this->app->acl->insertAll($acls, $comp, $uid);
        }

        if ($res) {
            $this->gadget->session->push(
                $this::t('USER_ACL_UPDATED'),
                RESPONSE_NOTICE
            );
        } else {
            $this->gadget->session->push(
                $this::t('USER_ACL_NOT_UPDATED'),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->pop();
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
        @list($gid, $comp, $acls) = $this->gadget->request->fetchAll('post');
        $acls = $this->gadget->request->fetch('2:array', 'post');
        $res = $this->app->acl->deleteByGroup($gid, $comp);
        if ($res) {
            $res = $this->app->acl->insertAll($acls, $comp, 0, $gid);
        }

        if ($res) {
            $this->gadget->session->push(
                $this::t('GROUP_ACL_UPDATED'),
                RESPONSE_NOTICE
            );
        } else {
            $this->gadget->session->push(
                $this::t('GROUP_ACL_NOT_UPDATED'),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->pop();
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
        @list($uid, $groups) = $this->gadget->request->fetchAll('post');
        $groups = $this->gadget->request->fetch('1:array', 'post');
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

            $this->gadget->session->push(
                $this::t('GROUPS_UPDATED_USERS'),
                RESPONSE_NOTICE
            );
        } else {
            $this->gadget->session->push(
                $oldGroups->GetMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->pop();
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
        @list($guid, $users) = $this->gadget->request->fetchAll('post');
        $users = $this->gadget->request->fetch('1:array', 'post');
        $uModel = $this->gadget->model->loadAdmin('UsersGroup');
        $res = $uModel->AddUsersToGroup($guid, $users);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push($this::t('GROUPS_UPDATED_USERS'), RESPONSE_NOTICE);
        }
        return $this->gadget->session->pop();
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
        @list($id, $comp, $action) = $this->gadget->request->fetchAll('post');
        // fetch default ACLs
        $default_acls = array();
        $result = $this->app->acl->fetchAll($comp);
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
            $this->app->acl->fetchAllByUser($id, $comp):
            $this->app->acl->fetchAllByGroup($id, $comp);
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
        $uData = $this->gadget->request->fetchAll('post');
        $uid   = $uData['uid'];
        // unset invalid keys
        $invalids = array_diff(array_keys($uData), array('username', 'nickname', 'email', 'mobile', 'password'));
        foreach ($invalids as $invalid) {
            unset($uData[$invalid]);
        }

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $uData['password'] = $JCrypt->decrypt($uData['password']);
        }

        $res = $this->_UserModel->UpdateUser($uid, $uData);
        if (Jaws_Error::isError($res)) {
            $this->gadget->session->push(
                $res->getMessage(),
                RESPONSE_ERROR
            );
        } else {
            $this->gadget->session->push(
                $this::t('MYACCOUNT_UPDATED'),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
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
        @list($uid) = $this->gadget->request->fetchAll('post');
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
     * Returns the UI of the extra options
     *
     * @access  public
     * @return  string  XHTML content
     */
    function ExtraUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Users');
        return $gadget->ExtraUI();
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
        ) = $this->gadget->request->fetchAll('post');
        $dob = empty($dob)? null : $dob;
        if (!empty($dob)) {
            $objDate = Jaws_Date::getInstance();
            $dob = $objDate->ToBaseDate(preg_split('/[- :]/', $dob), 'Y-m-d H:i:s');
            $dob = $this->app->UserTime2UTC($dob, 'Y-m-d H:i:s');
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
            $this->gadget->session->push(
                $this::t('USERS_PERSONALINFO_NOT_UPDATED'),
                RESPONSE_ERROR
            );
        } else {
            $this->gadget->session->push(
                $this::t('USERS_PERSONALINFO_UPDATED'),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
    }

    /**
     * Updates preferences options of the user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdatePreferences()
    {
        @list($uid, $lang, $theme, $editor, $timezone) = $this->gadget->request->fetchAll('post');
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
            $this->gadget->session->push(
                $this::t('USERS_NOT_ADVANCED_UPDATED'),
                RESPONSE_ERROR
            );
        } else {
            $this->gadget->session->push(
                $this::t('USERS_ADVANCED_UPDATED'),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
    }

    /**
     * Updates contacts information of the user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateContacts()
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

        $uModel = $this->gadget->model->load('Contacts');
        $res = $uModel->UpdateContact(
            (int)$post['uid'],
            $post['data']
        );
        if ($res === false) {
            $this->gadget->session->push(
                $this::t('USERS_NOT_CONTACTINFO_UPDATED'),
                RESPONSE_ERROR
            );
        } else {
            $this->gadget->session->push(
                $this::t('USERS_CONTACTINFO_UPDATED'),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
    }

    /**
     * Updates extra information of the user
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateExtra()
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

        $uModel = $this->gadget->model->load('Extra');
        $res = $uModel->UpdateExtra(
            (int)$post['uid'],
            $post['data']
        );
        if ($res === false) {
            $this->gadget->session->push(
                $this::t('USERS_NOT_EXTRAINFO_UPDATED'),
                RESPONSE_ERROR
            );
        } else {
            $this->gadget->session->push(
                $this::t('USERS_EXTRAINFO_UPDATED'),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
    }

    /**
     * Gets information of a the group
     *
     * @access  public
     * @return  array   Group information
     */
    function GetGroup()
    {
        @list($guid) = $this->gadget->request->fetchAll('post');
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
        @list($offset) = $this->gadget->request->fetchAll('post');
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
        @list($name, $title, $description, $enabled) = $this->gadget->request->fetchAll('post');
        $res = $this->_UserModel->AddGroup(
            array(
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'enabled' => (bool)$enabled
            )
        );

        if (Jaws_Error::isError($res)) {
            $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push(
                $this::t('GROUPS_CREATED', $title),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
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
        @list($guid, $name, $title, $description, $enabled) = $this->gadget->request->fetchAll('post');
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
            $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push(
                $this::t('GROUPS_UPDATED', $title),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
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
        @list($guid) = $this->gadget->request->fetchAll('post');

        $currentUid = $this->app->session->user->id;
        $groupinfo = $this->_UserModel->GetGroup((int)$guid);
        if (!$this->_UserModel->DeleteGroup($guid)) {
            $this->gadget->session->push(
                $this::t('GROUPS_CANT_DELETE', $groupinfo['name']),
                RESPONSE_ERROR
            );
        } else {
            $this->gadget->session->push(
                $this::t('GROUPS_DELETED', $groupinfo['name']),
                RESPONSE_NOTICE
            );
        }
        return $this->gadget->session->pop();
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
        @list($gid) = $this->gadget->request->fetchAll('post');
        $users = $this->_UserModel->GetUsers((int)$gid);
        if (Jaws_Error::IsError($users)) {
            return array();
        }

        return $users;
    }
}