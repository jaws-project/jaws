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
     * Adds a new user
     *
     * @access  public
     * @param   array   $uData  User information data
     * @return  mixed   Returns user's id if user was successfully added, otherwise Jaws_Error
     */
    function addUser($uData)
    {
        // username
        $uData['username'] = trim($uData['username'], '-_.@');
        if (!preg_match('/^[[:alnum:]\-_.@]{3,32}$/', $uData['username'])) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INVALID_USERNAME'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        $uData['username'] = strtolower($uData['username']);

        // check reserved users
        $reservedUsers = preg_split("/\n|\r|\n\r/", $this->app->registry->fetch('reserved_users', 'Users'));
        if (in_array($uData['username'], $reservedUsers)) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_RESERVED_USERNAME', substr(strrchr($uData['username'], '@'), 1)),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // nickname
        $uData['nickname'] = Jaws_UTF8::trim($uData['nickname']);
        if (empty($uData['nickname'])) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INCOMPLETE_FIELDS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // email
        $uData['email'] = trim($uData['email']);
        if (!empty($uData['email'])) {
            if (!preg_match("/^[[:alnum:]\-_.]+\@[[:alnum:]\-_.]+\.[[:alnum:]\-_]+$/", $uData['email'])) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INVALID_EMAIL_ADDRESS'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
            $uData['email'] = strtolower($uData['email']);
            $blockedDomains = $this->app->registry->fetch('blocked_domains', 'Policy');
            if (false !== strpos($blockedDomains, "\n".substr(strrchr($uData['email'], '@'), 1))) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INVALID_EMAIL_DOMAIN', substr(strrchr($uData['email'], '@'), 1)),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        // mobile
        $uData['mobile'] = isset($uData['mobile'])? trim($uData['mobile']) : '';
        if (!empty($uData['mobile'])) {
            if (!empty($uData['mobile'])) {
                if (!preg_match("/^[00|\+|0]\d{10,16}$/", $uData['mobile'])) {
                    return Jaws_Error::raiseError(
                        Jaws::t('ERROR_INVALID_MOBILE_NUMBER'),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE
                    );
                }
            }
        }

        if (empty($uData['email']) && empty($uData['mobile'])) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INCOMPLETE_FIELDS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // password & complexity
        $min = (int)$this->app->registry->fetch('password_min_length', 'Policy');
        $min = ($min == 0)? 1 : $min;
        if ($uData['password'] == '' ||
            !preg_match("/^[[:print:]]{{$min},24}$/", $uData['password'])
        ) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INVALID_PASSWORD', $min),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        if (!preg_match($this->app->registry->fetch('password_complexity', 'Policy'),
                $uData['password'])
        ) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INVALID_COMPLEXITY'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        $uData['last_update'] = time();
        if (!array_key_exists('last_password_update', $uData)) {
            $uData['last_password_update'] = time();
        } else {
            $uData['last_password_update'] = (int)$uData['last_password_update'];
        }

        $uData['registered_date'] = time();
        $uData['superadmin'] = isset($uData['superadmin'])? (bool)$uData['superadmin'] : false;
        $uData['status'] = isset($uData['status'])? (int)$uData['status'] : 1;
        $uData['concurrents'] =
            isset($uData['concurrents'])?
            (int)$uData['concurrents'] :
            (int)$this->app->registry->fetch('default_concurrents', 'Users');
        $uData['password'] = Jaws_User::GetHashedPassword($uData['password']);
        $uData['logon_hours'] = empty($uData['logon_hours'])? str_pad('', 42, 'F') : $uData['logon_hours'];
        if (isset($uData['expiry_date'])) {
            if (empty($uData['expiry_date'])) {
                $uData['expiry_date'] = 0;
            } else {
                $objDate = Jaws_Date::getInstance();
                $uData['expiry_date'] = $this->app->UserTime2UTC(
                    (int)$objDate->ToBaseDate(preg_split('/[\/\- \:]/', $uData['expiry_date']), 'U')
                );
            }
        }

        // set user's domain to default domain if not set
        if (!isset($uData['domain'])) {
            $uData['domain'] = (int)$this->app->registry->fetch('default_domain', 'Users');
        }

        // delete unverified old user with this username/email/mobile
        $objORM = Jaws_ORM::getInstance()
            ->table('users')
            ->delete()
            ->where('domain', $uData['domain'])
            ->and()
            ->where('status', 2)  // 2 = unverified user
            ->and()
            ->openWhere('username', $uData['username']);
        if (!empty($uData['email'])) {
            $objORM->or()->where('email', $uData['email']);
        }
        if (!empty($uData['mobile'])) {
            $objORM->or()->where('mobile', $uData['mobile']);
        }
        $objORM->closeWhere();
        $result = $objORM->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // prevent duplicate username/email/mobile
        $objORM->reset();
        $objORM->table('users')
            ->select('count(id)')
            ->where('domain', $uData['domain'])
            ->and()
            ->openWhere('username', $uData['username']);
        if (!empty($uData['email'])) {
            $objORM->or()->where('email', $uData['email']);
        }
        if (!empty($uData['mobile'])) {
            $objORM->or()->where('mobile', $uData['mobile']);
        }
        $objORM->closeWhere();
        $howmany = $objORM->fetchOne();
        if (Jaws_Error::IsError($howmany) || !empty($howmany)) {
            return Jaws_Error::raiseError(
                Jaws::t('USERS_ALREADY_EXISTS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // insert user
        $objORM->reset();
        $result = $objORM->table('users')->insert($uData)->exec();
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_USERS_ALREADY_EXISTS', $uData['username']));
            }
            return $result;
        }

        // Let everyone know a user has been added
        $res = $this->app->listener->Shout(
            'Users',
            'UserChanges',
            array('action' => 'AddUser', 'user' => $result)
        );
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $result;
    }

    /**
     * Edit user attributes
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   array   $uData  User information data
     * @return  bool    Returns true if user was successfully updated, false if not
     */
    function editUser($uid, $uData)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($uData),
            array('domain', 'username', 'nickname', 'email', 'new_email', 'mobile',
                'superadmin', 'status', 'concurrents', 'logon_hours', 'expiry_date',
            )
        );
        foreach ($invalids as $invalid) {
            unset($uData[$invalid]);
        }

        // username
        if (array_key_exists('username', $uData)) {
            $uData['username'] = trim($uData['username'], '-_.@');
            if (!preg_match('/^[[:alnum:]\-_.@]{3,32}$/', $uData['username'])) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INVALID_USERNAME'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
            $uData['username'] = strtolower($uData['username']);
        }

        // nickname
        if (array_key_exists('nickname', $uData)) {
            $uData['nickname'] = Jaws_UTF8::trim($uData['nickname']);
            if (empty($uData['nickname'])) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INCOMPLETE_FIELDS'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        // email
        if (array_key_exists('email', $uData)) {
            $uData['email'] = trim($uData['email']);
            if (!empty($uData['email'])) {
                if (!preg_match("/^[[:alnum:]\-_.]+\@[[:alnum:]\-_.]+\.[[:alnum:]\-_]+$/", $uData['email'])) {
                    return Jaws_Error::raiseError(
                        Jaws::t('ERROR_INVALID_EMAIL_ADDRESS'),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE
                    );
                }
                $uData['email'] = strtolower($uData['email']);
                $blockedDomains = $this->app->registry->fetch('blocked_domains', 'Policy');
                if (false !== strpos($blockedDomains, "\n".substr(strrchr($uData['email'], '@'), 1))) {
                    return Jaws_Error::raiseError(
                        Jaws::t('ERROR_INVALID_EMAIL_DOMAIN', substr(strrchr($uData['email'], '@'), 1)),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE
                    );
                }
            }
        }

        // new email
        if (array_key_exists('new_email', $uData)) {
            $uData['new_email'] = trim($uData['new_email']);
            if (!preg_match("/^[[:alnum:]\-_.]+\@[[:alnum:]\-_.]+\.[[:alnum:]\-_]+$/", $uData['new_email'])) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INVALID_EMAIL_ADDRESS'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
            $uData['new_email'] = strtolower($uData['new_email']);
            if (false !== strpos($blockedDomains, "\n".substr(strrchr($uData['new_email'], '@'), 1))) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INVALID_EMAIL_DOMAIN', substr(strrchr($uData['new_email'], '@'), 1)),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        // mobile
        if (array_key_exists('mobile', $uData)) {
            $uData['mobile'] = isset($uData['mobile'])? trim($uData['mobile']) : '';
            if (!empty($uData['mobile'])) {
                if (!empty($uData['mobile'])) {
                    if (!preg_match("/^[00|\+|0]\d{10,16}$/", $uData['mobile'])) {
                        return Jaws_Error::raiseError(
                            Jaws::t('ERROR_INVALID_MOBILE_NUMBER'),
                            __FUNCTION__,
                            JAWS_ERROR_NOTICE
                        );
                    }
                }
            }
        }

        // get user information, we need it for rename avatar
        $user = $this->getUser(
            $uid,
            0,
            array('default' => true, 'account' => true, 'personal' => true)
        );
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        // at least one of email or mobile or email
        if (((array_key_exists('email', $uData) && empty($uData['email'])) ||
            (!array_key_exists('email', $uData) && empty($user['email']))) &&
            ((array_key_exists('mobile', $uData) && empty($uData['mobile'])) ||
            (!array_key_exists('mobile', $uData) && empty($user['mobile'])))
        ) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INCOMPLETE_FIELDS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // if currently a user logged
        
        if (isset($this->app) && property_exists($this->app, 'session') && $this->app->session->user->logged) {
            // other users can't modify the god user
            if (JAWS_GODUSER == $user['id'] && $this->app->session->user->id != $user['id']) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_ACCESS_DENIED'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }

            // not superadmin cant set/modify superadmin users 
            if (!$this->app->session->user->superadmin) {
                unset($uData['superadmin']);
                // non-superadmin user can't change properties of superadmin users
                if ($user['superadmin']) {
                    return Jaws_Error::raiseError(
                        Jaws::t('ERROR_ACCESS_DENIED'),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE
                    );
                }
            }
        }

        if (array_key_exists('username', $uData) && ($uData['username'] !== $user['username'])) {
            // check reserved users
            $reservedUsers = preg_split("/\n|\r|\n\r/", $this->app->registry->fetch('reserved_users', 'Users'));
            if (in_array($uData['username'], $reservedUsers)) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_RESERVED_USERNAME', substr(strrchr($uData['username'], '@'), 1)),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }

            // set new avatar name if username changed
            if (!empty($user['avatar'])) {
                $fileinfo = Jaws_FileManagement_File::pathinfo($user['avatar']);
                if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                    $uData['avatar'] = $uData['username']. '.'. $fileinfo['extension'];
                }
            }
        }

        $uData['last_update'] = time();
        if (isset($uData['status'])) {
            $uData['status'] = (int)$uData['status'];
            if ($uData['status'] == 1) {
                $uData['recovery_key'] = '';
            }
        }
        if (isset($uData['expiry_date'])) {
            if (empty($uData['expiry_date'])) {
                $uData['expiry_date'] = 0;
            } else {
                $objDate = Jaws_Date::getInstance();
                $uData['expiry_date'] = $this->app->UserTime2UTC(
                    (int)$objDate->ToBaseDate(preg_split('/[\/\- \:]/', $uData['expiry_date']), 'U')
                );
            }
        }

        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->update($uData)->where('id', $uid)->exec();
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_USERS_ALREADY_EXISTS', $uData['username']));
            }
            return $result;
        }

        // rename avatar name
        if (isset($uData['avatar'])) {
            Jaws_FileManagement_File::delete(AVATAR_PATH. $uData['avatar']);
             Jaws_FileManagement_File::rename(
                AVATAR_PATH. $user['avatar'],
                AVATAR_PATH. $uData['avatar']
            );
        }

        if (isset($this->app) && property_exists($this->app, 'session') && $this->app->session->user->id == $uid) {
            if (array_key_exists('username', $uData)) {
                $this->app->session->user = array('username' => $uData['username']);
            }
            if (array_key_exists('nickname', $uData)) {
                $this->app->session->user  = array('nickname' => $uData['nickname']);
            }
            if (array_key_exists('email', $uData)) {
                $this->app->session->user = array('email' => $uData['email']);
            }
            if (array_key_exists('mobile', $uData)) {
                $this->app->session->user = array('mobile' => $uData['mobile']);
            }

            // update user's avatar in current session
            if (isset($uData['avatar'])) {
                $this->app->session->user = array(
                    'avatar' => $this->GetAvatar($uData['avatar'], $uData['email'], 48, $uData['last_update'])
                );
            }
        }

        // Let everyone know a user has been added
        $res = $this->app->listener->Shout(
            'Users',
            'UserChanges',
            array('action' => 'UpdateUser', 'user' => $uid)
        );
        if (Jaws_Error::IsError($res)) {
            return false;
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
        $result = Jaws_ORM::getInstance()->table('users')
            ->update($pData)
            ->where('id', (int)$id)
            ->exec();
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

}