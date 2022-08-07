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
     * @return  mixed   Returns an array with the info of the user and Jaws_Error on error
     */
    function get($user, $domain = 0, $fieldsets = array())
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
                'last_access:integer'
            ),
            'personal' => array(
                'fname', 'lname', 'gender', 'ssn', 'dob', 'extra', 'public:boolean', 'privacy:boolean',
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
            ->where('domain', (int)$domain);
        if (is_int($user)) {
            $objORM->and()->where('users.id', $user);
        } else {
            $objORM->and()->where('username', Jaws_UTF8::strtolower($user));
        }

        return $objORM->fetchRow();
    }

    /**
     * Get the info of an user(s) by the email address
     *
     * @access  public
     * @param   int     $domain     Domain Id
     * @param   string  $term       User name/email/mobile
     * @return  mixed   Returns an array with the info of the user or false on error
     */
    function getByTerm($domain, $term)
    {
        return Jaws_ORM::getInstance()
            ->table('users')
            ->select('id:integer', 'domain:integer', 'username', 'nickname', 'email',
                'mobile', 'superadmin:boolean', 'status:integer'
            )->where('domain', (int)$domain)
            ->and()
            ->openWhere('username', Jaws_UTF8::strtolower($term))
            ->or()
            ->where('email', Jaws_UTF8::strtolower($term))
            ->or()
            ->closeWhere('mobile', $term)
            ->fetchRow();
    }

    /**
     * Get list of users
     *
     * @access  public
     * @param   int     $domain     Domain ID // 0: all domains
     * @param   int     $group      Group ID  // 0: all groups
     * @param   array   $filters    Users filters // status, superadmin, term
     *                  ex: array(
     *                      'status'  => 1,
     *                      'term' => 'smith'
     *                  )
     * @param   array   $fieldsets  Users fields sets // default, account, personal, password
     *                  ex: array('account'  => true)
     * @param   array   $orderBy    Field to order by
     *                  ex: array(
     *                      'id'       => true  // ascending,
     *                      'username' => false // descending
     *                  )
     * @param   int     $limit
     * @param   int     $offset
     * @return  array|Jaws_Error    Returns an array of the available users or Jaws_Error on error
     */
    function list(
        $domain = 0, $group = 0,
        $filters = array(), $fieldsets = array(),
        $orderBy = array(), $limit = 0, $offset = null
    ) {
        $columns = array(
            'default'  => array(
                'users.domain:integer', 'users.id:integer', 'username', 'users.email', 'users.mobile',
                'nickname', 'contact:integer', 'avatar:boolean', 'status:integer'
            ),
            'account'  => array(
                'superadmin:boolean', 'concurrents:integer', 'logon_hours',
                'expiry_date:integer', 'registered_date:integer', 'bad_password_count:integer', 
                'last_update:integer', 'last_password_update:integer',
                'last_access:integer'
            ),
            'personal' => array(
                'fname', 'lname', 'gender', 'ssn', 'dob', 'extra', 'public:boolean', 'privacy:boolean',
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
        // group
        if (!empty($group)) {
            $objORM->join('users_groups', 'users_groups.user', 'users.id');
            $objORM->and()->where('group', (int)$group);
        }

        // filters
        $baseFilters = array(
            'term'       => '',
            'status'     => 0,
            'superadmin' => null,
        );
        // remove invalid filters keys
        $filters = array_intersect_key($filters, $baseFilters);
        // set undefined keys by default values
        $filters = array_merge($baseFilters, $filters);
        // status
        $objORM->and()->where('status', (int)$filters['status'], '=', empty($filters['status']));
        // superadmin
        $objORM->and()->where('superadmin', (bool)$filters['superadmin'], '=', is_null($filters['superadmin']));
        // term
        if (!empty($filters['term'])) {
            $term = Jaws_UTF8::strtolower($filters['term']);
            $objORM->and()
                ->openWhere('username', $term, 'like')
                ->or()
                ->where('lower(nickname)', $term, 'like')
                ->or()
                ->where('mobile', $term, 'like')
                ->or()
                ->closeWhere('email', $term, 'like');
        }

        // Order by
        $orders = array();
        if (empty($orderBy)) {
            $orderBy = array('id' => true);
        }
        foreach ($orderBy as $field => $ascending) {
            $orders[] = 'users.'. $field . ' '. ($ascending? 'asc' : 'desc');
        }
        call_user_func_array(array($objORM, 'orderBy'), $orders);

        return $objORM->limit($limit, $offset)->fetchAll();
    }

    /**
     * Get count of users list
     *
     * @access  public
     * @param   int     $domain     Domain ID // 0: all domains
     * @param   int     $group      Group ID  // 0: all groups
     * @param   array   $filters    Users filters // status, superadmin, term
     *                  ex: array(
     *                      'status'  => 1,
     *                      'term' => 'smith'
     *                  )
     * @return  array|Jaws_Error    Returns an array of the available users or Jaws_Error on error
     */
    function listCount($domain = 0, $group = 0, $filters = array())
    {
        $objORM = Jaws_ORM::getInstance()
            ->table('users')
            ->select('count(users.id):integer')
            ->where('domain', (int)$domain, '=', empty($domain));
        // group
        if (!empty($group)) {
            $objORM->join('users_groups', 'users_groups.user', 'users.id');
            $objORM->and()->where('group', (int)$group);
        }

        // filters
        $baseFilters = array(
            'term'       => '',
            'status'     => 0,
            'superadmin' => null,
        );
        // remove invalid filters keys
        $filters = array_intersect_key($filters, $baseFilters);
        // set undefined keys by default values
        $filters = array_merge($baseFilters, $filters);
        // status
        $objORM->and()->where('status', (int)$filters['status'], '=', empty($filters['status']));
        // superadmin
        $objORM->and()->where('superadmin', (bool)$filters['superadmin'], '=', is_null($filters['superadmin']));
        // term
        if (!empty($filters['term'])) {
            $term = Jaws_UTF8::strtolower($filters['term']);
            $objORM->and()
                ->openWhere('username', $term, 'like')
                ->or()
                ->where('lower(nickname)', $term, 'like')
                ->or()
                ->where('mobile', $term, 'like')
                ->or()
                ->closeWhere('email', $term, 'like');
        }

        return $objORM->fetchOne();
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
        $user = $this->get(
            $uid,
            0,
            array('default' => true, 'account' => true, 'password' => true)
        );
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        // check old password
        if ($old_password !== false) {
            if ($user['password'] !== Jaws_Utils::HashedPassword($old_password, $user['password'])) {
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
                    'password' => Jaws_Utils::HashedPassword($new_password),
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
    function add($uData)
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
        $uData['password'] = Jaws_Utils::HashedPassword($uData['password']);
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
                $result->SetMessage($this::t('USERS_ALREADY_EXISTS', $uData['username']));
            }
            return $result;
        }

        // Let everyone know a user has been added
        $res = $this->gadget->event->shout(
            'UserChanges',
            array('action' => 'AddUser', 'user' => $result, 'data' => $uData)
        );
        if (Jaws_Error::IsError($res)) {
            // nothing
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
    function update($uid, $uData)
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
        $user = $this->get(
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
                $result->SetMessage($this::t('USERS_ALREADY_EXISTS', $uData['username']));
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
                    'avatar' => $this->getAvatar($uid)
                );
            }
        }

        // Let everyone know a user has been added
        $res = $this->gadget->event->shout(
            'UserChanges',
            array('action' => 'UpdateUser', 'user' => $uid, 'data' => $uData)
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
        $user = $this->get((int)$id);
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
                        'avatar' => $this->getAvatar($user['id'])
                    );
                } else {
                    $this->app->session->user = array($k => $v);
                }
            }
        }

        // Let everyone know a user has been added
        $res = $this->gadget->event->shout(
            'UserChanges',
            array('action' => 'UpdateUser', 'user' => (int)$id, 'data' => $pData)
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
            // Gravatar  -> $avatar = Jaws_Gravatar::GetGravatar($email, $size);
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
            array('action' => 'UpdateUser', 'user' => (int)$uid, 'data' => $uData)
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
    function delete($uid)
    {
        $objORM = Jaws_ORM::getInstance();
        $user = $this->get(
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
     * Verify a user
     *
     * @access  public
     * @param   string  $user      User name/email/mobile
     * @param   string  $password  Password of the user
     * @return  boolean Returns true if the user is valid and false if not
     */
    function verify($domain, $user, $password)
    {
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->select(
            'id:integer', 'domain:integer', 'username', 'password', 'email', 'mobile',
            'superadmin:boolean', 'nickname', 'ssn', 'dob', 'concurrents:integer',
            'logon_hours', 'expiry_date', 'avatar', 'registered_date', 'last_update',
            'bad_password_count', 'last_password_update', 'last_access', 'status:integer')
            ->where('domain', (int)$domain)
            ->and()
            ->openWhere('username', Jaws_UTF8::strtolower($user))
            ->or()
            ->where('email', Jaws_UTF8::strtolower($user))
            ->or()
            ->closeWhere('mobile', $user)
            ->fetchRow();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_LOGIN_WRONG'),
                401,
                JAWS_ERROR_NOTICE
            );
        }

        // check password
        if ($result['password'] !== Jaws_Utils::HashedPassword($password, $result['password'])) {
            // password incorrect event logging
            $this->gadget->event->shout(
                'Log',
                array(
                    'gadget'   => 'Users',
                    'action'   => 'Login',
                    'domain'   => $result['domain'],
                    'user'     => $result['id'],
                    'username' => $result['username'],
                    'priority' => JAWS_WARNING,
                    'result'   => 401,
                    'status'   => false,
                )
            );
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_LOGIN_WRONG'),
                401,
                JAWS_ERROR_NOTICE
            );
        }
        unset($result['password']);

        // status
        if ($result['status'] !== 1) {
            // forbidden access event logging
            $this->gadget->event->shout(
                'Log',
                array(
                    'gadget'   => 'Users',
                    'action'   => 'Login',
                    'domain'   => $result['domain'],
                    'user'     => $result['id'],
                    'username' => $result['username'],
                    'priority' => JAWS_WARNING,
                    'result'   => 403,
                    'status'   => false,
                )
            );
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_LOGIN_STATUS_'. $result['status']),
                403,
                JAWS_ERROR_NOTICE
            );
        }

        // expiry date
        if (!empty($result['expiry_date']) && $result['expiry_date'] <= time()) {
            // forbidden access event logging
            $this->gadget->event->shout(
                'Log',
                array(
                    'gadget'   => 'Users',
                    'action'   => 'Login',
                    'domain'   => $result['domain'],
                    'user'     => $result['id'],
                    'username' => $result['username'],
                    'priority' => JAWS_WARNING,
                    'result'   => 403,
                    'status'   => false,
                )
            );
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_LOGIN_EXPIRED'),
                403,
                JAWS_ERROR_NOTICE
            );
        }

        // logon hours
        $wdhour = explode(',', $this->app->UTC2UserTime(time(), 'w,G', true));
        $lhByte = hexdec($result['logon_hours'][$wdhour[0]*6 + intval($wdhour[1]/4)]);
        if ((pow(2, fmod($wdhour[1], 4)) & $lhByte) == 0) {
            // forbidden access event logging
            $this->gadget->event->shout(
                'Log',
                array(
                    'gadget'   => 'Users',
                    'action'   => 'Login',
                    'domain'   => $result['domain'],
                    'user'     => $result['id'],
                    'username' => $result['username'],
                    'priority' => JAWS_WARNING,
                    'result'   => 403,
                    'status'   => false,
                )
            );
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_LOGIN_LOGON_HOURS'),
                403,
                JAWS_ERROR_NOTICE
            );
        }

        // update last access
        $this->updateLastAccess($result['id']);
        return $result;

    }

    /**
     * Updates the last login time for the given user
     *
     * @param   int     $user       user id of the user being updated
     * @return  bool    true if all is ok, false if error
     */
    function updateLastAccess($user)
    {
        $data['last_access'] = time();
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->update($data)->where('id', (int)$user)->exec();
        return !Jaws_Error::IsError($result);
    }

    /**
     * Check username/email/mobile already exists
     *
     * @access  public
     * @param   string  $username   The username
     * @param   int     $exclude    Excluded user ID
     * @return  mixed   Returns email address exists or not
     */
    function exists($term, $exclude = 0)
    {
        $howmany = Jaws_ORM::getInstance()->table('users')->select('count(id)')
            ->openWhere()
            ->where('username', Jaws_UTF8::strtolower($term))
            ->or()
            ->where('email', Jaws_UTF8::strtolower($term))
            ->or()
            ->where('mobile', $term)
            ->closeWhere()
            ->and()
            ->where('id', $exclude, '<>')
            ->fetchOne();
        
        return !Jaws_Error::IsError($howmany) && !empty($howmany);
    }

}