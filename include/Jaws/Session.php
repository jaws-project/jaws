<?php
/**
 * Responses warning
 */
define('RESPONSE_WARNING', 'RESPONSE_WARNING');
/**
 * Responses error
 */
define('RESPONSE_ERROR',   'RESPONSE_ERROR');
/**
 * Responses notice
 */
define('RESPONSE_NOTICE',  'RESPONSE_NOTICE');
/**
 *
 */
define('SESSION_RESERVED_ATTRIBUTES', "sid,salt,type,user,user_name,superadmin,concurrents,acl,updatetime");

/**
 * Class to manage User session.
 *
 * @category   Session
 * @package    Core
 * @author     Ivan -sk8- Chavero <imcsk8@gluch.org.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Session
{
    /**
     * Authentication model
     * @var     object $_AuthModel
     * @access  private
     */
    var $_AuthModel;

    /**
     * Authentication method
     * @var     string $_AuthMethod
     * @access  private
     */
    var $_AuthMethod;

    /**
     * Attributes array
     * @var     array $_Attributes
     * @access  private
     * @see     SetAttribute(), GetAttibute()
     */
    var $_Attributes = array();

    /**
     * Attributes array trash
     * @var     array $_AttributesTrash
     * @access  private
     * @see     SetAttribute(), GetAttibute()
     */
    var $_AttributesTrash = array();

    /**
     * Changes flag
     * @var     bool    $_HasChanged
     * @access  private
     */
    var $_HasChanged;

    /**
     * Session unique identifier
     * @var     string $_SessionID
     * @access  private
     */
    var $_SessionID;

    /**
     * Is session exists in browser or application
     * @var     bool    $_SessionExists
     * @access  private
     */
    var $_SessionExists = true;

    /**
     * An interface for available drivers
     *
     * @access  public
     * @return  object  Jaws_Session type object
     */
    function &factory()
    {
        $SessionType = ucfirst(strtolower(APP_TYPE));
        $SessionType = preg_replace('/[^[:alnum:]_-]/', '', $SessionType);
        $sessionFile = JAWS_PATH . 'include/Jaws/Session/'. $SessionType .'.php';
        if (!file_exists($sessionFile)) {
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loading session $SessionType failed.");
            return new Jaws_Error("Loading session $SessionType failed.",
                                  __FUNCTION__);
        }

        include_once $sessionFile;
        $className = 'Jaws_Session_' . $SessionType;
        $obj = new $className();
        return $obj;
    }

    /**
     * Initializes the Session
     *
     * @access  public
     * @return  void
     */
    function Init()
    {
        $this->_AuthMethod = $GLOBALS['app']->Registry->Get('auth_method', 'Users', JAWS_COMPONENT_GADGET);
        $this->_AuthMethod = preg_replace('/[^[:alnum:]_-]/', '', $this->_AuthMethod);
        $authFile = JAWS_PATH . 'include/Jaws/Auth/' . $this->_AuthMethod . '.php';
        if (empty($this->_AuthMethod) || !file_exists($authFile)) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR,
                                 $this->_AuthMethod . ' Error: ' . $authFile .
                                 ' file doesn\'t exists, using DefaultAuth');
            $this->_AuthMethod = 'Default';
        }

        // Try to restore session...
        $this->_HasChanged = false;

        // Delete expired sessions
        if (mt_rand(1, 32) == mt_rand(1, 32)) {
            $this->DeleteExpiredSessions();
        }
    }

    /**
     * Login
     *
     * @param   string  $username   Username
     * @param   string  $password   Password
     * @param   bool    $remember   Remember me
     * @param   string  $authmethod Authentication method
     * @return  mixed   An Array of user's attributes if success, otherwise Jaws_Error
     */
    function Login($username, $password, $remember, $authmethod = '')
    {
        $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'LOGGIN IN');
        if (!$this->_SessionExists) {
            return Jaws_Error::raiseError(_t('GLOBAL_ERROR_SESSION_NOTFOUND'),
                                          __FUNCTION__,
                                          JAWS_ERROR_NOTICE);
        }

        if ($username === '' && $password === '') {
            $result = Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_WRONG'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        } else {
            if (!empty($authmethod)) {
                $authmethod = preg_replace('/[^[:alnum:]_-]/', '', $authmethod);
            } else {
                $authmethod = $this->_AuthMethod;
            }

            require_once JAWS_PATH . 'include/Jaws/Auth/' . $authmethod . '.php';
            $className = 'Jaws_Auth_' . $authmethod;
            $this->_AuthModel = new $className();
            $result = $this->_AuthModel->Auth($username, $password);
            if (!Jaws_Error::isError($result)) {
                $result = $this->_AuthModel->GetAttributes();
                if (!Jaws_Error::isError($result)) {
                    $existSessions = 0;
                    if (!empty($result['concurrents'])) {
                        $existSessions = $this->GetUserSessions($result['id'], true);
                    }

                    if (empty($existSessions) || $result['concurrents'] > $existSessions)
                    {
                        // remove login trying count from session
                        $this->DeleteAttribute('bad_login_count');
                        // create session & cookie
                        $this->Create($result, $remember);
                        // Let everyone know a user has been logged
                        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
                        $GLOBALS['app']->Shouter->Shout('onLoginUser');
                        return true;
                    } else {
                        $result = Jaws_Error::raiseError(
                            _t('GLOBAL_ERROR_LOGIN_CONCURRENT_REACHED'),
                            __FUNCTION__,
                            JAWS_ERROR_NOTICE
                        );
                    }
                }
            }
        }

        // increment login trying count in session
        $this->SetAttribute('bad_login_count', (int)$this->GetAttribute('bad_login_count') + 1);
        return $result;
    }

    /**
     * Return session login status
     *
     * @access  public
     * @return  bool    login status
     */
    function Logged()
    {
        return $this->GetAttribute('logged');
    }

    /**
     * Logout from session and reset session values
     *
     * @access  public
     * @return  void
     */
    function Logout()
    {
        $this->Reset();
        $this->Synchronize($this->_SessionID);
        $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Session logout');
    }

    /**
     * Loads Jaws Session
     *
     * @access  protected
     * @param   string  $sid Session identifier
     * @return  bool    True if can load session, false if not
     */
    function Load($sid)
    {
        $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Loading session');
        $this->_SessionID = '';
        @list($sid, $salt) = explode('-', $sid);
        $session = $this->GetSession((int)$sid);
        if (is_array($session)) {
            $checksum = md5($session['user'] . $session['data']);
            $expTime = time() - 60 * (int) $GLOBALS['app']->Registry->Get('session_idle_timeout', 'Policy', JAWS_COMPONENT_GADGET);

            $this->_SessionID  = $session['sid'];
            $this->_Attributes = unserialize($session['data']);

            // check session longevity
            if ($session['updatetime'] < ($expTime - $session['longevity'])) {
                $GLOBALS['app']->Session->Logout();
                $GLOBALS['log']->Log(JAWS_LOG_NOTICE, 'Previous session has expired');
                return false;
            }

            // user expiry date
            $expiry_date = $this->GetAttribute('expiry_date');
            if (!empty($expiry_date) && $expiry_date <= time()) {
                $GLOBALS['app']->Session->Logout();
                $GLOBALS['log']->Log(JAWS_LOG_NOTICE, 'This username is expired');
                return false;
            }

            // logon hours
            $logon_hours = $this->GetAttribute('logon_hours');
            if (!empty($logon_hours)) {
                $wdhour = explode(',', $GLOBALS['app']->UTC2UserTime(time(), 'w,G', true));
                $lhByte = hexdec($logon_hours{$wdhour[0]*6 + intval($wdhour[1]/4)});
                if ((pow(2, fmod($wdhour[1], 4)) & $lhByte) == 0) {
                    $GLOBALS['app']->Session->Logout();
                    $GLOBALS['log']->Log(JAWS_LOG_NOTICE, 'Logon hours terminated');
                    return false;
                }
            }

            // concurrent logins
            if ($session['updatetime'] < $expTime) {
                $logins = $this->GetAttribute('concurrents');
                $existSessions = $this->GetUserSessions($this->GetAttribute('user'), true);
                if (!empty($existSessions) && !empty($logins) && $existSessions >= $logins) {
                    $GLOBALS['app']->Session->Logout();
                    $GLOBALS['log']->Log(JAWS_LOG_NOTICE, 'Maximum number of concurrent logins reached');
                    return false;
                }
            }

            // browser agent
            $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            $agent = $xss->filter($_SERVER['HTTP_USER_AGENT']);
            if ($agent !== $session['agent']) {
                $GLOBALS['app']->Session->Logout();
                $GLOBALS['log']->Log(JAWS_LOG_NOTICE, 'Previous session agent has been changed');
                return false;
            }

            // salt & checksum
            if (($salt !== $this->GetAttribute('salt')) || ($checksum !== $session['checksum'])) {
                $GLOBALS['app']->Session->Logout();
                $GLOBALS['log']->Log(JAWS_LOG_NOTICE, 'Previous session salt has been changed');
                return false;
            }

            // check referrer of request
            $referrer = @parse_url($_SERVER['HTTP_REFERER']);
            if ($referrer && isset($referrer['host'])) {
                $referrer = $referrer['host'];
            } else {
                $referrer = $_SERVER['HTTP_HOST'];
            }

            if (!$this->GetAttribute('logged') ||
                (JAWS_SCRIPT != 'admin') ||
                $referrer == $_SERVER['HTTP_HOST'] ||
                $session['referrer'] === md5($referrer))
            {
                $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Session was OK');
                return true;
            } else {
                $GLOBALS['app']->Session->Logout();
                $GLOBALS['log']->Log(JAWS_LOG_NOTICE, 'Session found but referrer changed');
                return false;
            }
        }

        $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'No previous session exists');
        return false;
    }

    /**
     * Create a new session for a given data
     *
     * @access  protected
     * @param   array   $info       User's attributes
     * @param   bool    $remember   Remember me
     * @return  bool    True if can create session
     */
    function Create($info = array(), $remember = false)
    {
        if (empty($info)) {
            $info['id']          = '';
            $info['internal']    = false;
            $info['username']    = '';
            $info['superadmin']  = false;
            $info['groups']      = array();
            $info['nickname']    = '';
            $info['logon_hours'] = '';
            $info['expiry_date'] = 0;
            $info['concurrents'] = 0;
            $info['email']      = '';
            $info['url']        = '';
            $info['avatar']     = '';
            $info['language']   = '';
            $info['theme']      = '';
            $info['editor']     = '';
            $info['timezone']   = null;
        }

        $this->_Attributes = array();
        $this->SetAttribute('user',        $info['id']);
        $this->SetAttribute('internal',    $info['internal']);
        $this->SetAttribute('salt',        uniqid(mt_rand(), true));
        $this->SetAttribute('type',        APP_TYPE);
        $this->SetAttribute('username',    $info['username']);
        $this->SetAttribute('superadmin',  $info['superadmin']);
        $this->SetAttribute('groups',      $info['groups']);
        $this->SetAttribute('logon_hours', $info['logon_hours']);
        $this->SetAttribute('expiry_date', $info['expiry_date']);
        $this->SetAttribute('concurrents', $info['concurrents']);
        $this->SetAttribute('longevity',  $remember?
                                          (int)$GLOBALS['app']->Registry->Get('session_remember_timeout', 'Policy', JAWS_COMPONENT_GADGET)*3600 : 0);
        $this->SetAttribute('logged',     !empty($info['id']));
        //profile
        $this->SetAttribute('nickname',   $info['nickname']);
        $this->SetAttribute('email',      $info['email']);
        $this->SetAttribute('url',        $info['url']);
        $this->SetAttribute('avatar',     $info['avatar']);
        //preferences
        $this->SetAttribute('language',   $info['language']);
        $this->SetAttribute('theme',      $info['theme']);
        $this->SetAttribute('editor',     $info['editor']);
        $this->SetAttribute('timezone',  (trim($info['timezone']) == "") ? null : $info['timezone']);

        $this->_SessionID = $this->Synchronize($this->_SessionID);
        return true;
    }

    /**
     * Reset current session
     *
     * @access  protected
     * @return  bool    True if can reset it
     */
    function Reset()
    {
        $this->_Attribute = array();
        $this->SetAttribute('user',        '');
        $this->SetAttribute('salt',        uniqid(mt_rand(), true));
        $this->SetAttribute('type',        APP_TYPE);
        $this->SetAttribute('internal',    false);
        $this->SetAttribute('username',    '');
        $this->SetAttribute('superadmin',  false);
        $this->SetAttribute('groups',      array());
        $this->SetAttribute('logon_hours', '');
        $this->SetAttribute('expiry_date', 0);
        $this->SetAttribute('concurrents', 0);
        $this->SetAttribute('longevity',  0);
        $this->SetAttribute('logged',     false);
        $this->SetAttribute('nickname',   '');
        $this->SetAttribute('email',      '');
        $this->SetAttribute('url',        '');
        $this->SetAttribute('avatar',     '');
        $this->SetAttribute('language',   '');
        $this->SetAttribute('theme',      '');
        $this->SetAttribute('editor',     '');
        $this->SetAttribute('timezone',   null);
        return true;
    }

    /**
     * Set a session attribute
     *
     * @access  public
     * @param   string  $name       Attribute name
     * @param   mixed   $value      Attribute value
     * @param   bool    $trashed    Trashed attribute(eliminated end of current request)
     * @return  bool    True if can set value
     */
    function SetAttribute($name, $value, $trashed = false)
    {
        if ($trashed) {
            $this->_AttributesTrash[$name] = $value;
        } else {
            $this->_HasChanged = !array_key_exists($name, $this->_Attributes) || ($this->_Attributes[$name] != $value);
            if (is_array($value) && $name == 'LastResponses') {
                $this->_Attributes['LastResponses'][] = $value;
            } else {
                $this->_Attributes[$name] = $value;
            }
        }

        return true;
    }

    /**
     * Get a session attribute
     *
     * @access  public
     * @param   string  $name attribute name
     * @return  mixed   Value of the attribute or Null if not exist
     */
    function GetAttribute($name)
    {
        // Deprecated: in next major version will be removed
        if ($name == 'user_id') {
            $name = 'user';
        }

        if (array_key_exists($name, $this->_Attributes)) {
            return $this->_Attributes[$name];
        } elseif (array_key_exists($name, $this->_AttributesTrash)) {
            return $this->_AttributesTrash[$name];
        }

        return null;
    }

    /**
     * Get value of given session's attributes
     *
     * @access  public
     * @param   mixed   $argv Optional variable list of attributes name
     * @return  array   Value of the attributes
     */
    function GetAttributes($argv)
    {
        $names = func_get_args();
        // for support array of keys array
        if (isset($names[0][0]) && is_array($names[0][0])) {
            $names = $names[0];
        }

        if (empty($names)) {
            return $this->_Attributes;
        }

        $attributes = array();
        foreach ($names as $name) {
            $attributes[$name] = $this->GetAttribute($name);
        }

        return $attributes;
    }

    /**
     * Delete a session attribute
     *
     * @access  public
     * @param   string  $name       Attribute name
     * @param   bool    $trashed    Move ttribute to trash before delete
     * @return  bool    True if can delete value
     */
    function DeleteAttribute($name, $trashed = false)
    {
        if (array_key_exists($name, $this->_Attributes)) {
            if ($trashed) {
                $this->_AttributesTrash[$name] = $this->_Attributes[$name];
            }
            unset($this->_Attributes[$name]);
            $this->_HasChanged = true;
        } elseif (!$trashed && array_key_exists($name, $this->_AttributesTrash)) {
            unset($this->_AttributesTrash[$name]);
        }

        return true;
    }

    /**
     * Get permission on a given gadget/task
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $task       Task(s) name
     * @param   bool    $together   And/Or tasks permission result, default true
     * @return  bool    True if granted, else False
     */
    function GetPermission($gadget, $task, $together = true)
    {
        $result = $together? true : false;
        $user = $this->GetAttribute('username');
        $groups = $this->GetAttribute('groups');
        $tasks = array_filter(array_map('trim', explode(',', $task)));
        foreach ($tasks as $task) {
            if ($together) {
                $result = $result &&
                          $GLOBALS['app']->ACL->GetFullPermission($user, $groups, $gadget,
                                                                  $task, $this->IsSuperAdmin());
            } else {
                $result = $result ||
                          $GLOBALS['app']->ACL->GetFullPermission($user, $groups, $gadget,
                                                                  $task, $this->IsSuperAdmin());
            }
        }

        return $result;
    }

    /**
     * Check permission on a given gadget/task
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   string  $task           Task(s) name
     * @param   bool    $together       And/Or tasks permission result, default true
     * @param   string  $errorMessage   Error message to return
     * @return  mixed   True if granted, else throws an Exception(Jaws_Error::Fatal)
     */
    function CheckPermission($gadget, $task, $together = true, $errorMessage = '')
    {
        if ($this->GetPermission($gadget, $task, $together)) {
            return true;
        }

        if (empty($errorMessage)) {
            $errorMessage = 'User '.$this->GetAttribute('username').
                ' don\'t have permission to execute '.$gadget.'::'.$task;
        }

        Jaws_Error::Fatal($errorMessage);
    }

    /**
     * Returns is a current user is superadmin
     *
     * @access  public
     * @return  bool    True if user is a superadmin
     */
    function IsSuperAdmin()
    {
        return $this->GetAttribute('logged') && $this->GetAttribute('superadmin');
    }

    /**
     * Synchronize current session
     *
     * @access  public
     * @return  mixed   Session ID if success, otherwise Jaws_Error or false
     */
    function Synchronize()
    {
        // agent
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $agent = $xss->filter($_SERVER['HTTP_USER_AGENT']);
        // ip
        $ip = 0;
        if (preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $_SERVER['REMOTE_ADDR'])) {
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
            $ip = ($ip < 0)? ($ip + 0xffffffff + 1) : $ip;
        }
        // referrer
        $referrer = @parse_url($_SERVER['HTTP_REFERER']);
        if ($referrer && isset($referrer['host']) && ($referrer['host'] != $_SERVER['HTTP_HOST'])) {
            $referrer = $referrer['host'];
        } else {
            $referrer = '';
        }

        if (!empty($this->_SessionID)) {
            // Now we sync with a previous session only if has changed
            if ($GLOBALS['app']->Session->_HasChanged) {
                $params = array();
                $serialized = serialize($GLOBALS['app']->Session->_Attributes);
                $params['sid']        = $this->_SessionID;
                $params['data']       = $serialized;
                $params['user']       = $GLOBALS['app']->Session->GetAttribute('user');
                $params['longevity']  = $GLOBALS['app']->Session->GetAttribute('longevity');
                $params['referrer']   = md5($referrer);
                $params['checksum']   = md5($params['user'] . $serialized);
                $params['ip']         = $ip;
                $params['agent']      = $agent;
                $params['updatetime'] = time();

                $sql = '
                    UPDATE [[session]] SET
                        [user]       = {user},
                        [data]       = {data},
                        [longevity]  = {longevity},
                        [referrer]   = {referrer},
                        [checksum]   = {checksum},
                        [ip]         = {ip},
                        [agent]      = {agent},
                        [updatetime] = {updatetime}
                    WHERE [sid] = {sid}';

                $result = $GLOBALS['db']->query($sql, $params);
                if (!Jaws_Error::IsError($result)) {
                    $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Session synchronized succesfully');
                    return $this->_SessionID;
                }
            } else {
                $params = array();
                $params['sid']        = $this->_SessionID;
                $params['updatetime'] = time();
                $sql = '
                    UPDATE [[session]] SET
                        [updatetime] = {updatetime}
                    WHERE [sid] = {sid}';
                $result = $GLOBALS['db']->query($sql, $params);
                if (!Jaws_Error::IsError($result)) {
                    $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Session synchronized succesfully(only modification time)');
                    return $this->_SessionID;
                }
            }
        } else {
            //A new session, we insert it to the DB
            $updatetime = time();
            $GLOBALS['app']->Session->SetAttribute('groups', array());
            $serialized = serialize($GLOBALS['app']->Session->_Attributes);

            $params = array();
            $params['data']       = $serialized;
            $params['longevity']  = $GLOBALS['app']->Session->GetAttribute('longevity');
            $params['app_type']   = APP_TYPE;
            $params['user']       = $GLOBALS['app']->Session->GetAttribute('user');
            $params['referrer']   = md5($referrer);
            $params['checksum']   = md5($params['user'] . $serialized);
            $params['ip']         = $ip;
            $params['agent']      = $agent;
            $params['updatetime'] = $updatetime;
            $params['createtime'] = $updatetime;

            $sql = '
                INSERT INTO [[session]]
                    ([user], [type], [longevity], [data], [referrer], [checksum],
                     [ip], [agent], [createtime], [updatetime])
                VALUES
                    ({user}, {app_type}, {longevity}, {data}, {referrer}, {checksum},
                     {ip}, {agent}, {createtime}, {updatetime})';

            $result = $GLOBALS['db']->query($sql, $params);
            if (!Jaws_Error::IsError($result)) {
                $result = $GLOBALS['db']->lastInsertID('session', 'sid');
                if (!Jaws_Error::IsError($result) && !empty($result)) {
                    return $result;
                }
            }
        }

        return false;
    }

    /**
     * Delete a session
     *
     * @access  public
     * @param   int     $sid  Session ID
     * @return  bool    True if success, otherwise False
     */
    function Delete($sid)
    {
        $sql = 'DELETE FROM [[session]] WHERE [sid] = {sid}';
        $result = $GLOBALS['db']->query($sql, array('sid' => $sid));
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Deletes all sessions of an user
     *
     * @access  public
     * @param   string  $user   User's ID
     * @return  bool    True if success, otherwise False
     */
    function DeleteUserSessions($user)
    {
        //Get the sessions ID of the user
        $sql = 'DELETE FROM [[session]] WHERE [user] = {user}';
        $result = $GLOBALS['db']->query($sql, array('user' => (string)$user));
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Delete expired sessions
     *
     * @access  public
     * @return  bool    True if success, otherwise False
     */
    function DeleteExpiredSessions()
    {
        $params = array();
        $params['expired'] = time() - ($GLOBALS['app']->Registry->Get('session_idle_timeout', 'Policy', JAWS_COMPONENT_GADGET) * 60);
        $sql = "DELETE FROM [[session]] WHERE [updatetime] < ({expired} - [longevity])";
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Returns all users's sessions count
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   bool    $onlyOnline Optional only count of online sessions
     * @return  mixed   Sessions    count/False if error occurs when runing query
     */
    function GetUserSessions($user, $onlyOnline = false)
    {
        $params = array();
        $params['user'] = (string)$user;
        $params['expired'] = time() - ($GLOBALS['app']->Registry->Get('session_idle_timeout', 'Policy', JAWS_COMPONENT_GADGET) * 60);
        $sql = '
            SELECT COUNT([user])
            FROM [[session]]
            WHERE [user] = {user}';

        if ($onlyOnline) {
            $sql.= ' AND [updatetime] >= {expired}';
        }

        $count = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::isError($count)) {
            return false;
        }

        return (int)$count;
    }

    /**
     * Returns the session's attributes
     *
     * @access  private
     * @param   string  $sid    Session ID
     * @return  mixed   Session's attributes if exist, otherwise False
     */
    function GetSession($sid)
    {
        $params = array();
        $params['sid'] = $sid;

        $sql = '
            SELECT
                [sid], [user], [data], [referrer], [checksum], [ip], [agent],
                [updatetime], [longevity]
            FROM [[session]]
            WHERE
                [sid] = {sid}';

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (!Jaws_Error::isError($result) && isset($result['sid'])) {
            return $result;
        }

        return false;
    }

    /**
     * Returns the sessions attributes
     *
     * @access  public
     * @param   bool    $onlyActive     Only return active session
     * @return  mixed   Sessions attributes if successfully, otherwise Jaws_Error
     */
    function GetSessions($onlyActive = true)
    {
        // remove expired session
        $this->DeleteExpiredSessions();

        $idle_timeout = (int)$GLOBALS['app']->Registry->Get('session_idle_timeout', 'Policy', JAWS_COMPONENT_GADGET);
        $params = array();
        $params['onlinetime'] = time() - ($idle_timeout * 60);

        $sql = '
            SELECT
                [sid], [domain], [user], [type], [longevity], [ip], [agent], [referrer],
                [data], [createtime], [updatetime]
            FROM
                [[session]]
            ';

        if ($onlyActive) {
            $sql.= 'WHERE [updatetime] >= {onlinetime}';
        }

        $sql.= ' ORDER BY [updatetime] DESC';

        $types = array(
            'integer', 'text', 'text', 'text', 'integer', 'integer', 'text', 'text',
            'text', 'integer', 'integer',
        );
        $sessions = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::isError($sessions)) {
            return $sessions;
        }

        foreach ($sessions as $key => $session) {
            if ($data = @unserialize($session['data'])) {
                $sessions[$key]['internal']   = $data['internal'];
                $sessions[$key]['username']   = $data['username'];
                $sessions[$key]['superadmin'] = $data['superadmin'];
                $sessions[$key]['groups']     = $data['groups'];
                $sessions[$key]['nickname']   = $data['nickname'];
                $sessions[$key]['email']      = $data['email'];
                $sessions[$key]['avatar']     = $data['avatar'];
                $sessions[$key]['online']     = $session['updatetime'] > (time() - ($idle_timeout * 60));
                unset($sessions[$key]['data']);
            }
        }

        return $sessions;
    }

    /**
     * Push response data
     *
     * @access  public
     * @param   mixed   $data       Response data
     * @param   string  $resource   Response name
     * @return  void
     */
    function PushResponse($data, $resource = 'Response')
    {
        $this->SetAttribute($resource, $data);
    }

    /**
     * Returns the response data
     *
     * @access  public
     * @param   string  $resource   Resource's name
     * @param   bool    $remove     Optional remove popped response
     * @return  mixed   Response data, or Null if resource not found
     */
    function PopResponse($resource = 'Response', $remove = true)
    {
        $response = $this->GetAttribute($resource);
        if ($remove) {
            // move it into attributes trash
            $this->DeleteAttribute($resource, true);
        }

        return $response;
    }

    /**
     * Push response data
     *
     * @deprecated
     * @access  public
     * @param   mixed   $data       Response data
     * @param   string  $resource   Response name
     * @return  void
     */
    function PushSimpleResponse($data, $resource = 'SimpleResponse')
    {
        $this->PushResponse($data, $resource);
    }

    /**
     * Returns the response data
     *
     * @deprecated
     * @access  public
     * @param   string  $resource   Resource's name
     * @param   bool    $remove     Optional remove popped response
     * @return  mixed   Response data, or Null if resource not found
     */
    function PopSimpleResponse($resource = 'SimpleResponse', $remove = true)
    {
        return $this->PopResponse($resource, $remove);
    }

    /**
     * Add the last response to the session system
     *
     * @access  public
     * @param   string  $msg    Response's message
     * @param   string  $level  Response type
     * @param   mixed   $data   Optional extra data
     * @return  void
     */
    function PushLastResponse($msg, $level = RESPONSE_WARNING, $data = null)
    {
        switch ($level) {
            case RESPONSE_ERROR:
                $css = 'error-message';
                break;
            case RESPONSE_NOTICE:
                $css = 'notice-message';
                break;
            default:
                $level = RESPONSE_WARNING;
                $css = 'warning-message';
                break;
        }

        $this->SetAttribute('LastResponses',
                            array('message' => $msg,
                                  'data'    => $data,
                                  'level'   => $level,
                                  'css'     => $css
                                  )
                            );
    }

    /**
     * Get the response
     *
     * @access  public
     * @param   string  $msg    Response's message
     * @param   string  $level  Response type
     * @param   mixed   $data   Optional extra data
     * @return  array   Returns array include msg, data, level and css class
     */
    function GetResponse($msg, $level = RESPONSE_WARNING, $data = null)
    {
        switch ($level) {
            case RESPONSE_ERROR:
                $css = 'error-message';
                break;
            case RESPONSE_NOTICE:
                $css = 'notice-message';
                break;
            default:
                $level = RESPONSE_WARNING;
                $css = 'warning-message';
                break;
        }

        return array('message' => $msg,
                     'data'    => $data,
                     'level'   => $level,
                     'css'     => $css);
    }

    /**
     * Return and deletes the last response pushed
     *
     * @access  public
     * @return  mixed   Last responses array if exist, otherwise False
     */
    function PopLastResponse()
    {
        $responses = $this->GetAttribute('LastResponses');
        if ($responses === null) {
            return false;
        }

        $this->DeleteAttribute('LastResponses');
        $responses = array_reverse($responses);
        if (empty($responses[0]['message'])) {
            return false;
        }

        return $responses;
    }

}