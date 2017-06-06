<?php
define('AVATAR_PATH', JAWS_DATA. 'avatar'. DIRECTORY_SEPARATOR);

/**
 * This class is for Jaws_User table operations
 *
 * @category   User
 * @package    Core
 * @author     Ivan -sk8- Chavero <imcsk8@gluch.org.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_User
{
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
            $result = '{CRAM-MD5}' . Jaws_Hash_CRAMMD5::hash($password);
        } else {
            if (substr($salt, 0, 10) === '{CRAM-MD5}') {
                $result = '{CRAM-MD5}' . Jaws_Hash_CRAMMD5::hash($password);
            } elseif (substr($salt, 0, 14) === '{SHA512-CRYPT}') {
                $salt = substr($salt, 17, 16);
                //$salt = substr(sha1(uniqid(mt_rand(), true)), 0, 16);
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
     * @param   string  $user      User name/email
     * @param   string  $password  Password of the user
     * @return  boolean Returns true if the user is valid and false if not
     */
    function VerifyUser($user, $password)
    {
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->select(
            'id:integer', 'domain:integer', 'username', 'password', 'email',
            'superadmin:boolean', 'nickname', 'concurrents:integer',
            'logon_hours', 'expiry_date', 'avatar', 'registered_date', 'last_update',
            'bad_password_count', 'last_password_update', 'last_access', 'status:integer')
            ->where('lower(username)', Jaws_UTF8::strtolower($user))
            ->or()
            ->where('lower(email)', Jaws_UTF8::strtolower($user))
            ->fetchRow();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_WRONG'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // bad_password_count & lockedout time
        $max_password_bad_count = $GLOBALS['app']->Registry->fetch('password_bad_count', 'Policy');
        $password_lockedout_time = $GLOBALS['app']->Registry->fetch('password_lockedout_time', 'Policy');
        if ($result['bad_password_count'] >= $max_password_bad_count &&
           ((time() - $result['last_access']) <= $password_lockedout_time))
        {
            // forbidden access event logging
            $GLOBALS['app']->Listener->Shout(
                'Users',
                'Log',
                array('Users', 'Login', JAWS_WARNING, null, 403, $result['id'])
            );
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_LOCKED_OUT'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // check password
        if ($result['password'] !== Jaws_User::GetHashedPassword($password, $result['password'])) {
            $this->updateLastAccess($result['id'], false);
            // password incorrect event logging
            $GLOBALS['app']->Listener->Shout(
                'Users',
                'Log',
                array('Users', 'Login', JAWS_WARNING, null, 401, $result['id'])
            );
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_WRONG'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        unset($result['password']);

        // status
        if ($result['status'] !== 1) {
            // forbidden access event logging
            $GLOBALS['app']->Listener->Shout(
                'Users',
                'Log',
                array('Users', 'Login', JAWS_WARNING, null, 403, $result['id'])
            );
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_STATUS_'. $result['status']),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // expiry date
        if (!empty($result['expiry_date']) && $result['expiry_date'] <= time()) {
            // forbidden access event logging
            $GLOBALS['app']->Listener->Shout(
                'Users',
                'Log',
                array('Users', 'Login', JAWS_WARNING, null, 403, $result['id'])
            );
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_EXPIRED'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // logon hours
        $wdhour = explode(',', $GLOBALS['app']->UTC2UserTime(time(), 'w,G', true));
        $lhByte = hexdec($result['logon_hours']{$wdhour[0]*6 + intval($wdhour[1]/4)});
        if ((pow(2, fmod($wdhour[1], 4)) & $lhByte) == 0) {
            // forbidden access event logging
            $GLOBALS['app']->Listener->Shout(
                'Users',
                'Log',
                array('Users', 'Login', JAWS_WARNING, null, 403, $result['id'])
            );
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_LOGON_HOURS'),
                __FUNCTION__,
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
     * Get the info of an user by the username or ID
     *
     * @access  public
     * @param   mixed   $user       The username or ID
     * @param   bool    $account    Account information
     * @param   bool    $personal   Personal information
     * @param   bool    $contacts   Contacts information
     * @param   bool    $password   Returns password
     * @return  mixed   Returns an array with the info of the user and false on error
     */
    function GetUser($user, $account = true, $personal = false, $contacts = false, $password = false)
    {
        $columns = array('users.id:integer', 'contact:integer', 'avatar');
        // account information
        if ($account) {
            $columns = array_merge($columns, array('username', 'nickname', 'users.email', 'users.mobile',
                'superadmin:boolean', 'concurrents', 'logon_hours', 'expiry_date', 'registered_date',
                'status:integer', 'last_update', 'bad_password_count', 'last_access',)
            );
        }

        if ($password) {
            $columns = array_merge($columns, array('password'));
        }

        if ($personal) {
            $columns = array_merge($columns, array('fname', 'lname', 'gender', 'ssn', 'dob',
                'public:boolean', 'privacy:boolean', 'signature', 'about', 'experiences', 'occupations',
                'interests',)
            );
        }

        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select($columns);
        if (is_int($user)) {
            $usersTable->where('users.id', $user);
        } else {
            $usersTable->where('lower(username)', Jaws_UTF8::strtolower($user));
        }

        return $usersTable->fetchRow();
    }

    /**
     * Get the contact information of an user by the username or ID
     *
     * @access  public
     * @param   mixed   $user   The username or ID
     * @param   mixed   $cid    The contact or ID
     * @return  mixed   Returns an array with the contact information of the user or Jaws_Error
     */
    function GetUserContact($user, $cid = 0)
    {
        $objORM = Jaws_ORM::getInstance()
            ->table('users_contacts', 'uc')
            ->select('uc.id:integer', 'uc.owner:integer', 'uc.title', 'uc.name', 'uc.tel', 'uc.mobile', 'uc.fax',
                     'uc.url', 'uc.email', 'uc.address', 'uc.note');

        if (!empty($cid)) {
            $objORM->where('uc.owner', $user);
            $objORM->and()->where('uc.id', $cid);
        } else {
            $objORM->join('users', 'users.contact', 'uc.id');

            if (is_int($user)) {
                $objORM->where('uc.owner', $user);
            } else {
                $objORM->where('lower(username)', Jaws_UTF8::strtolower($user));
            }
        }

        $contact = $objORM->fetchRow();

        if (!empty($contact) && !Jaws_Error::IsError($contact)) {
            $tel = json_decode($contact['tel'], true);
            $contact['tel_home'] = isset($tel['home']) ? $tel['home'] : '';
            $contact['tel_work'] = isset($tel['work']) ? $tel['work'] : '';
            $contact['tel_other'] = isset($tel['other']) ? $tel['other'] : '';
            unset($contact['tel']);

            $fax = json_decode($contact['fax'], true);
            $contact['fax_home'] = isset($fax['home']) ? $fax['home'] : '';
            $contact['fax_work'] = isset($fax['work']) ? $fax['work'] : '';
            $contact['fax_other'] = isset($fax['other']) ? $fax['other'] : '';
            unset($contact['fax']);

            $mobile = json_decode($contact['mobile'], true);
            $contact['mobile_home'] = isset($mobile['home']) ? $mobile['home'] : '';
            $contact['mobile_work'] = isset($mobile['work']) ? $mobile['work'] : '';
            $contact['mobile_other'] = isset($mobile['other']) ? $mobile['other'] : '';
            unset($contact['mobile']);

            $url = json_decode($contact['url'], true);
            $contact['url_home'] = isset($url['home']) ? $url['home'] : '';
            $contact['url_work'] = isset($url['work']) ? $url['work'] : '';
            $contact['url_other'] = isset($url['other']) ? $url['other'] : '';
            unset($contact['url']);

            $email = json_decode($contact['email'], true);
            $contact['email_home'] = isset($email['home']) ? $email['home'] : '';
            $contact['email_work'] = isset($email['work']) ? $email['work'] : '';
            $contact['email_other'] = isset($email['other']) ? $email['other'] : '';
            unset($contact['email']);

            $address = json_decode($contact['address'], true);
            $contact['country_home'] = isset($address['home']['country']) ? $address['home']['country'] : '';
            $contact['province_home'] = isset($address['home']['province']) ? $address['home']['province'] : '';
            $contact['city_home'] = isset($address['home']['city']) ? $address['home']['city'] : '';
            $contact['address_home'] = isset($address['home']['address']) ? $address['home']['address'] : '';
            $contact['postal_code_home'] = isset($address['home']['postal_code']) ? $address['home']['postal_code'] : '';

            $contact['country_work'] = isset($address['work']['country']) ? $address['work']['country'] : '';
            $contact['province_work'] = isset($address['work']['province']) ? $address['work']['province'] : '';
            $contact['city_work'] = isset($address['work']['city']) ? $address['work']['city'] : '';
            $contact['address_work'] = isset($address['work']['address']) ? $address['work']['address'] : '';
            $contact['postal_code_work'] = isset($address['work']['postal_code']) ? $address['work']['postal_code'] : '';

            $contact['country_other'] = isset($address['other']['country']) ? $address['other']['country'] : '';
            $contact['province_other'] = isset($address['other']['province']) ? $address['other']['province'] : '';
            $contact['city_other'] = isset($address['other']['city']) ? $address['other']['city'] : '';
            $contact['address_other'] = isset($address['other']['address']) ? $address['other']['address'] : '';
            $contact['postal_code_other'] = isset($address['other']['postal_code']) ? $address['other']['postal_code'] : '';
            unset($contact['address']);
        }

        return $contact;
    }

    /**
     * Get user's contact list
     *
     * @access  public
     * @param   int     $user   The User ID
     * @param   int     $limit  Count of posts to be returned
     * @param   int     $offset Offset of data array
     * @return  mixed   Returns an array with the contact information of the user or Jaws_Error
     */
    function GetUserContacts($user, $limit = 0, $offset = null)
    {
        $objORM = Jaws_ORM::getInstance()
            ->table('users_contacts', 'uc')
            ->select('uc.id:integer', 'uc.owner:integer', 'uc.title', 'uc.name', 'uc.tel', 'uc.mobile', 'uc.fax',
                     'uc.url', 'uc.email', 'uc.address', 'uc.note')
            ->join('users', 'users.id', 'uc.owner')
            ->where('users.id', $user)
            ->limit($limit, $offset);
        return $objORM->fetchAll();
    }

    /**
     * Get user's contact list
     *
     * @access  public
     * @param   int     $user   The User ID
     * @param   array   $ids    Contacts id
     * @return  mixed   Returns an array with the contact information of the user or Jaws_Error
     */
    function DeleteUserContacts($user, $ids)
    {
        return Jaws_ORM::getInstance()->table('users_contacts')
            ->delete()
            ->where('owner', $user)
            ->and()->where('id', $ids, 'in')
            ->exec();
    }

    /**
     * Get user's contact count
     *
     * @access  public
     * @param   int     $user   The User ID
     * @return  mixed   Returns an array with the contact information of the user or Jaws_Error
     */
    function GetUserContactsCount($user)
    {
        $objORM = Jaws_ORM::getInstance()
            ->table('users_contacts')
            ->select('count(id):integer')
            ->where('owner', $user);
        return $objORM->fetchOne();
    }

    /**
     * Get the info of an user by the username or ID
     *
     * @access  public
     * @param   mixed   $group      Group ID
     * @param   bool    $account    Account information
     * @param   bool    $personal   Personal information
     * @param   bool    $contacts   Contacts information
     * @param   bool    $password   Returns password
     * @return  mixed   Returns an array with the info of the user and false on error
     */
    function GetGroupUsers($group, $account = true, $personal = false, $contacts = false, $password = false)
    {
        $columns = array('users.id:integer', 'avatar');
        // account information
        if ($account) {
            $columns = array_merge($columns, array('username', 'nickname', 'email', 'superadmin:boolean',
                'concurrents', 'logon_hours', 'expiry_date', 'registered_date', 'status:integer',
                'last_update', 'bad_password_count', 'last_access',)
            );
        }

        if ($password) {
            $columns = array_merge($columns, array('password'));
        }

        if ($personal) {
            $columns = array_merge($columns, array('fname', 'lname', 'gender', 'ssn', 'dob',
                'public:boolean', 'privacy:boolean', 'signature', 'about', 'experiences', 'occupations',
                'interests',)
            );
        }

        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select($columns);
        $usersTable->join('users_groups', 'users_groups.user_id', 'users.id');
        $usersTable->where('group_id', (int)$group);
        return $usersTable->fetchAll();
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
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select('id:integer', 'username', 'nickname', 'email', 'superadmin:boolean', 'status:integer');
        $usersTable->where('lower(email)', strtolower($email));
        return $usersTable->fetchAll();
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
        $usersTable->select('id:integer', 'username', 'nickname', 'email', 'new_email', 'status:integer');
        $usersTable->where('email_verify_key', trim($key));
        return $usersTable->fetchRow();
    }

    /**
     * Get the info of an user(s) by the password verification key
     *
     * @access  public
     * @param   string  $key  Verification key
     * @return  mixed   Returns an array with the info of the user(s) and false on error
     */
    function GetUserByPasswordVerifyKey($key)
    {
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select('id:integer', 'username', 'nickname', 'email', 'status:integer');
        $usersTable->where('password_verify_key', trim($key));
        return $usersTable->fetchRow();
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
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select('count(id)');
        $usersTable->where('email', Jaws_UTF8::strtolower($email));
        $usersTable->and()->where('id', $exclude, '<>');
        $howmany = $usersTable->fetchOne();
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
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select('count(id)');
        $usersTable->where('mobile', $mobile);
        $usersTable->and()->where('id', $exclude, '<>');
        $howmany = $usersTable->fetchOne();
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
     * @param   int     $owner  The owner of group
     * @return  mixed   Returns an array with the info of the group and false on error
     */
    function GetGroup($group, $owner = 0)
    {
        $groupsTable = Jaws_ORM::getInstance()->table('groups');
        $groupsTable->select('id:integer', 'name', 'title', 'description', 'enabled:boolean');
        $groupsTable->where('owner', (int)$owner);
        if (is_int($group)) {
            $groupsTable->and()->where('id', $group);
        } else {
            $groupsTable->and()->where('lower(name)', Jaws_UTF8::strtolower($group));
        }

        return $groupsTable->fetchRow();
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
     * @param   int     $limit
     * @param   int     $offset
     * @return  array   Returns an array of the available users and false on error
     */
    function GetUsers($group = false, $superadmin = null, $status = null, $term = '', $orderBy = 'id asc',
        $limit = 0, $offset = null)
    {
        $fields = array(
            'id', 'id asc', 'id desc',
            'username', 'username asc', 'username desc',
            'nickname', 'nickname asc', 'nickname desc',
            'email'
        );
        if (!in_array($orderBy, $fields)) {
            $orderBy = 'id asc';
        }

        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select(
            'users.id:integer', 'username', 'email', 'nickname', 'fname', 'lname',
            'superadmin:boolean', 'users.status:integer'
        );
        if ($group !== false) {
            $usersTable->join('users_groups', 'users_groups.user_id', 'users.id');
            $usersTable->where('group_id', (int)$group);
        }

        if (!is_null($superadmin)) {
            $usersTable->and()->where('superadmin', (bool)$superadmin);
        }

        if (!is_null($status)) {
            $usersTable->and()->where('status', (int)$status);
        }

        if (!empty($term)) {
            $term = Jaws_UTF8::strtolower($term);
            $usersTable->and()->openWhere('lower(username)', $term, 'like');
            $usersTable->or()->where('lower(nickname)',      $term, 'like');
            $usersTable->or()->closeWhere('lower(email)',    $term, 'like');
        }

        $usersTable->orderBy('users.'.$orderBy);
        $usersTable->limit($limit, $offset);
        return $usersTable->fetchAll();
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
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select('count(users.id):integer');
        if ($group !== false) {
            $usersTable->join('users_groups', 'users_groups.user_id', 'users.id');
            $usersTable->where('group_id', (int)$group);
        }

        if (!is_null($superadmin)) {
            $usersTable->and()->where('superadmin', (bool)$superadmin);
        }

        if (!is_null($status)) {
            $usersTable->and()->where('status', (int)$status);
        }

        if (!empty($term)) {
            $term = Jaws_UTF8::strtolower($term);
            $usersTable->and()->openWhere('lower(username)', $term, 'like');
            $usersTable->or()->where('lower(nickname)',      $term, 'like');
            $usersTable->or()->closeWhere('lower(email)',    $term, 'like');
        }

        $result = $usersTable->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return 0;
        }

        return (int)$result;
    }

    /**
     * Get a list of all groups
     *
     * @access  public
     * @param   int     $owner      The owner of group
     * @param   bool    $enabled    enabled groups?(null for both)
     * @param   string  $orderBy    field to order by
     * @param   int     $limit
     * @param   int     $offset
     * @return  array   Returns an array of the available groups and false on error
     */
    function GetGroups($owner = 0, $enabled = null, $orderBy = 'name', $limit = 0, $offset = null)
    {
        $fields  = array('id', 'name', 'title');
        if (!in_array($orderBy, $fields)) {
            $GLOBALS['log']->Log(JAWS_LOG_WARNING, _t('GLOBAL_ERROR_UNKNOWN_COLUMN'));
            $orderBy = 'name';
        }

        $groupsTable = Jaws_ORM::getInstance()->table('groups');
        $groupsTable->select('id:integer', 'name', 'title', 'description', 'enabled:boolean');
        $groupsTable->where('owner', (int)$owner);
        if (!is_null($enabled)) {
            $groupsTable->and()->where('enabled', (bool)$enabled);
        }
        $groupsTable->limit($limit, $offset)->orderBy($orderBy);
        return $groupsTable->fetchAll();
    }

    /**
     * Get count of groups
     *
     * @access  public
     * @param   int     $owner      The owner of group
     * @param   bool    $enabled    enabled groups?(null for both)
     * @return  int     Returns groups count
     */
    function GetGroupsCount($owner = 0, $enabled = null)
    {
        $groupsTable = Jaws_ORM::getInstance()->table('groups');
        $groupsTable->select('count(id):integer');
        $groupsTable->where('owner', (int)$owner);
        if (!is_null($enabled)) {
            $groupsTable->and()->where('enabled', (bool)$enabled);
        }
        $result = $groupsTable->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return 0;
        }

        return (int)$result;
    }

    /**
     * Get a list of groups where a user is
     *
     * @access  public
     * @param   mixed   $user   Username or UserID
     * @param   int     $owner  Owner ID
     * @return  array   Returns an array of the available groups and false on error
     */
    function GetGroupsOfUser($user, $owner = 0)
    {
        $ugroupsTable = Jaws_ORM::getInstance()->table('users_groups');
        $ugroupsTable->select('groups.id:integer', 'groups.name');
        $ugroupsTable->join('users',  'users.id',  'users_groups.user_id');
        $ugroupsTable->join('groups', 'groups.id', 'users_groups.group_id');
        $ugroupsTable->where('groups.owner', (int)$owner);
        if (is_int($user)) {
            $ugroupsTable->and()->where('users.id', $user);
        } else {
            $ugroupsTable->and()->where('users.username', $user);
        }

        $result = $ugroupsTable->fetchAll();
        if (!Jaws_Error::IsError($result)) {
            $result = array_column($result, 'name', 'id');
        }

        return $result;
    }

    /**
     * Adds a new user
     *
     * @access  public
     * @param   array   $uData  User information data
     * @return  mixed   Returns user's id if user was successfully added, otherwise Jaws_Error
     */
    function AddUser($uData)
    {
        // username
        $uData['username'] = trim($uData['username'], '-_.@');
        if (!preg_match('/^[[:alnum:]-_.@]{3,32}$/', $uData['username'])) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INVALID_USERNAME'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        $uData['username'] = strtolower($uData['username']);

        // check reserved users
        $reservedUsers = preg_split("/\n|\r|\n\r/", $GLOBALS['app']->Registry->fetch('reserved_users', 'Users'));
        if (in_array($uData['username'], $reservedUsers)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_RESERVED_USERNAME', substr(strrchr($uData['username'], '@'), 1)),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // nickname
        $uData['nickname'] = Jaws_UTF8::trim($uData['nickname']);
        if (empty($uData['nickname'])) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // email
        $uData['email'] = trim($uData['email']);
        if (!preg_match("/^[[:alnum:]-_.]+\@[[:alnum:]-_.]+\.[[:alnum:]-_]+$/", $uData['email'])) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INVALID_EMAIL_ADDRESS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        $uData['email'] = strtolower($uData['email']);
        $blockedDomains = $GLOBALS['app']->Registry->fetch('blocked_domains', 'Policy');
        if (false !== strpos($blockedDomains, "\n".substr(strrchr($uData['email'], '@'), 1))) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INVALID_EMAIL_DOMAIN', substr(strrchr($uData['email'], '@'), 1)),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // mobile
        if (array_key_exists('mobile', $uData)) {
            $uData['mobile'] = trim($uData['mobile']);
            if (!empty($uData['mobile'])) {
                if (!preg_match("/^[00]\d{10,16}$/", $uData['mobile'])) {
                    return Jaws_Error::raiseError(
                        _t('GLOBAL_ERROR_INVALID_MOBILE_NUMBER'),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE
                    );
                }
            }
        }

        // password & complexity
        $min = (int)$GLOBALS['app']->Registry->fetch('password_min_length', 'Policy');
        $min = ($min == 0)? 1 : $min;
        if ($uData['password'] == '' ||
            !preg_match("/^[[:print:]]{{$min},24}$/", $uData['password'])
        ) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INVALID_PASSWORD', $min),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        if ($GLOBALS['app']->Registry->fetch('password_complexity', 'Policy') == 'yes') {
            if (!preg_match('/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])(?=.*[[:punct:]])/',
                    $uData['password'])
            ) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_INVALID_COMPLEXITY'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        $uData['last_update'] = time();
        $uData['registered_date'] = time();
        $uData['superadmin'] = isset($uData['superadmin'])? (bool)$uData['superadmin'] : false;
        $uData['status'] = isset($uData['status'])? (int)$uData['status'] : 1;
        $uData['concurrents'] = isset($uData['concurrents'])? (int)$uData['concurrents'] : 0;
        $uData['password'] = Jaws_User::GetHashedPassword($uData['password']);
        $uData['logon_hours'] = empty($uData['logon_hours'])? str_pad('', 42, 'F') : $uData['logon_hours'];
        if (isset($uData['expiry_date'])) {
            if (empty($uData['expiry_date'])) {
                $uData['expiry_date'] = 0;
            } else {
                $objDate = Jaws_Date::getInstance();
                $uData['expiry_date'] = $GLOBALS['app']->UserTime2UTC(
                    (int)$objDate->ToBaseDate(preg_split('/[- :]/', $uData['expiry_date']), 'U')
                );
            }
        }

        // set user's domain to default domain if not set
        if (!isset($uData['domain'])) {
            $uData['domain'] = (int)$GLOBALS['app']->Registry->fetch('default_domain', 'Users');
        }

        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->insert($uData)->exec();
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_USERS_ALREADY_EXISTS', $uData['username']));
            }
            return $result;
        }

        // Let everyone know a user has been added
        $res = $GLOBALS['app']->Listener->Shout('Users', 'AddUser', $result);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $result;
    }

    /**
     * Update the info of an user
     *
     * @access  public
     * @param   int     $id     User's ID
     * @param   array   $uData  User information data
     * @return  bool    Returns true if user was successfully updated, false if not
     */
    function UpdateUser($id, $uData)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($uData),
            array('username', 'nickname', 'email', 'new_email', 'password',
                'superadmin', 'status', 'concurrents', 'logon_hours', 'expiry_date',
            )
        );
        foreach ($invalids as $invalid) {
            unset($uData[$invalid]);
        }

        // username
        $uData['username'] = trim($uData['username'], '-_.@');
        if (!preg_match('/^[[:alnum:]-_.@]{3,32}$/', $uData['username'])) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INVALID_USERNAME'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        $uData['username'] = strtolower($uData['username']);

        // nickname
        $uData['nickname'] = Jaws_UTF8::trim($uData['nickname']);
        if (empty($uData['nickname'])) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // email
        $uData['email'] = trim($uData['email']);
        if (!preg_match("/^[[:alnum:]-_.]+\@[[:alnum:]-_.]+\.[[:alnum:]-_]+$/", $uData['email'])) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INVALID_EMAIL_ADDRESS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        $uData['email'] = strtolower($uData['email']);
        $blockedDomains = $GLOBALS['app']->Registry->fetch('blocked_domains', 'Policy');
        if (false !== strpos($blockedDomains, "\n".substr(strrchr($uData['email'], '@'), 1))) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INVALID_EMAIL_DOMAIN', substr(strrchr($uData['email'], '@'), 1)),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // new email
        if (isset($uData['new_email']) && !empty($uData['new_email'])) {
            $uData['new_email'] = trim($uData['new_email']);
            if (!preg_match("/^[[:alnum:]-_.]+\@[[:alnum:]-_.]+\.[[:alnum:]-_]+$/", $uData['new_email'])) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_INVALID_EMAIL_ADDRESS'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
            $uData['new_email'] = strtolower($uData['new_email']);
            if (false !== strpos($blockedDomains, "\n".substr(strrchr($uData['new_email'], '@'), 1))) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_INVALID_EMAIL_DOMAIN', substr(strrchr($uData['new_email'], '@'), 1)),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        // mobile
        if (array_key_exists('mobile', $uData)) {
            $uData['mobile'] = trim($uData['mobile']);
            if (!empty($uData['mobile'])) {
                if (!preg_match("/^[00]\d{10,16}$/", $uData['mobile'])) {
                    return Jaws_Error::raiseError(
                        _t('GLOBAL_ERROR_INVALID_MOBILE_NUMBER'),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE
                    );
                }
            }
        }

        // password & complexity
        if (isset($uData['password']) && $uData['password'] !== '') {
            $min = (int)$GLOBALS['app']->Registry->fetch('password_min_length', 'Policy');
            if (!preg_match("/^[[:print:]]{{$min},24}$/", $uData['password'])) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_INVALID_PASSWORD', $min),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }

            if ($GLOBALS['app']->Registry->fetch('password_complexity', 'Policy') == 'yes') {
                if (!preg_match('/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])(?=.*[[:punct:]])/',
                        $uData['password'])
                ) {
                    return Jaws_Error::raiseError(
                        _t('GLOBAL_ERROR_INVALID_COMPLEXITY'),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE
                    );
                }
            }

            // password hash
            $uData['password'] = Jaws_User::GetHashedPassword($uData['password']);
            $uData['password_verify_key'] = '';
            $uData['last_password_update'] = time();
        } else {
            unset($uData['password']);
        }

        // get user information, we need it for rename avatar
        $user = Jaws_User::GetUser((int)$id, true, true);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        if (JAWS_GODUSER == $user['id']) {
            if (!isset($GLOBALS['app']->Session) || $GLOBALS['app']->Session->GetAttribute('user') != $user['id']) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_ACCESS_DENIED'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        if (isset($GLOBALS['app']->Session) && !$GLOBALS['app']->Session->IsSuperAdmin()) {
            unset($uData['superadmin']);
            // non-superadmin user can't change properties of superadmin users
            if ($user['superadmin']) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_ACCESS_DENIED'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        if (($uData['username'] !== $user['username'])) {
            // check reserved users
            $reservedUsers = preg_split("/\n|\r|\n\r/", $GLOBALS['app']->Registry->fetch('reserved_users', 'Users'));
            if (in_array($uData['username'], $reservedUsers)) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_RESERVED_USERNAME', substr(strrchr($uData['username'], '@'), 1)),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }

            // set new avatar name if username changed
            if (!empty($user['avatar'])) {
                $fileinfo = pathinfo($user['avatar']);
                if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                    $uData['avatar'] = $uData['username']. '.'. $fileinfo['extension'];
                }
            }
        }

        $uData['last_update'] = time();
        if (isset($uData['status'])) {
            $uData['status'] = (int)$uData['status'];
            if ($uData['status'] == 1) {
                $uData['email_verify_key'] = '';
            }
        }
        if (isset($uData['expiry_date'])) {
            if (empty($uData['expiry_date'])) {
                $uData['expiry_date'] = 0;
            } else {
                $objDate = Jaws_Date::getInstance();
                $uData['expiry_date'] = $GLOBALS['app']->UserTime2UTC(
                    (int)$objDate->ToBaseDate(preg_split('/[- :]/', $uData['expiry_date']), 'U')
                );
            }
        }

        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->update($uData)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_USERS_ALREADY_EXISTS', $uData['username']));
            }
            return $result;
        }

        // rename avatar name
        if (isset($uData['avatar'])) {
            Jaws_Utils::Delete(AVATAR_PATH. $uData['avatar']);
            @rename(AVATAR_PATH. $user['avatar'],
                    AVATAR_PATH. $uData['avatar']);
        }

        if (isset($GLOBALS['app']->Session) && $GLOBALS['app']->Session->GetAttribute('user') == $id) {
            $GLOBALS['app']->Session->SetAttribute('username', $uData['username']);
            $GLOBALS['app']->Session->SetAttribute('nickname', $uData['nickname']);
            $GLOBALS['app']->Session->SetAttribute('email',    $uData['email']);
            // update user's avatar in current session
            if (isset($uData['avatar'])) {
                $GLOBALS['app']->Session->SetAttribute(
                    'avatar',
                    $this->GetAvatar($uData['avatar'], $uData['email'], 48, $uData['last_update'])
                );
            }
            // update last password update time in current session
            if (isset($uData['last_password_update'])) {
                $GLOBALS['app']->Session->SetAttribute('last_password_update', $uData['last_password_update']);
            }
        }

        // Let everyone know a user has been updated
        $res = $GLOBALS['app']->Listener->Shout('Users', 'UpdateUser', $id);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return true;
    }

    /**
     * Update personal information of a user such as fname, lname, gender, etc..
     *
     * @access  public
     * @param   int     $id     User's ID
     * @param   array   $pData  Personal information data
     * @return  bool    Returns true on success, false on failure
     */
    function UpdatePersonal($id, $pData)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($pData),
            array('fname', 'lname', 'gender', 'ssn', 'dob',
                'signature', 'about', 'experiences',
                'occupations', 'interests', 'avatar', 'privacy',
            )
        );
        foreach ($invalids as $invalid) {
            unset($pData[$invalid]);
        }

        // get user information
        $user = Jaws_User::GetUser((int)$id, true, true);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        if (JAWS_GODUSER == $user['id']) {
            if (!isset($GLOBALS['app']->Session) || $GLOBALS['app']->Session->GetAttribute('user') != $user['id']) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_ACCESS_DENIED'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        if (array_key_exists('avatar', $pData)) {
            if (!empty($user['avatar'])) {
                Jaws_Utils::Delete(AVATAR_PATH. $user['avatar']);
            }

            if (!empty($pData['avatar'])) {
                $fileinfo = pathinfo($pData['avatar']);
                if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                    if (!in_array($fileinfo['extension'], array('gif','jpg','jpeg','png','svg'))) {
                        return false;
                    } else {
                        $new_avatar = $user['username']. '.'. $fileinfo['extension'];
                        @rename(Jaws_Utils::upload_tmp_dir(). '/'. $pData['avatar'],
                                AVATAR_PATH. $new_avatar);
                        $pData['avatar'] = $new_avatar;
                    }
                }
            }
        }

        $pData['last_update'] = time();
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->update($pData)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (isset($GLOBALS['app']->Session) && $GLOBALS['app']->Session->GetAttribute('user') == $id) {
            foreach($pData as $k => $v) {
                if ($k == 'avatar') {
                    $GLOBALS['app']->Session->SetAttribute(
                        $k,
                        $this->GetAvatar($v, $user['email'], 48, $pData['last_update'])
                    );
                } else {
                    $GLOBALS['app']->Session->SetAttribute($k, $v);
                }
            }
        }

        // Let everyone know a user has been updated
        $res = $GLOBALS['app']->Listener->Shout('Users', 'UpdateUser', $id);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return true;
    }

    /**
     * Update default contact information of current user such as address, tel, mobile, fax, etc..
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   array   $data   Contacts information data
     * @return  bool    Returns true on success, false on failure
     */
    function UpdateContact($uid, $data)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($data),
            array('title', 'name', 'image', 'note', 'tel', 'mobile', 'fax', 'url', 'email', 'address')
        );
        foreach ($invalids as $invalid) {
            unset($data[$invalid]);
        }

        $user = $this->GetUser($uid);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        if (JAWS_GODUSER == $user['id']) {
            if (!isset($GLOBALS['app']->Session) || $GLOBALS['app']->Session->GetAttribute('user') != $user['id']) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_ACCESS_DENIED'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        // begin transaction
        $objORM = Jaws_ORM::getInstance()->beginTransaction();
        $objORM->table('users_contacts');
        $data['owner'] = $user['id'];
        $contactId = $objORM->upsert($data)
            ->where('owner', $uid)
            ->and()
            ->where('id', $user['contact'])
            ->exec();
        if (Jaws_Error::IsError($contactId)) {
            return $contactId;
        }

        // set user's contact id
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $res = $usersTable->update(array('contact' => $contactId))->where('id', (int)$uid)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // commit transaction
        $objORM->commit();
        return $contactId;
    }

    /**
     * Update contacts information of a user such as address, tel, mobile, fax, etc..
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $cid    Contact ID
     * @param   array   $data   Contacts information data
     * @return  bool    Returns true on success, false on failure
     */
    function UpdateContacts($uid, $cid, $data)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($data),
            array('title', 'name', 'image', 'note', 'tel', 'mobile', 'fax', 'url', 'email', 'address')
        );
        foreach ($invalids as $invalid) {
            unset($data[$invalid]);
        }

        $data['owner'] = $uid;
        return Jaws_ORM::getInstance()
            ->table('users_contacts')
            ->upsert($data)
            ->where('id', $cid)
            ->and()
            ->where('owner', $uid)
            ->exec();
    }

    /**
     * Adds a new group
     *
     * @access  public
     * @param   array   $gData  Group information data
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if group  was successfully added, false if not
     */
    function AddGroup($gData, $owner = 0)
    {
        // name
        $gData['name'] = trim($gData['name'], '-_.@');
        if (!preg_match('/^[[:alnum:]-_.@]{3,32}$/', $gData['name'])) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INVALID_GROUPNAME'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        $gData['name']  = strtolower($gData['name']);
        $gData['owner'] = (int)$owner;

        // title
        $gData['title'] = Jaws_UTF8::trim($gData['title']);
        if (empty($gData['title'])) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        $gData['removable'] = isset($gData['removable'])? (bool)$gData['removable'] : true;
        $gData['enabled'] = isset($gData['enabled'])? (bool)$gData['enabled'] : true;
        $groupsTable = Jaws_ORM::getInstance()->table('groups');
        $result = $groupsTable->insert($gData)->exec();
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_GROUPS_ALREADY_EXISTS', $gData['name']));
            }
            return $result;
        }

        // Let everyone know a group has been added
        $res = $GLOBALS['app']->Listener->Shout('Users', 'AddGroup', $result);
        if (Jaws_Error::IsError($res)) {
            //do nothing
        }

        return $result;
    }

    /**
     * Update the info of a group
     *
     * @access  public
     * @param   int     $id     Group ID
     * @param   array   $gData  Group information data
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if group was sucessfully updated, false if not
     */
    function UpdateGroup($id, $gData, $owner = 0)
    {
        // unset invalid keys
        $invalids = array_diff(array_keys($gData), array('name', 'title', 'description', 'enabled'));
        foreach ($invalids as $invalid) {
            unset($gData[$invalid]);
        }

        // name
        if (isset($gData['name'])) {
            $gData['name'] = trim($gData['name'], '-_.@');
            if (!preg_match('/^[[:alnum:]-_.@]{3,32}$/', $gData['name'])) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_INVALID_GROUPNAME'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
            $gData['name']  = strtolower($gData['name']);
        }
        $gData['owner'] = (int)$owner;

        // title
        if (isset($gData['title'])) {
            $gData['title'] = Jaws_UTF8::trim($gData['title']);
            if (empty($gData['title'])) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        if (isset($gData['enabled'])) {
            $gData['enabled'] = (bool)$gData['enabled'];
        }

        $groupsTable = Jaws_ORM::getInstance()->table('groups');
        $result = $groupsTable->update($gData)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_GROUPS_ALREADY_EXISTS', $gData['name']));
            }
            return $result;
        }

        // Let everyone know a group has been updated
        $res = $GLOBALS['app']->Listener->Shout('Users', 'UpdateGroup', $id);
        if (Jaws_Error::IsError($res)) {
            //do nothing
        }

        return true;
    }

    /**
     * Deletes an user
     *
     * @access  public
     * @param   int     $id     User's ID
     * @return  bool    Returns true if user was successfully deleted, false if not
     */
    function DeleteUser($id)
    {
        $objORM = Jaws_ORM::getInstance();
        $user = $this->GetUser((int)$id, true, false, true, false);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        if (JAWS_GODUSER == $user['id']) {
            return false;
        }

        // users can't delete himself
        if (isset($GLOBALS['app']->Session) && $GLOBALS['app']->Session->GetAttribute('user') == $user['id']) {
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

        $result = $objORM->delete()->table('users_groups')->where('user_id', $user['id'])->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        // Registry
        if (!$GLOBALS['app']->Registry->deleteByUser($user['id'])) {
            return false;
        }
        // ACL
        if (!$GLOBALS['app']->ACL->deleteByUser($user['id'])) {
            return false;
        }
        // Session
        if (!$GLOBALS['app']->Session->DeleteUserSessions($user['id'])) {
            return false;
        }

        //Commit Transaction
        $objORM->commit();

        // Let everyone know that a user has been deleted
        $res = $GLOBALS['app']->Listener->Shout('Users', 'DeleteUser', $user);
        if (Jaws_Error::IsError($res)) {
            // nothing
        }

        return true;
    }

    /**
     * Deletes a group
     *
     * @access  public
     * @param   int     $id     Group's ID
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if group was successfully deleted, false if not
     */
    function DeleteGroup($id, $owner = 0)
    {
        $objORM = Jaws_ORM::getInstance();

        //Start Transaction
        $objORM->beginTransaction();

        $objORM->delete()->table('groups');
        $result = $objORM->where('id', $id)
            ->and()
            ->where('removable', true)
            ->and()
            ->where('owner', (int)$owner)
            ->exec();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return false;
        }

        $result = $objORM->delete()->table('users_groups')->where('group_id', $id);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $GLOBALS['app']->ACL->deleteByGroup($id);

        //Commit Transaction
        $objORM->commit();

        // Let everyone know a group has been deleted
        $res = $GLOBALS['app']->Listener->Shout('Users', 'DeleteGroup', $id);
        if (Jaws_Error::IsError($res)) {
            // nothing
        }

        return true;
    }

    /**
     * Adds an user to a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if user was sucessfully added to the group, false if not
     */
    function AddUserToGroup($user, $group, $owner = 0)
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

        $objORM->table('users_groups');
        return $objORM->insert(array('user_id' => $user, 'group_id' => $group))->exec();
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

        $objORM->table('users_groups')->delete();
        return $objORM->where('user_id', $user)->and()->where('group_id', $group)->exec();
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
        $usrgrpTable->select('count(user_id):integer');
        $usrgrpTable->where('user_id', $user)->and()->where('group_id', $group);
        $howmany = $usrgrpTable->fetchOne();
        if (Jaws_Error::IsError($howmany)) {
            return false;
        }

        return (bool)$howmany;
    }

    /**
     * Update the email verification key of a certain user
     *
     * @access  public
     * @param   int     $uid  User's ID
     * @return  mixed   Generated key if success or Jaws_Error on failure
     */
    function UpdateEmailVerifyKey($uid)
    {
        $key = md5(uniqid(rand(), true)) . time() . floor(microtime()*1000);
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->update(array('email_verify_key' => $key))->where('id', (int)$uid)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $key;
    }

    /**
     * Update the change password verification key of a certain user
     *
     * @access  public
     * @param   int     $uid  User's ID
     * @return  mixed   Generated key if success or Jaws_Error on failure
     */
    function UpdatePasswordVerifyKey($uid)
    {
        $key = md5(uniqid(rand(), true)) . time() . floor(microtime()*1000);
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->update(array('password_verify_key' => $key))->where('id', (int)$uid)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $key;
    }

}