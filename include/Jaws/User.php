<?php
define('PASSWORD_SALT_LENGTH', 24);
define('AVATAR_PATH', JAWS_DATA. 'avatar'. DIRECTORY_SEPARATOR);

/**
 * This class is for Jaws_User table operations
 *
 * @category   User
 * @package    Core
 * @author     Ivan -sk8- Chavero <imcsk8@gluch.org.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_User
{
    /**
     * Get hashed password
     *
     * @access  private
     * @param   string  $password
     * @param   string  $salt
     * @return  string  Returns hashed password
     */
    function GetHashedPassword($password, $salt = null)
    {
        if (is_null($salt)) {
            $salt = substr(md5(uniqid(rand(), true)), 0, PASSWORD_SALT_LENGTH);
        } else {
            $salt = substr($salt, 0, PASSWORD_SALT_LENGTH);
        }

        return $salt . sha1($salt . $password);
    }

    /**
     * Validate a user
     *
     * @access  public
     * @param   string  $user      User to validate
     * @param   string  $password  Password of the user
     * @param   string  $onlyAdmin Only validate for admins
     * @return  bool    Returns true if the user is valid and false if not
     */
    function Valid($user, $password, $onlyAdmin = false)
    {
        $params = array();
        $params['user'] = Jaws_UTF8::strtolower($user);
        $GLOBALS['db']->dbc->loadModule('Function', null, true);
        $username = $GLOBALS['db']->dbc->function->lower('[username]');

        $sql = "
            SELECT [id], [passwd], [superadmin], [bad_passwd_count], [concurrent_logins],
                   [logon_hours], [expiry_date], [last_access], [status]
            FROM [[users]]
            WHERE $username = {user}";

        $types = array('integer', 'text', 'boolean', 'integer', 'integer',
                       'text', 'integer', 'integer', 'integer');
        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (isset($result['id'])) {
            // bad_passwd_count & lockedout time
            if ($result['bad_passwd_count'] >= $GLOBALS['app']->Registry->Get('/policy/passwd_bad_count') &&
               ((time() - $result['last_access']) <= $GLOBALS['app']->Registry->Get('/policy/passwd_lockedout_time')))
            {
                return Jaws_Error::raiseError(_t('GLOBAL_ERROR_LOGIN_LOCKED_OUT'),
                                              __FUNCTION__,
                                              JAWS_ERROR_NOTICE);
            }

            // password
            // compare md5ed password for backward compatibility
            if ($result['passwd'] === Jaws_User::GetHashedPassword($password, $result['passwd']) ||
                trim($result['passwd']) === md5($password))
            {
                // only superadmin
                if ($onlyAdmin && !$result['superadmin']) {
                    return Jaws_Error::raiseError(_t('GLOBAL_ERROR_LOGIN_ONLY_ADMIN'),
                                                  __FUNCTION__,
                                                  JAWS_ERROR_NOTICE);
                }

                // status
                if ($result['status'] !== 1) {
                    return Jaws_Error::raiseError(_t('GLOBAL_ERROR_LOGIN_STATUS_'. $result['status']),
                                                  __FUNCTION__,
                                                  JAWS_ERROR_NOTICE);
                }

                // expiry date
                if (!empty($result['expiry_date']) && $result['expiry_date'] <= time()) {
                    return Jaws_Error::raiseError(_t('GLOBAL_ERROR_LOGIN_EXPIRED'),
                                                  __FUNCTION__,
                                                  JAWS_ERROR_NOTICE);
                }

                // logon hours
                $wdhour = explode(',', $GLOBALS['app']->UTC2UserTime(time(), 'w,G', true));
                $lhByte = hexdec($result['logon_hours']{$wdhour[0]*6 + intval($wdhour[1]/4)});
                if ((pow(2, fmod($wdhour[1], 4)) & $lhByte) == 0) {
                    return Jaws_Error::raiseError(_t('GLOBAL_ERROR_LOGIN_LOGON_HOURS'),
                                                  __FUNCTION__,
                                                  JAWS_ERROR_NOTICE);
                }

                return array('id' => $result['id'],
                            'superadmin' => $result['superadmin'],
                            'concurrent_logins' => $result['concurrent_logins']);

            } else {
                // bad_passwd_count + 1
                $params['id']          = $result['id'];
                $params['bad_count']   = $result['bad_passwd_count'] + 1;
                $params['last_access'] = time();

                $sql = '
                    UPDATE [[users]] SET
                        [last_access]      = {last_access},
                        [bad_passwd_count] = {bad_count}
                    WHERE [id] = {id}';

                $result = $GLOBALS['db']->query($sql, $params);
            }
        }

        return Jaws_Error::raiseError(_t('GLOBAL_ERROR_LOGIN_WRONG'),
                                      __FUNCTION__,
                                      JAWS_ERROR_NOTICE);
    }

    /**
     * Updates the last login time for the given user
     *
     * @param $user_id integer user id of the user being updated
     * @return  bool    true if all is ok, false if error
     */
    function updateLoginTime($user_id)
    {
        $params = array();
        $params['id']    = (int)$user_id;
        $params['count'] = 0;

        $sql = '
            UPDATE [[users]] SET
                [bad_passwd_count] = {count}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::isError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Get the info of an user by the username or ID
     *
     * @access  public
     * @param   mixed   $user  The username or ID
     * @return  mixed   Returns an array with the info of the user and false on error
     */
    function GetUser($user, $account = true, $personal = false, $preferences = false, $extra = false)
    {
        $sql = 'SELECT [id]';
        $types = array('integer');

        if ($account) {
            $sql .= ', [username], [nickname], [email], [superadmin], [concurrent_logins], [logon_hours],
                       [expiry_date], [registered_date], [status], [last_update]';
            $types = array_merge($types, array('text', 'text', 'text', 'boolean', 'integer', 'text',
                                               'integer', 'integer', 'integer', 'integer'));
        }
        if ($personal) {
            $sql .= ', [fname], [lname], [gender], [dob], [url], [avatar],
                       [privacy], [about], [occupation], [interests]';
            $types = array_merge($types, array('text', 'text', 'integer', 'timestamp', 'text', 'text',
                                               'boolean', 'text', 'text', 'text'));
        }
        if ($preferences) {
            $sql .= ', [language], [theme], [editor], [timezone]';
            $types = array_merge($types, array('text', 'text', 'text', 'text'));
        }
        if ($extra) {
            //
        }

        $params = array();
        if (is_int($user)) {
            $params['id'] = $user;
            $sql .= '
                FROM [[users]]
                WHERE [id] = {id}';
        } else {
            $params['user'] = Jaws_UTF8::strtolower($user);
            $GLOBALS['db']->dbc->loadModule('Function', null, true);
            $username = $GLOBALS['db']->dbc->function->lower('[username]');
            $sql .= "
                FROM [[users]]
                WHERE $username = {user}";
        }

        return $GLOBALS['db']->queryRow($sql, $params, $types);
    }

    /**
     * Get the info of an user(s) by the email address
     *
     * @access  public
     * @param   int     $email  The email address
     * @return  mixed   Returns an array with the info of the user(s) and false on error
     */
    function GetUserInfoByEmail($email)
    {
        $params = array();
        $params['email'] = Jaws_UTF8::strtolower($email);

        $GLOBALS['db']->dbc->loadModule('Function', null, true);
        $email = $GLOBALS['db']->dbc->function->lower('[email]');

        $sql = "
            SELECT [id], [username], [nickname], [email], [superadmin], [status]
            FROM [[users]]
            WHERE $email = {email}";

        $types = array('integer', 'text', 'text', 'text', 'boolean', 'integer');
        return $GLOBALS['db']->queryAll($sql, $params, $types);
    }

    /**
     * Check and email address already exists
     *
     * @access  public
     * @param   string  $email      The email address
     * @param   int     $exclude    Excluded user ID
     * @return  mixed   Returns an array with the info of the user and false on error
     */
    function UserEmailExists($email, $exclude = 0)
    {
        $params = array();
        $params['id']    = $exclude;
        $params['email'] = Jaws_UTF8::strtolower($email);

        $sql = '
            SELECT COUNT([id])
            FROM [[users]]
            WHERE
                [email] = {email}
              AND
                [id] <> {id}';

        $howmany = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($howmany) || empty($howmany)) {
            return false;
        }

        return true;
    }

    /**
     * Get the avatar url
     * @access  public
     * @param   string   $avatar    User's avatar
     * @param   string   $email     User's email address
     * @param   integer  $size      Avatar size
     * @param   integer  $time      An integer for force browser to refresh it cache
     * @return  string   Url to avatar image
     */
    function GetAvatar($avatar, $email, $size = 48, $time = '')
    {
        if (empty($avatar) || !file_exists(AVATAR_PATH . $avatar)) {
            require_once JAWS_PATH . 'include/Jaws/Gravatar.php';
            $uAvatar = Jaws_Gravatar::GetGravatar($email, $size);
        } else {
            $uAvatar = $GLOBALS['app']->getDataURL(). "avatar/$avatar";
            $uAvatar.= !empty($time)? "?$time" : '';
        }

        return $uAvatar;
    }

    /**
     * Get the info of a group
     *
     * @access  public
     * @param   mixed   $group  The group ID/Name
     * @return  mixed   Returns an array with the info of the group and false on error
     */
    function GetGroup($group)
    {
        $sql = '
            SELECT
                [id], [name], [title], [description], [enabled]
            FROM [[groups]]
            ';

        $params = array();
        if (is_int($group)) {
            $params['id'] = $group;
            $sql .= 'WHERE [id] = {id}';
        } else {
            $params['group'] = Jaws_UTF8::strtolower($group);
            $GLOBALS['db']->dbc->loadModule('Function', null, true);
            $groupname = $GLOBALS['db']->dbc->function->lower('[name]');
            $sql .= "WHERE $groupname = {group}";
        }

        $types = array('integer', 'text', 'text', 'text', 'boolean');
        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Get list of users
     *
     * @access  public
     * @param   mixed   $group      Group ID of users
     * @param   mixed   $superadmin Type of user(null = all types, true = superadmin, false = normal)
     * @param   int     $status     User's status (null: all users, 0: disabled, 1: enabled, 2: not verified)
     * @param   string  $term       Search term(searched in username, nickname and email)
     * @param   string  $orderBy    Field to order by
     * @return  array   Returns an array of the available users and false on error
     */
    function GetUsers($group = false, $superadmin = null, $status = null, $term = '', $orderBy = '[nickname]',
                      $limit = 0, $offset = null)
    {
        $fields  = array(
            '[id]',
            '[id] DESC',
            '[username]',
            '[username] DESC',
            '[nickname]',
            '[nickname] DESC',
            '[email]');
        if (!in_array($orderBy, $fields)) {
            $GLOBALS['log']->Log(JAWS_LOG_WARNING, _t('GLOBAL_ERROR_UNKNOWN_COLUMN'));
            $orderBy = '[username]';
        }

        $params = array();
        $params['true']       = true;
        $params['gid']        = $group;
        $params['superadmin'] = (bool)$superadmin;
        $params['status']     = (int)$status;

        $sql = '
            SELECT
                [[users]].[id], [username], [email], [url], [nickname], [fname], [lname],
                [superadmin], [language], [theme], [editor], [timezone], [[users]].[status]
            FROM [[users]]';

        $types = array('integer', 'text', 'text', 'text', 'text', 'text', 'text',
                       'boolean', 'text', 'text', 'text', 'text', 'integer');
        if ($group !== false) {
            $sql .= '
                INNER JOIN [[users_groups]] ON [[users_groups]].[user_id] = [[users]].[id]
                WHERE [[users_groups]].[group_id] = {gid}';
        } else {
            $sql .= '
                WHERE {true} = {true}';
        }

        if (!is_null($superadmin)) {
            $sql .= " AND [superadmin] = {superadmin}";
        }

        if (!is_null($status)) {
            $sql .= ' AND [[users]].[status] = {status}';
        }

        if (!empty($term)) {
            $userTerm = $GLOBALS['db']->dbc->datatype->matchPattern(
                                                        array(1 => '%', $term, '%'),
                                                        'ILIKE',
                                                        '[[users]].[username]');
            $nickTerm = $GLOBALS['db']->dbc->datatype->matchPattern(
                                                        array(1 => '%', $term, '%'),
                                                        'ILIKE',
                                                        '[[users]].[nickname]');
            $emailTerm = $GLOBALS['db']->dbc->datatype->matchPattern(
                                                        array(1 => '%', $term, '%'),
                                                        'ILIKE',
                                                        '[[users]].[email]');
            $sql.= " AND ($userTerm OR $nickTerm OR $emailTerm)";
        }

        $sql .= '
            ORDER BY [[users]].'. $orderBy;

        if (!empty($limit)) {
            $result = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return false;
            }
        }

        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Get count of users
     *
     * @access  public
     * @param   mixed   $group      Group ID of users
     * @param   mixed   $superadmin Type of user(null = all types, true = superadmin, false = normal)
     * @param   int     $status     user's status (null: all users, 0: disabled, 1: enabled, 2: not verified)
     * @param   string  $term       Search term(searched in username, nickname and email)
     * @return  int     Returns users count
     */
    function GetUsersCount($group = false, $superadmin = null, $status = null, $term = '')
    {
        $params = array();
        $params['true']       = true;
        $params['gid']        = $group;
        $params['superadmin'] = (bool)$superadmin;
        $params['status']     = (int)$status;

        $sql = '
            SELECT
                COUNT([[users]].[id])
            FROM [[users]]';

        if ($group !== false) {
            $sql .= '
                INNER JOIN [[users_groups]] ON [[users_groups]].[user_id] = [[users]].[id]
                WHERE [[users_groups]].[group_id] = {gid}';
        } else {
            $sql .= '
                WHERE {true} = {true}';
        }

        if (!is_null($superadmin)) {
            $sql .= " AND [superadmin] = {superadmin}";
        }

        if (!is_null($status)) {
            $sql .= ' AND [[users]].[status] = {status}';
        }

        if (!empty($term)) {
            $userTerm = $GLOBALS['db']->dbc->datatype->matchPattern(
                                                        array(1 => '%', $term, '%'),
                                                        'ILIKE',
                                                        '[[users]].[username]');
            $nickTerm = $GLOBALS['db']->dbc->datatype->matchPattern(
                                                        array(1 => '%', $term, '%'),
                                                        'ILIKE',
                                                        '[[users]].[nickname]');
            $emailTerm = $GLOBALS['db']->dbc->datatype->matchPattern(
                                                        array(1 => '%', $term, '%'),
                                                        'ILIKE',
                                                        '[[users]].[email]');
            $sql.= " AND ($userTerm OR $nickTerm OR $emailTerm)";
        }

        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return 0;
        }

        return (int)$result;
    }

    /**
     * Get a list of all groups
     *
     * @access  public
     * @param   bool    $enabled    enabled groups?(null for both)
     * @param   string  $orderBy    field to order by
     * @return  array   Returns an array of the available groups and false on error
     */
    function GetGroups($enabled = null, $orderBy = 'name', $limit = 0, $offset = null)
    {
        $fields  = array('id', 'name', 'title');
        if (!in_array($orderBy, $fields)) {
            $GLOBALS['log']->Log(JAWS_LOG_WARNING, _t('GLOBAL_ERROR_UNKNOWN_COLUMN'));
            $orderBy = 'name';
        }

        if (!empty($limit)) {
            $result = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        $params = array();
        $params['enabled'] = (bool)$enabled;

        $sql = '
            SELECT
                [id], [name], [title], [description], [enabled]
            FROM [[groups]]
            ';

        if (!is_null($enabled)) {
            $sql .= 'WHERE [enabled] = {enabled} ';
        }

        $sql .= 'ORDER BY [' . $orderBy. ']';
        $types = array('integer', 'text', 'text', 'text', 'boolean');
        return $GLOBALS['db']->queryAll($sql, $params, $types);
    }

    /**
     * Get count of groups
     *
     * @access  public
     * @param   bool    $enabled    enabled groups?(null for both)
     * @return  int     Returns groups count
     */
    function GetGroupsCount($enabled = null)
    {
        $params = array();
        $params['enabled'] = (bool)$enabled;

        $sql = '
            SELECT
                COUNT([id])
            FROM [[groups]]
            ';

        if (!is_null($enabled)) {
            $sql .= 'WHERE [enabled] = {enabled} ';
        }

        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return 0;
        }

        return (int)$result;
    }

    /**
     * Get a list of groups where a user is
     *
     * @access  public
     * @param   mixed  $user  Username or UserID
     * @return  array  Returns an array of the available groups and false on error
     */
    function GetGroupsOfUser($user)
    {
        $params  = array();
        $params['user'] = $user;
        $sql = '
            SELECT
                [[groups]].[id]
            FROM [[users_groups]]
            INNER JOIN [[users]]  ON [[users]].[id] =  [[users_groups]].[user_id]
            INNER JOIN [[groups]] ON [[groups]].[id] = [[users_groups]].[group_id]
            ';
        if (is_int($user)) {
            $sql .= 'WHERE [[users]].[id] = {user}';
        } else {
            $sql .= 'WHERE [[users]].[username] = {user}';
        }

        return $GLOBALS['db']->queryCol($sql, $params);
    }

    /**
     * Adds a new user
     *
     * @access  public
     * @param   string  $username   The username
     * @param   string  $nickname   User's display name
     * @param   string  $email      User's email
     * @param   string  $password   User's password
     * @param   string  $superadmin Is superadmin (superadmin or normal)
     * @param   int     $status     User's status
     * @param   int     $concurrent_logins  Concurrent logins limitation
     * @param   string  $logon_hours        Logon hours
     * @param   string  $expiry_date        Expiry date
     * @return  bool    Returns true if user was successfully added, false if not
     */
    function AddUser($username, $nickname, $email, $password, $superadmin = false,
                     $status = 1, $concurrent_logins = 0, $logon_hours = null, $expiry_date = null)
    {
        // username
        $username = trim($username, '-_.@');
        if (!preg_match('/^[[:alnum:]-_.@]{3,32}$/', $username)) {
            return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INVALID_USERNAME'),
                                          __FUNCTION__,
                                          JAWS_ERROR_NOTICE);
        }
        $username = strtolower($username);

        // nickname
        $nickname = $GLOBALS['app']->UTF8->trim($nickname);
        if (empty($nickname)) {
            return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                                          __FUNCTION__,
                                          JAWS_ERROR_NOTICE);
        }

        // email
        $email = trim($email);
        if (!preg_match ("/^[[:alnum:]-_.]+\@[[:alnum:]-_.]+\.[[:alnum:]-_]+$/", $email)) {
            return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INVALID_EMAIL_ADDRESS'),
                                          __FUNCTION__,
                                          JAWS_ERROR_NOTICE);
        }
        $email = strtolower($email);

        // password & complexity
        $min = (int)$GLOBALS['app']->Registry->Get('/policy/passwd_min_length');
        $min = ($min == 0)? 1 : $min;
        if (!preg_match("/^[[:print:]]{{$min},24}$/", $password)) {
            return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INVALID_PASSWORD', $min),
                                          __FUNCTION__,
                                          JAWS_ERROR_NOTICE);
        }

        if ($GLOBALS['app']->Registry->Get('/policy/passwd_complexity') == 'yes') {
            if (!preg_match('/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])(?=.*[[:punct:]])/', $password)) {
                return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INVALID_COMPLEXITY'),
                                              __FUNCTION__,
                                              JAWS_ERROR_NOTICE);
            }
        }

        $params = array();
        $params['username']          = $username;
        $params['nickname']          = $nickname;
        $params['email']             = $email;
        $params['password']          = Jaws_User::GetHashedPassword($password);
        $params['superadmin']        = (bool)$superadmin;
        $params['status']            = (int)$status;
        $params['last_update']       = time();
        $params['concurrent_logins'] = (int)$concurrent_logins;
        $params['logon_hours']       = empty($logon_hours)? str_pad('', 42, 'F') : $logon_hours;
        $params['expiry_date']       = 0;
        if (!empty($expiry_date)) {
            $objDate = $GLOBALS['app']->loadDate();
            $expiry_date = (int)$objDate->ToBaseDate(preg_split('/[- :]/', $expiry_date), 'U');
            $params['expiry_date'] = $GLOBALS['app']->UserTime2UTC($expiry_date);
        }

        $sql = '
            INSERT INTO [[users]]
                ([username], [nickname], [email], [passwd], [superadmin],
                 [concurrent_logins], [logon_hours], [expiry_date], [status], [last_update])
            VALUES
                ({username}, {nickname}, {email}, {password}, {superadmin},
                 {concurrent_logins}, {logon_hours}, {expiry_date}, {status}, {last_update})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_USERS_ALREADY_EXISTS', $username));
            }
            return $result;
        }

        // Fetch the id of the user that was just created
        $id = $GLOBALS['db']->lastInsertID('users', 'id');
        if (Jaws_Error::IsError($id)) {
            return false;
        }

        // Let everyone know a user has been added
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onAddUser', $id);
        if (Jaws_Error::IsError($res) || !$res) {
            return false;
        }

        return $id;
    }

    /**
     * Update the info of an user
     *
     * @access  public
     * @param   int     $id         User's ID
     * @param   string  $username   The username
     * @param   string  $nickname   User's display name
     * @param   string  $email      User's email
     * @param   string  $password   User's password
     * @param   bool    $superadmin Is superadmin
     * @param   int     $status     User's status
     * @param   int     $concurrent_logins  Concurrent logins limitation
     * @param   string  $logon_hours        Logon hours
     * @param   string  $expiry_date        Expiry date
     * @return  bool    Returns true if user was successfully updated, false if not
     */
    function UpdateUser($id, $username, $nickname, $email, $password = null, $superadmin = null,
                        $status = null, $concurrent_logins = null, $logon_hours = null, $expiry_date = null)
    {
        // username
        $username = trim($username, '-_.@');
        if (!preg_match('/^[[:alnum:]-_.@]{3,32}$/', $username)) {
            return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INVALID_USERNAME'),
                                          __FUNCTION__,
                                          JAWS_ERROR_NOTICE);
        }
        $username = strtolower($username);

        // nickname
        $nickname = $GLOBALS['app']->UTF8->trim($nickname);
        if (empty($nickname)) {
            return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                                          __FUNCTION__,
                                          JAWS_ERROR_NOTICE);
        }

        // email
        $email = trim($email);
        if (!preg_match ("/^[[:alnum:]-_.]+\@[[:alnum:]-_.]+\.[[:alnum:]-_]+$/", $email)) {
            return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INVALID_EMAIL_ADDRESS'),
                                          __FUNCTION__,
                                          JAWS_ERROR_NOTICE);
        }
        $email = strtolower($email);

        // password & complexity
        if (!is_null($password) && $password !== '') {
            $min = (int)$GLOBALS['app']->Registry->Get('/policy/passwd_min_length');
            if (!preg_match("/^[[:print:]]{{$min},24}$/", $password)) {
                return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INVALID_PASSWORD', $min),
                                              __FUNCTION__,
                                              JAWS_ERROR_NOTICE);
            }

            if ($GLOBALS['app']->Registry->Get('/policy/passwd_complexity') == 'yes') {
                if (!preg_match('/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])(?=.*[[:punct:]])/', $password)) {
                    return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INVALID_COMPLEXITY'),
                                                  __FUNCTION__,
                                                  JAWS_ERROR_NOTICE);
                }
            }
        }

        // get user information, we need it for rename avatar
        $user = Jaws_User::GetUser((int)$id, true, true);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        $params = array();
        $params['id']                = $id;
        $params['username']          = $username;
        $params['nickname']          = $nickname;
        $params['email']             = $email;
        $params['password']          = Jaws_User::GetHashedPassword($password);
        $params['superadmin']        = (bool)$superadmin;
        $params['status']            = (int)$status;
        $params['last_update']       = time();
        $params['concurrent_logins'] = (int)$concurrent_logins;
        $params['logon_hours']       = empty($logon_hours)? str_pad('', 42, 'F') : $logon_hours;
        $params['expiry_date']       = 0;
        if (!empty($expiry_date)) {
            $objDate = $GLOBALS['app']->loadDate();
            $expiry_date = (int)$objDate->ToBaseDate(preg_split('/[- :]/', $expiry_date), 'U');
            $params['expiry_date'] = $GLOBALS['app']->UserTime2UTC($expiry_date);
        }

        // set new avatar name if username changed
        if (($username !== $user['username']) && !empty($user['avatar'])) {
            $fileinfo = pathinfo($user['avatar']);
            if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                $params['avatar'] = $username. '.'. $fileinfo['extension'];
            }
        }

        $sql = '
            UPDATE [[users]] SET
                [username] = {username},
                [nickname] = {nickname},
                [email] = {email} ';
        if (!is_null($password) && $password !== '') {
            $sql .= ', [passwd] = {password} ';
        }
        if (!is_null($superadmin)) {
            $sql .= ', [superadmin] = {superadmin} ';
        }
        if (!is_null($concurrent_logins)) {
            $sql .= ', [concurrent_logins] = {concurrent_logins} ';
        }
        if (!is_null($expiry_date)) {
            $sql .= ', [expiry_date] = {expiry_date} ';
        }
        if (!is_null($logon_hours)) {
            $sql .= ', [logon_hours] = {logon_hours} ';
        }
        if (isset($params['avatar'])) {
            $sql .= ', [avatar] = {avatar} ';
        }
        if (!is_null($status)) {
            $sql .= ', [status] = {status} ';
        }
        $sql .= ', [last_update] = {last_update} WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_USERS_ALREADY_EXISTS', $username));
            }
            return $result;
        }

        // rename avatar name
        if (isset($params['avatar'])) {
            Jaws_Utils::Delete(AVATAR_PATH. $params['avatar']);
            @rename(AVATAR_PATH. $user['avatar'],
                    AVATAR_PATH. $params['avatar']);
        }

        if (isset($GLOBALS['app']->Session) && $GLOBALS['app']->Session->GetAttribute('user') == $id) {
            $GLOBALS['app']->Session->SetAttribute('username', $username);
            $GLOBALS['app']->Session->SetAttribute('nickname', $nickname);
            $GLOBALS['app']->Session->SetAttribute('email',    $email);
            if (isset($params['avatar'])) {
                $GLOBALS['app']->Session->SetAttribute(
                    'avatar',
                    $this->GetAvatar($v, $email, 48, $params['last_update'])
                );
            }
        }

        // Let everyone know a user has been updated
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onUpdateUser', $id);
        if (Jaws_Error::IsError($res) || !$res) {
            return false;
        }

        return true;
    }

    /**
     * Update personal information of a user such as fname, lname, gender, etc..
     *
     * @access  public
     * @param   int     $id    User's ID
     * @param   array   $info  Personal information
     * @return  bool    Returns true on success, false on failure
     */
    function UpdatePersonalInfo($id, $info = array())
    {
        $validInfo = array('fname', 'lname', 'gender', 'dob', 'url', 'avatar', 'privacy');
        $params = array();
        $params['last_update'] = time();
        $updateStr = '';
        foreach($info as $k => $v) {
            if (in_array($k, $validInfo)) {
                if (!is_null($v)) {
                    $params[$k] = $v;
                    $updateStr.= '['. $k . '] = {'.$k.'}, ';
                }
            }
        }

        if (array_key_exists('avatar', $params)) {
            // get user information
            $user = Jaws_User::GetUser((int)$id, true, true);
            if (Jaws_Error::IsError($user) || empty($user)) {
                return false;
            }

            if (!empty($user['avatar'])) {
                Jaws_Utils::Delete(AVATAR_PATH. $user['avatar']);
            }

            if (!empty($params['avatar'])) {
                $fileinfo = pathinfo($params['avatar']);
                if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                    if (!in_array($fileinfo['extension'], array('gif','jpg','jpeg','png'))) {
                        return false;
                    } else {
                        $new_avatar = $user['username']. '.'. $fileinfo['extension'];
                        @rename(Jaws_Utils::upload_tmp_dir(). '/'. $params['avatar'],
                                AVATAR_PATH. $new_avatar);
                        $params['avatar'] = $new_avatar;
                    }
                }
            }
        }

        if (count($params) > 0) {
            $params['id'] = $id;
            $updateStr.= '[last_update] = {last_update}';
            $sql = 'UPDATE [[users]] SET '. $updateStr. ' WHERE [id] = {id}';
            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            if (isset($GLOBALS['app']->Session) && $GLOBALS['app']->Session->GetAttribute('user') == $id) {
                foreach($params as $k => $v) {
                    if ($k == 'avatar') {
                        $GLOBALS['app']->Session->SetAttribute(
                            $k,
                            $this->GetAvatar($v, $user['email'], 48, $params['last_update'])
                        );
                    } else {
                        $GLOBALS['app']->Session->SetAttribute($k, $v);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Update advanced options of a user such as language, theme, editor, etc..
     *
     * @access  public
     * @param   int     $id    User's ID
     * @param   array   $opts  Advanced options
     * @return  bool    Returns true on success, false on failure
     */
    function UpdateAdvancedOptions($id, $opts = array())
    {
        $validOptions = array('language', 'theme', 'editor', 'timezone');
        $params = array();
        $params['last_update'] = time();
        $updateStr    = '';
        foreach($opts as $k => $v) {
            if (in_array($k, $validOptions)) {
                $params[$k] = $v;
                $updateStr.= '['. $k . '] = {'.$k.'}, ';
            }
        }

        if (count($params) > 0) {
            $params['id'] = $id;
            $updateStr.= '[last_update] = {last_update}';
            $sql = 'UPDATE [[users]] SET '. $updateStr. ' WHERE [id] = {id}';
            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                return false;
            }

            if (isset($GLOBALS['app']->Session) && $GLOBALS['app']->Session->GetAttribute('user') == $id) {
                foreach($params as $k => $v) {
                    $GLOBALS['app']->Session->SetAttribute($k, $v);
                }
            }
        }
        return true;
    }

    /**
     * Adds a new group
     *
     * @access  public
     * @param   string  $name        Group's name
     * @param   string  $title       Group's title
     * @param   string  $description Group's description
     * @param   bool    $enabled     Group's status
     * @param   bool    $removable   (Optional) Can the group be removed by users (via UI)?
     * @return  bool    Returns true if group  was sucessfully added, false if not
     */
    function AddGroup($name, $title, $description, $enabled, $removable = true)
    {
        $params = array();
        $params['name']        = $name;
        $params['title']       = $title;
        $params['description'] = $description;
        $params['removable']   = (bool)$removable;
        $params['enabled']     = (bool)$enabled;

        $sql = '
            INSERT INTO [[groups]]
                ([name], [title], [description], [removable], [enabled])
            VALUES
                ({name}, {title}, {description}, {removable}, {enabled})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        // Fetch the id of the group that was just created
        $id = $GLOBALS['db']->lastInsertID('groups', 'id');
        if (Jaws_Error::IsError($id)) {
            return false;
        }

        // Let everyone know a group has been added
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onAddGroup', $id);
        if (Jaws_Error::IsError($res) || !$res) {
            //do nothing
        }

        return $id;
    }

    /**
     * Update the info of a group
     *
     * @access  public
     * @param   int     $id          Group's ID
     * @param   string  $name        Group's title
     * @param   string  $title       Group's name
     * @param   string  $description Group's description
     * @param   bool    $enabled     Group's status
     * @return  bool    Returns true if group was sucessfully updated, false if not
     */
    function UpdateGroup($id, $name, $title, $description, $enabled)
    {
        $params = array();
        $params['id']          = $id;
        $params['name']        = $name;
        $params['title']       = $title;
        $params['description'] = $description;
        $params['enabled']     = (bool)$enabled;

        $sql = '
            UPDATE [[groups]] SET
                [name]        = {name},
                [title]       = {title},
                [description] = {description},
                [enabled]     = {enabled}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        // Let everyone know a group has been updated
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onUpdateGroup', $id);
        if (Jaws_Error::IsError($res) || !$res) {
            //do nothing
        }

        return true;
    }

    /**
     * Deletes an user
     *
     * @access  public
     * @param   int     $id     User's ID
     * @return  bool    Returns true if user was sucessfully deleted, false if not
     */
    function DeleteUser($id)
    {
        $user = Jaws_User::GetUser((int)$id);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        $params = array();
        $params['id'] = $id;
        $sql = 'DELETE FROM [[users]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $sql = 'DELETE FROM [[users_groups]] WHERE [user_id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $GLOBALS['app']->loadClass('ACL', 'Jaws_ACL');
        $GLOBALS['app']->ACL->DeleteUserACL($user['username']);

        if (isset($GLOBALS['app']->Session)) {
            $res = $GLOBALS['app']->Session->DeleteUserSessions($id);
            if (!$res) {
                return false;
            }
        }

        // Let everyone know that a user has been deleted
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onDeleteUser', $id);
        if (Jaws_Error::IsError($res) || !$res) {
            return false;
        }

        return true;
    }


    /**
     * Deletes a group
     *
     * @access  public
     * @param   int     $id     Group's ID
     * @return  bool    Returns true if group was sucessfully deleted, false if not
     */
    function DeleteGroup($id)
    {
        $params = array();
        $params['id'] = $id;
        $params['removable'] = true;

        $sql = '
            DELETE FROM [[groups]]
            WHERE
                [id] = {id}
              AND
                [removable]';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result) || empty($result)) {
            return false;
        }

        $sql = 'DELETE FROM [[users_groups]] WHERE [group_id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $GLOBALS['app']->ACL->DeleteGroupACL($id);

        // Let everyone know a group has been deleted
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onDeleteGroup', $id);
        if (Jaws_Error::IsError($res) || !$res) {
            return false;
        }

        return true;
    }

    /**
     * Adds an user to a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @return  bool    Returns true if user was sucessfully added to the group, false if not
     */
    function AddUserToGroup($user, $group)
    {
        $params = array();
        $params['user']  = $user;
        $params['group'] = $group;

        $sql = '
            INSERT INTO [[users_groups]]
                ([user_id], [group_id])
            VALUES
                ({user}, {group})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Deletes an user from a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @return  bool    Returns true if user was sucessfully deleted from a group, false if not
     */
    function DeleteUserFromGroup($user, $group)
    {
        $params = array();
        $params['user']  = $user;
        $params['group'] = $group;

        $sql = '
            DELETE FROM [[users_groups]]
            WHERE
                [user_id] = {user}
              AND
                [group_id] = {group}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if a user is in a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @return  bool    Returns true if user in in the group or false if not
     */
    function UserIsInGroup($user, $group)
    {
        $params = array();
        $params['user']  = $user;
        $params['group'] = $group;

        $sql = '
            SELECT COUNT([user_id])
            FROM [[users_groups]]
            WHERE
                [user_id] = {user}
              AND
                [group_id] = {group}';

        $howmany = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($howmany)) {
            return false;
        }

        return ($howmany == '0') ? false : true;
    }

    /**
     * Returns the ID of a user by a certain verification key
     *
     * @access  public
     * @param   string  $key  Secret key
     * @return  bool    Success/Failure
     */
    function GetIDByVerificationKey($key)
    {
        $key = trim($key);
        if (empty($key)) {
            return false;
        }

        $params        = array();
        $params['key'] = $key;

        $sql = ' SELECT [id] FROM [[users]] WHERE [verification_key] = {key}';
        $uid = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($uid) || empty($uid)) {
            return false;
        }

        return $uid;
    }

    /**
     * Update the activation key of a certain user with a given (or auto-generated)
     * secret key (MD5)
     *
     * @access  public
     * @param   int     $uid  User's ID
     * @param   string  $key  (Optional) Secret key
     * @return  bool    Success/Failure
     */
    function UpdateVerificationKey($uid, $key = '')
    {
        if (empty($key)) {
            $key = md5(uniqid(rand(), true)) . time() . floor(microtime()*1000);
        }

        $params = array();
        $params['key'] = $key;
        $params['id']  = (int)$uid;

        $sql = '
            UPDATE [[users]] SET
                [verification_key] = {key}
            WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::isError($result)) {
            return $result;
        }

        return true;
    }

}