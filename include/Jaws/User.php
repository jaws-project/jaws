<?php
define('AVATAR_PATH', ROOT_DATA_PATH. 'avatar/');

/**
 * This class is for Jaws_User table operations
 *
 * @category   User
 * @package    Core
 * @author     Ivan -sk8- Chavero <imcsk8@gluch.org.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_User
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Constructor
     *
     * @access  protected
     * @return  void
     */
    private function __construct()
    {
        $this->app = Jaws::getInstance();
    }

    /**
     * Creates the Jaws_User instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @return  object returns the instance
     */
    static function getInstance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new Jaws_User();
        }

        return $instance;
    }

    /**
     * Get hashed password
     *
     * @access  public
     * @param   string  $password
     * @param   string  $salt
     * @return  string  Returns hashed password
     */
    static function GetHashedPassword($password, $salt = null)
    {
        $result = '';
        if (is_null($salt)) {
            $salt = substr(sha1(uniqid(mt_rand(), true)), 0, 16);
            $result = '{SHA512-CRYPT}' . crypt($password, "$6$$salt");
        } else {
            if (substr($salt, 0, 10) === '{CRAM-MD5}') {
                $result = '{CRAM-MD5}' . Jaws_Hash_CRAMMD5::hash($password);
            } elseif (substr($salt, 0, 14) === '{SHA512-CRYPT}') {
                $salt = substr($salt, 17, 16);
                $result = '{SHA512-CRYPT}' . crypt($password, "$6$$salt");
           } elseif (substr($salt, 0, 9) === '{SSHA512}') {
                $salt = substr(base64_decode(substr($salt, 9)), 64);
                $result = '{SSHA512}'. base64_encode(hash('sha512', $password. $salt, true). $salt);
            } elseif (substr($salt, 0, 7) === '{SSHA1}') {
                // old salted sha1 password
                $salt = substr($salt, 7, 24);
                $result = '{SSHA1}'. $salt . sha1($salt . $password);
            } else {
                // very old md5ed password
                $result = '{MD5}'. md5($password);
            }
        }

        return $result;
    }

    /**
     * Verify a user
     *
     * @access  public
     * @param   string  $user      User name/email/mobile
     * @param   string  $password  Password of the user
     * @return  boolean Returns true if the user is valid and false if not
     */
    function VerifyUser($domain, $user, $password)
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
        if ($result['password'] !== Jaws_User::GetHashedPassword($password, $result['password'])) {
            $this->updateLastAccess($result['id'], false);
            // password incorrect event logging
            $this->app->listener->Shout(
                'Users',
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
            $this->app->listener->Shout(
                'Users',
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
            $this->app->listener->Shout(
                'Users',
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
            $this->app->listener->Shout(
                'Users',
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
        $this->updateLastAccess($result['id'], true);
        return $result;

    }

    /**
     * Updates the last login time for the given user
     *
     * @param   int     $user       user id of the user being updated
     * @param   bool    $success    successfully accessed
     * @return  bool    true if all is ok, false if error
     */
    function updateLastAccess($user, $success = true)
    {
        $data['last_access'] = time();
        $usersTable = Jaws_ORM::getInstance()->table('users');
        if ($success) {
            $data['bad_password_count'] = 0;
        } else {
            // increase bad_password_count
            $data['bad_password_count'] = $usersTable->expr('bad_password_count + ?', 1);
        }

        $result = $usersTable->update($data)->where('id', (int)$user)->exec();
        return !Jaws_Error::IsError($result);
    }

    /**
     * Get the info of an user(s) by the email address
     *
     * @access  public
     * @param   int     $domain     Domain Id
     * @param   string  $term       User name/email/mobile
     * @return  mixed   Returns an array with the info of the user or false on error
     */
    function GetUserByTerm($domain, $term)
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
     * Get the info of an user(s) by the email address
     *
     * @access  public
     * @param   string  $term   User name/email/mobile
     * @return  mixed   Returns an array with the info of the user(s) and false on error
     */
    function FindUserByTerm($term)
    {
        return Jaws_ORM::getInstance()->table('users')
            ->select('id:integer', 'domain:integer', 'username', 'nickname', 'email',
                'mobile', 'superadmin:boolean', 'status:integer'
            )->openWhere('username', Jaws_UTF8::strtolower($term))
            ->or()
            ->where('email', Jaws_UTF8::strtolower($term))
            ->or()
            ->closeWhere('mobile', $term)
            ->fetchRow();
    }

    /**
     * Get the info of an user(s) by the email verification key
     *
     * @access  public
     * @param   string  $key  Verification key
     * @return  mixed   Returns an array with the info of the user(s) and false on error
     */
    function GetUserByEmailVerifyKey($key)
    {
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select(
            'id:integer', 'domain:integer', 'username', 'nickname', 'email', 'new_email', 'status:integer'
        );
        $usersTable->where('recovery_key', trim($key));
        return $usersTable->fetchRow();
    }

    /**
     * Check username already exists
     *
     * @access  public
     * @param   string  $username   The username
     * @param   int     $exclude    Excluded user ID
     * @return  mixed   Returns email address exists or not
     */
    function UsernameExists($username, $exclude = 0)
    {
        $howmany = Jaws_ORM::getInstance()->table('users')->select('count(id)')
            ->openWhere()
            ->where('username', Jaws_UTF8::strtolower($username))
            ->or()
            ->where('email', Jaws_UTF8::strtolower($username))
            ->or()
            ->where('mobile', $username)
            ->closeWhere()
            ->and()
            ->where('id', $exclude, '<>')
            ->fetchOne();
        return !empty($howmany);
    }

    /**
     * Check email address already exists
     *
     * @access  public
     * @param   string  $email      The email address
     * @param   int     $exclude    Excluded user ID
     * @return  mixed   Returns email address exists or not
     */
    function UserEmailExists($email, $exclude = 0)
    {
        $howmany = 0;
        $email = trim($email);
        if (!empty($email)) {
            $howmany = Jaws_ORM::getInstance()->table('users')->select('count(id)')
                ->openWhere()
                ->where('email', Jaws_UTF8::strtolower($email))
                ->or()
                ->where('username', Jaws_UTF8::strtolower($email))
                ->closeWhere()
                ->and()
                ->where('id', $exclude, '<>')
                ->fetchOne();
        }
        return !empty($howmany);
    }

    /**
     * Check mobile number already exists
     *
     * @access  public
     * @param   string  $mobile     The mobile number
     * @param   int     $exclude    Excluded user ID
     * @return  bool    Returns mobile number exists or not
     */
    function UserMobileExists($mobile, $exclude = 0)
    {
        $howmany = 0;
        $mobile = trim($mobile);
        if (!empty($mobile)) {
            $howmany = Jaws_ORM::getInstance()->table('users')->select('count(id)')
                ->openWhere()
                ->where('mobile', $mobile)
                ->or()
                ->where('username', Jaws_UTF8::strtolower($mobile))
                ->closeWhere()
                ->and()
                ->where('id', $exclude, '<>')
                ->fetchOne();
        }
        return !empty($howmany);
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
        if (empty($avatar) || !Jaws_FileManagement_File::file_exists(AVATAR_PATH . $avatar)) {
            $uAvatar = Jaws_Gravatar::GetGravatar($email, $size);
        } else {
            $uAvatar = $this->app->getDataURL(). "avatar/$avatar";
            $uAvatar.= !empty($time)? "?$time" : '';
        }

        return $uAvatar;
    }

    /**
     * Adds an user to a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if user was successfully added to the group, false if not
     */
    function AddUserToGroup($user, $group, $owner = 0)
    {
        $objORM = Jaws_ORM::getInstance();
        $group = $objORM->table('groups')
            ->select('id:integer', 'name')
            ->where('owner', (int)$owner)
            ->and()
            ->where('id', $group)
            ->fetchRow();
        if (Jaws_Error::IsError($group) || empty($group)) {
            return $group;
        }

        $result = $objORM->table('users_groups')
            ->insert(array('user' => $user, 'group' => $group['id']))
            ->exec();
        if (!Jaws_Error::IsError($result)) {
            if (isset($this->app) && property_exists($this->app, 'session') && $this->app->session->user->id == $user) {
                // update logged user session
                $user_groups = $this->app->session->user->groups;
                $user_groups[$group['id']] = $group['name'];
                $this->app->session->user = array('groups' => $user_groups);
            }

            // Let everyone know user added to a group
            $res = $this->app->listener->Shout(
                'Users',
                'UserGroupsChanges',
                array('action' => 'AddUserToGroup', 'user' => $user,'group' => $group['id'])
            );
            if (Jaws_Error::IsError($res)) {
                // nothing
            }
        }

        return $result;
    }

    /**
     * Deletes an user from a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if user was sucessfully deleted from a group, false if not
     */
    function DeleteUserFromGroup($user, $group, $owner = 0)
    {
        $objORM = Jaws_ORM::getInstance();
        $result = $objORM->table('groups')
            ->select('id')
            ->where('owner', (int)$owner)
            ->and()
            ->where('id', $group)
            ->fetchOne();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return $result;
        }

        $result = $objORM->table('users_groups')
            ->delete()
            ->where('user', $user)
            ->and()
            ->where('group', $group)
            ->exec();
        if (!Jaws_Error::IsError($result)) {
            if ($this->app->session->user->id == $user) {
                // update logged user session
                $user_groups = $this->app->session->user->groups;
                unset($user_groups[$group]);
                $this->app->session->user = array('groups' => $user_groups);
            }

            // Let everyone know user added to a group
            $res = $this->app->listener->Shout(
                'Users',
                'UserGroupsChanges',
                array('action' => 'DeleteUserFromGroup', 'user' => $user, 'group' => $group)
            );
            if (Jaws_Error::IsError($res)) {
                // nothing
            }
        }
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
        $usrgrpTable = Jaws_ORM::getInstance()->table('users_groups');
        $usrgrpTable->select('count(user):integer');
        $usrgrpTable->where('user', $user)->and()->where('group', $group);
        $howmany = $usrgrpTable->fetchOne();
        if (Jaws_Error::IsError($howmany)) {
            return false;
        }

        return (bool)$howmany;
    }

}