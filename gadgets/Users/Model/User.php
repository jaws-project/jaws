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
                'nickname', 'contact:integer', 'avatar', 'status:integer'
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
            $objORM->and()->where('lower(username)', Jaws_UTF8::strtolower($user));
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
            if (!empty($user['avatar'])) {
                Jaws_FileManagement_File::delete(AVATAR_PATH. $user['avatar']);
            }

            if (!empty($pData['avatar'])) {
                $fileinfo = Jaws_FileManagement_File::pathinfo($pData['avatar']);
                if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                    $new_avatar = $user['username']. '.'. $fileinfo['extension'];
                    Jaws_FileManagement_File::rename(
                        AVATAR_PATH. $pData['avatar'], AVATAR_PATH. $new_avatar
                    );
                    $pData['avatar'] = $new_avatar;
                }
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
            return false;
        }

        return true;
    }

}