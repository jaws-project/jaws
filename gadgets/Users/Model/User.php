<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_User extends Jaws_Gadget_Model
{
    /**
     * Get the info of an user by the username or ID
     *
     * @access  public
     * @param   mixed   $user       The username or ID
     * @param   int     $domain     Domain ID // 0: don't check domain
     * @param   array   $fieldsets  Users fields sets // default, account, personal, password
     * @return  mixed   Returns an array with the info of the user and false on error
     */
    function getUser($user, $domain = 0, $fieldsets = array())
    {
        $columns = array(
            'default'  => array(
                'users.domain:integer', 'users.id:integer', 'username', 'users.email', 'users.mobile',
                'nickname', 'contact:integer', 'avatar:boolean', 'status:integer'
            ),
            'account'  => array(
                'superadmin:boolean', 'concurrents:integer', 'logon_hours',
                'expiry_date:integer', 'registered_date:integer', 'bad_password_count:integer', 
                'last_update:integer', 'last_password_update:integer',
                'last_access:integer', 'verify_key'
            ),
            'personal' => array(
                'fname', 'lname', 'gender', 'ssn', 'dob:integer', 'extra', 'public:boolean', 'privacy:boolean',
                'pgpkey', 'signature', 'about', 'experiences', 'occupations', 'interests'
            ),
            'password' => array('password'),
        );
        $fieldsets['default'] = true;

        $selectedColumns = array();
        foreach ($fieldsets as $key => $keyValue) {
            if ($keyValue) {
                $selectedColumns = array_merge($selectedColumns, $columns[$key]);
            }
        }

        $objORM = Jaws_ORM::getInstance()
            ->table('users')
            ->select($selectedColumns)
            ->where('domain', (int)$domain, '=', empty($domain));
        if (is_int($user)) {
            $objORM->and()->where('users.id', $user);
        } else {
            $objORM->and()->where('username', Jaws_UTF8::strtolower($user));
        }

        return $objORM->fetchRow();
    }

    /**
     * Update user password
     *
     * @access  public
     * @param   int     $uid            User's ID
     * @param   string  $new_password   New password
     * @param   mixed   $old_password   Old password
     * @param   bool    $expired        Password age expired
     * @return  mixed   Returns true if user was successfully updated, Jaws_Error if not
     */
    function updatePassword($uid, $new_password, $old_password = false, $expired = false)
    {
        $user = $this->getUser(
            $uid,
            0,
            array('default' => true, 'account' => true, 'password' => true)
        );
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        // check old password
        if ($old_password !== false) {
            if ($user['password'] !== Jaws_User::GetHashedPassword($old_password, $user['password'])) {
                return Jaws_Error::raiseError(
                    Jaws_Gadget::t('Users.USERS_PASSWORD_OLD_DONT_MATCH'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        // password & complexity
        $min = (int)$this->app->registry->fetch('password_min_length', 'Policy');
        if (!preg_match("/^[[:print:]]{{$min},24}$/", $new_password)) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INVALID_PASSWORD', $min),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        if (!preg_match($this->app->registry->fetch('password_complexity', 'Policy'), $new_password))
        {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INVALID_COMPLEXITY'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        $last_password_update = $expired? 0 : time();
        $result = Jaws_ORM::getInstance()
            ->table('users')->update(
                array(
                    'password' => Jaws_User::GetHashedPassword($new_password),
                    'last_password_update' => $last_password_update,
                )
            )
            ->where('id', $uid)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // update last password update time in current session
        if (isset($this->app) &&
            property_exists($this->app, 'session') &&
            $this->app->session->user->id == $uid
        ) {
            $this->app->session->user = array('last_password_update' => $last_password_update);
        }

        // Let everyone know a password has been changed
        $res = $this->gadget->event->shout(
            'UserChanges',
            array('action' => 'UpdatePassword', 'user' => $uid, 'password' => $new_password)
        );
        if (Jaws_Error::IsError($res)) {
            // nothing
        }

        return true;
    }

    /**
     * Updates user profile
     *
     * @access  public
     * @param   int     $id     User ID
     * @param   array   $pData  Personal information data
     * @return  bool    Returns true on success, false on failure
     */
    function updatePersonal($id, $pData)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($pData),
            array('fname', 'lname', 'gender', 'ssn', 'dob', 'extra',
                'pgpkey', 'signature', 'about', 'experiences',
                'occupations', 'interests', 'avatar', 'privacy',
            )
        );
        foreach ($invalids as $invalid) {
            unset($pData[$invalid]);
        }

        // get user information
        $user = $this->getUser((int)$id);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        if (JAWS_GODUSER == $user['id']) {
            if (!isset($this->app) ||
                !property_exists($this->app, 'session') ||
                $this->app->session->user->id != $user['id']
            ) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_ACCESS_DENIED'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        if (array_key_exists('avatar', $pData)) {
            if (empty($pData['avatar'])) {
                $pData['avatar'] = null;
            } else {
                $pData['avatar'] = array(
                    'File://' . Jaws_FileManagement_File::upload_tmp_dir() . '/' . $pData['avatar'],
                    'blob'
                );
            }
        }

        $pData['last_update'] = time();
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->update($pData)->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (isset($this->app) &&
            property_exists($this->app, 'session') &&
            $this->app->session->user->id == (int)$id
        ) {
            foreach($pData as $k => $v) {
                if ($k == 'avatar') {
                    // url to avatar action
                    $this->app->session->user = array(
                        'avatar' => $this->app->users->GetAvatar($v, $user['email'], 48, $pData['last_update'])
                    );
                } else {
                    $this->app->session->user = array($k => $v);
                }
            }
        }

        // Let everyone know a user has been added
        $res = $this->gadget->event->shout(
            'UserChanges',
            array('action' => 'UpdateUser', 'user' => (int)$id)
        );
        if (Jaws_Error::IsError($res)) {
            // nothing
        }

        return true;
    }

    /**
     * Get user's avatar
     *
     * @access  public
     * @param   int     $user   User ID/Name
     * @param   int     $domain Domain ID
     * @return  mixed   The function returns the avatar image content or Jaws_Error on failure
     */
    function getAvatar($user, $domain = 0)
    {
        $objORM = Jaws_ORM::getInstance()
            ->table('users')
            ->select('avatar:blob')
            ->where('domain', (int)$domain, '=', empty($domain));
        if (is_int($user)) {
            $objORM->and()->where('id', (int)$user);
        } else {
            $objORM->and()->where('username', Jaws_UTF8::strtolower($user));
        }
        $blob = $objORM->fetchOne();
        if (Jaws_Error::IsError($blob)) {
            return false;
        }

        $avatar = '';
        if (is_resource($blob)) {
            while (!feof($blob)) {
                $avatar.= fread($blob, 8192);
            }
        } else {
            $theme = Jaws::getInstance()->GetTheme();
            $defaultImage = $theme['path'] . 'default_avatar.png';
            if (!file_exists($defaultImage)) {
                $defaultImage = ROOT_JAWS_PATH. 'gadgets/Users/Resources/images/photo128px.png';
            }

            // FIXME: Gravatar support!
            $avatar = (string)Jaws_FileManagement_File::file_get_contents($defaultImage);
        }

        return $avatar;
    }

    /**
     * Updates user account information
     *
     * @access  public
     * @param   int     $uid    User ID
     * @param   array   $uData  Account information data
     * @return  bool    Returns true on success, false on failure
     */
    function updateAccount($uid, $uData)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($uData),
            array('username', 'nickname', 'email', 'mobile')
        );
        foreach ($invalids as $invalid) {
            unset($uData[$invalid]);
        }

        $uData['last_update'] = time();
        $objORM = Jaws_ORM::getInstance()->table('users');
        $result = $objORM->update($uData)->where('id', (int)$uid)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (isset($this->app) &&
            property_exists($this->app, 'session') &&
            $this->app->session->user->id == (int)$uid
        ) {
            foreach($uData as $k => $v) {
                $this->app->session->user = array($k => $v);
            }
        }

        // Let everyone know a user has been added
        $res = $this->gadget->event->shout(
            'UserChanges',
            array('action' => 'UpdateUser', 'user' => (int)$uid)
        );
        if (Jaws_Error::IsError($res)) {
            // nothing
        }

        return true;
    }

    /**
     * Deletes an user
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @return  bool    Returns true if user was successfully deleted, false if not
     */
    function deleteUser($uid)
    {
        $objORM = Jaws_ORM::getInstance();
        $user = $this->getUser(
            $uid,
            0,
            array('default' => true, 'account' => true)
        );
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        if (JAWS_GODUSER == $user['id']) {
            return false;
        }

        // users can't delete himself
        if (isset($this->app) &&
            property_exists($this->app, 'session') && $this->app->session->user->id == $user['id']
        ) {
            return false;
        }

        //Start Transaction
        $objORM->beginTransaction();

        $result = $objORM->delete()->table('users')->where('id', $user['id'])->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $result = $objORM->delete()->table('groups')->where('owner', $user['id'])->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $result = $objORM->delete()->table('users_groups')->where('user', $user['id'])->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        // delete user's contact
        $result = $objORM->delete()->table('users_contacts')->where('owner', $user['id'])->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        // Registry
        if (!$this->app->registry->deleteByUser($user['id'])) {
            return false;
        }
        // ACL
        if (!$this->app->acl->deleteByUser($user['id'])) {
            return false;
        }
        // Session
        if (!$this->app->session->deleteUserSessions($user['id'])) {
            return false;
        }

        //Commit Transaction
        $objORM->commit();

        // Let everyone know a user has been deleted
        $res = $this->gadget->event->shout(
            'UserChanges',
            array('action' => 'DeleteUser', 'user' => $user['id'])
        );
        if (Jaws_Error::IsError($res)) {
            // nothing
        }

        return true;
    }

    /**
     * Updates contacts information of the user
     *
     * @access  public
     * @param   int     $uid    User ID
     * @param   array   $data   Contact's data
     * @param   bool    $main   Main contact?
     * @param   int     $cid    Contact's ID
     * @return  array   Response array (notice or error)
     */
    function updateContact($uid, $data, $main = true, $cid = 0)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($data),
            array('title', 'name', 'image', 'note', 'tel', 'mobile', 'fax', 'url', 'email', 'address')
        );
        foreach ($invalids as $invalid) {
            unset($data[$invalid]);
        }

        $user = $this->getUser($uid);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        if (JAWS_GODUSER == $user['id']) {
            if (!isset($this->app) ||
                !property_exists($this->app, 'session') ||
                $this->app->session->user->id != $user['id']
            ) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_ACCESS_DENIED'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        $data['owner'] = $user['id'];
        $data['checksum'] = hash64(json_encode($data));

        $objORM = Jaws_ORM::getInstance();
        if (!$main) {
            $howmany = $objORM->table('users_contacts')
                ->select('count(id):integer')
                ->where('owner', $uid)
                ->and()
                ->where('checksum', $data['checksum'])
                ->fetchOne();
            if (!empty($howmany)) {
                return false;
            }
        }
        // begin transaction
        $objORM->beginTransaction();

        $contactId = $objORM->table('users_contacts')
            ->upsert($data)
            ->where('owner', $uid)
            ->and()
            ->where('id', $main? $user['contact'] : $cid)
            ->exec();
        if (Jaws_Error::IsError($contactId)) {
            return $contactId;
        }

        if ($main) {
            // set user's contact id
            $res = $objORM->table('users')->update(
                    array('contact' => $contactId)
                )->where('id', (int)$uid)
                ->exec();
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        // commit transaction
        $objORM->commit();
        return $contactId;
    }

}