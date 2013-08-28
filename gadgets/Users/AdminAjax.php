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
class Users_AdminAjax extends Jaws_Gadget_HTML
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
        parent::Jaws_Gadget_HTML($gadget);
        $this->_UserModel = new Jaws_User();
    }

    /**
     * Gets users's profile
     *
     * @access  public
     * @param   int     $uid            User ID
     * @param   bool    $account        Include account information
     * @param   bool    $personal       Include personal information
     * @param   bool    $preferences    Include user preferences information
     * @param   bool    $extra          Include user extra information
     * @param   bool    $contacts       Include user contacts information
     * @return  array   User information
     */
    function GetUser($uid, $account = true, $personal = false, $preferences = false, $contacts = false)
    {
        $profile = $this->_UserModel->GetUser((int)$uid, $account, $personal, $preferences, $contacts);
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
                $profile['avatar'] = $GLOBALS['app']->getSiteURL('/gadgets/Users/images/photo128px.png');
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
     * @param   string  $group      User group
     * @param   bool    $superadmin Is superadmin
     * @param   int     $status     User status
     * @param   string  $term       Term to search
     * @param   string  $orderBy    Order type of result list
     * @param   int     $offset     Data offset
     * @return  array   Users list
     */
    function GetUsers($group, $superadmin, $status, $term, $orderBy, $offset)
    {
        $superadmin = ($superadmin == -1)? null : (bool)$superadmin;
        if (!$GLOBALS['app']->Session->IsSuperAdmin()) {
            $superadmin = false;
        }

        $group  = ($group  == -1)? false : (int)$group;
        $status = ($status == -1)? null  : (int)$status;
        if (!is_numeric($offset)) {
            $offset = null;
        }

        $usrHTML = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Users');
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
        $usrHTML = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'OnlineUsers');
        return $usrHTML->GetOnlineUsers();
    }

    /**
     * Gets number of users
     *
     * @access  public
     * @param   string  $group      User group
     * @param   bool    $superadmin Is superadmin
     * @param   int     $status     User status
     * @param   string  $term       Search term(searched in username, nickname and email)
     * @return  int     Number of users
     */
    function GetUsersCount($group, $superadmin, $status, $term)
    {
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
     * @param   array   $uData  User information data
     * @return  array   Response array (notice or error)
     */
    function AddUser($uData)
    {
        $this->gadget->CheckPermission('ManageUsers');
        if ($this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true') {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
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
     * @param   int     $uid    User ID
     * @param   array   $uData  User information data
     * @return  array   Response array (notice or error)
     */
    function UpdateUser($uid, $uData)
    {
        $this->gadget->CheckPermission('ManageUsers');
        if ($this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true') {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
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
                $uRegistration = $GLOBALS['app']->LoadGadget('Users', 'HTML', 'Registration');
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
     * @param   int     $uid   User ID
     * @return  array   Response array (notice or error)
     */
    function DeleteUser($uid)
    {
        $this->gadget->CheckPermission('ManageUsers');
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
     * @param   int     $sid    Session ID
     * @return  array   Response array (notice or error)
     */
    function DeleteSession($sid)
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
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
     * @param   string  $ip
     * @return  array   Response array (notice or error)
     */
    function IPBlock($ip)
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $this->gadget->CheckPermission('ManageIPs');

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'IP');
        if ($mPolicy->AddIPRange($ip, null, true)) {
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

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Block agent
     *
     * @access  public
     * @param   string  $agent
     * @return  array   Response array (notice or error)
     */
    function AgentBlock($agent)
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $this->gadget->CheckPermission('ManageAgents');

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'Agent');
        if ($mPolicy->AddAgent($agent, true)) {
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

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates modified user ACL keys
     *
     * @access  public
     * @param   int     $uid    User ID
     * @param   string  $comp   Gadget/plugin name
     * @param   array   $acls   ACL keys
     * @return  array   Response array (notice or error)
     */
    function UpdateUserACL($uid, $comp, $acls)
    {
        $this->gadget->CheckPermission('ManageUserACLs');
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
     * @param   int     $gid    Group ID
     * @param   string  $comp   Gadget/plugin name
     * @param   array   $acls   ACL keys
     * @return  array   Response array (notice or error)
     */
    function UpdateGroupACL($gid, $comp, $acls)
    {
        $this->gadget->CheckPermission('ManageUserACLs');
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
     * @param   int     $uid    User ID
     * @param   array   $groups Array with group id
     * @return  array   Response array (notice or error)
     */
    function AddUserToGroups($uid, $groups)
    {
        $this->gadget->CheckPermission('ManageGroups');
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
     * @param   int     $guid  Group ID
     * @param   array   $users Array with user ID
     * @return  array   Response array (notice or error)
     */
    function AddUsersToGroup($guid, $users)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $uModel = $GLOBALS['app']->LoadGadget('Users', 'AdminModel', 'UsersGroup');
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
     * @param   string  $method     Authentication method
     * @param   string  $anon       Anonymous users can auto-register
     * @param   string  $repetitive Anonymous can register by repetitive email
     * @param   string  $act        Activation type
     * @param   int     $group      Default group of anonymous registered user
     * @param   string  $recover    Users can recover their passwords
     * @return  array   Response array (notice or error)
     */
    function SaveSettings($method, $anon, $repetitive, $act, $group, $recover)
    {
        $this->gadget->CheckPermission('ManageProperties');
        $uModel = $GLOBALS['app']->LoadGadget('Users', 'AdminModel', 'Settings');
        $res = $uModel->SaveSettings($method, $anon, $repetitive, $act, $group, $recover);
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
        $html = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'ACL');
        return $html->ACLUI();
    }

    /**
     * Returns ACL keys of the component and user/group
     *
     * @access  public
     * @param   int     $id      User/Group ID
     * @param   string  $comp    Gadget/Plugin name
     * @param   string  $action  UserACL or GroupACL
     * @return  array   Array of default ACLs and the user/group ACLs
     */
    function GetACLKeys($id, $comp, $action)
    {
        $this->gadget->CheckPermission('ManageUserACLs');
        // fetch default ACLs
        $default_acls = $GLOBALS['app']->ACL->fetchAll($comp);
        // set ACL keys description
        $info = $GLOBALS['app']->LoadGadget($comp, 'Info');
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
     * @param   string  $uid    User ID
     * @param   array   $uData  User information data
     * @return  array   Response array (notice or error)
     */
    function UpdateMyAccount($uid, $uData)
    {
        $this->gadget->CheckPermission('EditUserName,EditUserNickname,EditUserEmail,EditUserPassword', false);
        // unset invalid keys
        $invalids = array_diff(array_keys($uData), array('username', 'nickname', 'email', 'password'));
        foreach ($invalids as $invalid) {
            unset($uData[$invalid]);
        }

        if ($this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true') {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
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
        $gadget = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Users');
        return $gadget->UserGroupsUI();
    }

    /**
     * Gets the user-groups data
     *
     * @access  public
     * @param   string  $uid    User ID
     * @return  array   Groups data
     */
    function GetUserGroups($uid)
    {
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
        $gadget = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Users');
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
        $gadget = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Users');
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
        $gadget = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Users');
        return $gadget->ContactsUI();
    }

    /**
     * Updates personal information of selected user
     *
     * @access  public
     * @param   int     $uid        User ID
     * @param   string  $fname      First name
     * @param   string  $lname      Last name
     * @param   string  $gender     User gender
     * @param   string  $ssn        Social Security number
     * @param   string  $dob        User birth date
     * @param   string  $url        User URL
     * @param   string  $about
     * @param   string  $avatar     User avatar
     * @param   bool    $privacy    User's display name
     * @return  array   Response array (notice or error)
     */
    function UpdatePersonal($uid, $fname, $lname, $gender, $ssn, $dob, $url, $about, $avatar, $privacy)
    {
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
     * @param   int     $uid       User ID
     * @param   string  $lang      User language
     * @param   string  $theme     User theme
     * @param   string  $editor    User editor
     * @param   string  $timezone  User timezone
     * @return  array   Response array (notice or error)
     */
    function UpdatePreferences($uid, $lang, $theme, $editor, $timezone)
    {
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
     * @param   int     $uid            User ID
     * @param   string  $country        User country
     * @param   string  $city           User city
     * @param   string  $address        User address
     * @param   string  $postalCode     User postal code
     * @param   string  $phoneNumber    User phone number
     * @param   string  $mobileNumber   User mobile number
     * @param   string  $faxNumber      User fax number
     * @return  array   Response array (notice or error)
     */
    function UpdateContacts($uid, $country, $city, $address, $postalCode, $phoneNumber, $mobileNumber, $faxNumber)
    {
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
     * @param   int     $guid  Group ID
     * @return  array   Group information
     */
    function GetGroup($guid)
    {
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
     * @param   int     $offset Data offset
     * @return  array   Groups list
     */
    function GetGroups($offset)
    {
        $grpHTML = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Groups');
        return $grpHTML->GetGroups(null, $offset);
    }

    /**
     * Adds a new group
     *
     * @access  public
     * @param   string  $name        Groups name
     * @param   string  $title       Groups title
     * @param   string  $description Groups description
     * @param   bool    $enabled     Group status
     * @return  array   Response array (notice or error)
     */
    function AddGroup($name, $title, $description, $enabled)
    {
        $this->gadget->CheckPermission('ManageGroups');
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
     * @param   int     $guid        Group ID
     * @param   string  $name        Group name
     * @param   string  $title       Groups title
     * @param   string  $description Groups description
     * @param   bool    $enabled    Group status
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup($guid, $name, $title, $description, $enabled)
    {
        $this->gadget->CheckPermission('ManageGroups');
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
     * @param   int     $guid   Group ID
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup($guid)
    {
        $this->gadget->CheckPermission('ManageGroups');
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
        $grpHTML = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Groups');
        return $grpHTML->GroupUsersUI();
    }

    /**
     * Gets the group-users array
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  array   List of users
     */
    function GetGroupUsers($gid)
    {
        $users = $this->_UserModel->GetUsers((int)$gid);
        if (Jaws_Error::IsError($users)) {
            return array();
        }

        return $users;
    }

}