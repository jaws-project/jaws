<?php
/**
 * Responses warning
 */
define('RESPONSE_WARNING', 'response_warning');
/**
 * Responses error
 */
define('RESPONSE_ERROR',   'response_error');
/**
 * Responses notice
 */
define('RESPONSE_NOTICE',  'response_notice');
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
     * Authentication type
     * @var     string $_AuthType
     * @access  private
     */
    var $_AuthType;

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
     * An interface for available drivers
     *
     * @access  public
     * @return  object  Jaws_Session type object
     */
    static function factory()
    {
        if (!defined('JAWS_APPTYPE')) {
            $apptype = jaws()->request->fetch('apptype');
            $apptype = empty($apptype)? 'Web' : preg_replace('/[^[:alnum:]_-]/', '', ucfirst(strtolower($apptype)));
            define('JAWS_APPTYPE', $apptype);
        }

        $file = JAWS_PATH . 'include/Jaws/Session/'. JAWS_APPTYPE. '.php';
        if (file_exists($file)) {
            include_once($file);
            $className = 'Jaws_Session_'. JAWS_APPTYPE;
            $obj = new $className();
            return $obj;
        }

        Jaws_Error::Fatal('Loading session '. JAWS_APPTYPE. ' failed.');
    }

    /**
     * Initializes the Session
     *
     * @access  public
     * @return  void
     */
    function Init()
    {
        $this->_AuthType = $GLOBALS['app']->Registry->fetch('authtype', 'Users');
        $this->_AuthType = preg_replace('/[^[:alnum:]_-]/', '', $this->_AuthType);
        $authFile = JAWS_PATH . 'include/Jaws/Auth/' . $this->_AuthType . '.php';
        if (empty($this->_AuthType) || !file_exists($authFile)) {
            $GLOBALS['log']->Log(
                JAWS_LOG_NOTICE,
                $authFile. ' file doesn\'t exists, using default authentication type'
            );
            $this->_AuthType = 'Default';
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
     * @param   string  $authtype   Authentication type
     * @return  mixed   An Array of user's attributes if success, otherwise Jaws_Error
     */
    function Login($username, $password, $remember, $authtype = '')
    {
        $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'LOGGIN IN');
        if ($username === '' && $password === '') {
            $result = Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_WRONG'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        } else {
            if (!empty($authtype)) {
                $authtype = preg_replace('/[^[:alnum:]_-]/', '', $authtype);
            } else {
                $authtype = $this->_AuthType;
            }

            require_once JAWS_PATH . 'include/Jaws/Auth/' . $authtype . '.php';
            $className = 'Jaws_Auth_' . $authtype;
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
                        // Login event Logging
                        $GLOBALS['app']->Listener->Shout('Log', array('Users', 'Login', JAWS_WARNING));
                        // Let everyone know a user has been logged
                        $GLOBALS['app']->Listener->Shout('LoginUser');
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
        $GLOBALS['app']->Listener->Shout('Log', array('Users', 'Logout', JAWS_WARNING));
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
            $expTime = time() - 60 * (int) $GLOBALS['app']->Registry->fetch('session_idle_timeout', 'Policy');

            $this->_SessionID  = $session['sid'];
            $this->_Attributes = unserialize($session['data']);
            $this->SetAttribute('sid', $this->_SessionID, true);

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
            $agent = substr(Jaws_XSS::filter($_SERVER['HTTP_USER_AGENT']), 0, 252);
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

            if ($_SERVER['REQUEST_METHOD'] == 'GET' ||
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
            $info['layout']      = 0;
            $info['nickname']    = '';
            $info['logon_hours'] = '';
            $info['expiry_date'] = 0;
            $info['concurrents'] = 0;
            $info['email']      = '';
            $info['url']        = '';
            $info['avatar']     = '';
        }

        $this->_Attributes = array();
        $this->SetAttribute('sid',         $this->_SessionID, true);
        $this->SetAttribute('user',        $info['id']);
        $this->SetAttribute('internal',    $info['internal']);
        $this->SetAttribute('salt',        uniqid(mt_rand(), true));
        $this->SetAttribute('type',        JAWS_APPTYPE);
        $this->SetAttribute('username',    $info['username']);
        $this->SetAttribute('superadmin',  $info['superadmin']);
        $this->SetAttribute('groups',      $info['groups']);
        $this->SetAttribute('logon_hours', $info['logon_hours']);
        $this->SetAttribute('expiry_date', $info['expiry_date']);
        $this->SetAttribute('concurrents', $info['concurrents']);
        $this->SetAttribute(
            'longevity', 
            $remember? (int)$GLOBALS['app']->Registry->fetch('session_remember_timeout', 'Policy')*3600 : 0
        );
        $this->SetAttribute('logged',     !empty($info['id']));
        $this->SetAttribute('layout',     isset($info['layout'])? $info['layout'] : 0);
        //profile
        $this->SetAttribute('nickname',   $info['nickname']);
        $this->SetAttribute('email',      $info['email']);
        $this->SetAttribute('url',        $info['url']);
        $this->SetAttribute('avatar',     $info['avatar']);

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
        $this->SetAttribute('type',        JAWS_APPTYPE);
        $this->SetAttribute('internal',    false);
        $this->SetAttribute('username',    '');
        $this->SetAttribute('superadmin',  false);
        $this->SetAttribute('groups',      array());
        $this->SetAttribute('logon_hours', '');
        $this->SetAttribute('expiry_date', 0);
        $this->SetAttribute('concurrents', 0);
        $this->SetAttribute('longevity',   0);
        $this->SetAttribute('logged',      false);
        $this->SetAttribute('layout',      0);
        $this->SetAttribute('nickname',    '');
        $this->SetAttribute('email',       '');
        $this->SetAttribute('url',         '');
        $this->SetAttribute('avatar',      '');
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
            $this->_HasChanged =
                !array_key_exists($name, $this->_Attributes) || ($this->_Attributes[$name] !== $value);
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
     * @param   string  $key        ACL key name
     * @param   string  $subkey     ACL subkey name
     * @param   bool    $together   And/Or tasks permission result, default true
     * @return  bool    True if granted, else False
     */
    function GetPermission($gadget, $key, $subkey = '', $together = true)
    {
        $user = $this->GetAttribute('user');
        $groups = $this->GetAttribute('groups');
        $keys = array_filter(array_map('trim', explode(',', $key)));
        $perms = array();
        foreach ($keys as $key) {
            $perms[] = $GLOBALS['app']->ACL->GetFullPermission(
                $user,
                array_keys($groups),
                $gadget,
                $key,
                $subkey,
                $this->IsSuperAdmin()
            );
        }

        return $together? @min($perms) : @max($perms);
    }

    /**
     * Check permission on a given gadget/task
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   string  $key            ACL key(s) name
     * @param   string  $subkey         ACL subkey name
     * @param   bool    $together       And/Or tasks permission result, default true
     * @param   string  $errorMessage   Error message to return
     * @return  mixed   True if granted, else throws an Exception(Jaws_Error::Fatal)
     */
    function CheckPermission($gadget, $key, $subkey = '', $together = true, $errorMessage = '')
    {
        if ($perm = $this->GetPermission($gadget, $key, $subkey, $together)) {
            return $perm;
        }

        if (empty($errorMessage)) {
            $errorMessage = 'User '.$this->GetAttribute('username').
                ' don\'t have permission to execute '.$gadget.'::'.$key. (empty($subkey)? '' : "($subkey)");
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
        $agent = substr(Jaws_XSS::filter($_SERVER['HTTP_USER_AGENT']), 0, 252);
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

        $sessTable = Jaws_ORM::getInstance()->table('session', '', 'sid');
        if (!empty($this->_SessionID)) {
            // Now we sync with a previous session only if has changed
            if ($GLOBALS['app']->Session->_HasChanged) {
                $user = $GLOBALS['app']->Session->GetAttribute('user');
                $serialized = serialize($GLOBALS['app']->Session->_Attributes);
                $sessTable->update(array(
                    'user'       => $user,
                    'data'       => $serialized,
                    'longevity'  => $GLOBALS['app']->Session->GetAttribute('longevity'),
                    'referrer'   => md5($referrer),
                    'checksum'   => md5($user. $serialized),
                    'ip'         => $ip,
                    'agent'      => $agent,
                    'updatetime' => time()
                ));
                $result = $sessTable->where('sid', $this->_SessionID)->exec();
                if (!Jaws_Error::IsError($result)) {
                    $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Session synchronized succesfully');
                    return $this->_SessionID;
                }
            } else {
                $sessTable->update(array('updatetime' => time()));
                $result = $sessTable->where('sid', $this->_SessionID)->exec();
                if (!Jaws_Error::IsError($result)) {
                    $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Session synchronized succesfully(only modification time)');
                    return $this->_SessionID;
                }
            }
        } elseif (!empty($GLOBALS['app']->Session->_Attributes)) {
            //A new session, we insert it to the DB
            $updatetime = time();
            $user = $GLOBALS['app']->Session->GetAttribute('user');
            $serialized = serialize($GLOBALS['app']->Session->_Attributes);
            $sessTable->insert(array(
                'user'       => $user,
                'type'       => JAWS_APPTYPE,
                'longevity'  => $GLOBALS['app']->Session->GetAttribute('longevity'),
                'data'       => $serialized,
                'referrer'   => md5($referrer),
                'checksum'   => md5($user. $serialized),
                'ip'         => $ip,
                'agent'      => $agent,
                'createtime' => $updatetime,
                'updatetime' => $updatetime
            ));
            $result = $sessTable->exec();
            if (!Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return false;
    }

    /**
     * Delete a session
     *
     * @access  public
     * @param   int|array   $sid  Session ID(s)
     * @return  bool    True if success, otherwise False
     */
    function Delete($sid)
    {
        $result = true;
        if (!empty($sid)) {
            $sessTable = Jaws_ORM::getInstance()->table('session');
            if (is_array($sid)) {
                $result = $sessTable->delete()->where('sid', $sid, 'in')->exec();
            } else {
                $result = $sessTable->delete()->where('sid', $sid)->exec();
            }
        }
        return Jaws_Error::IsError($result)? false : true;
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
        $sessTable = Jaws_ORM::getInstance()->table('session');
        $result = $sessTable->delete()->where('user', (string)$user)->exec();
        return Jaws_Error::IsError($result)? false : true;
    }

    /**
     * Delete expired sessions
     *
     * @access  public
     * @return  bool    True if success, otherwise False
     */
    function DeleteExpiredSessions()
    {
        $expired = time() - ($GLOBALS['app']->Registry->fetch('session_idle_timeout', 'Policy') * 60);
        $sessTable = Jaws_ORM::getInstance()->table('session');
        $result = $sessTable->delete()->where('updatetime', $sessTable->expr('? - longevity', $expired))->exec();
        return Jaws_Error::IsError($result)? false : true;
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
        $expired = time() - ($GLOBALS['app']->Registry->fetch('session_idle_timeout', 'Policy') * 60);
        $sessTable = Jaws_ORM::getInstance()->table('session');
        $sessTable->select('count(user)')->where('user', (string)$user);
        if ($onlyOnline) {
            $sessTable->and()->where('updatetime', $expired, '>=');
        }
        $result = $sessTable->fetchOne();
        return Jaws_Error::isError($result)? false : (int)$result;
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
        $sessTable = Jaws_ORM::getInstance()->table('session');
        $sessTable->select(
            'sid', 'user', 'longevity', 'ip', 'agent', 'referrer', 'data',
            'checksum', 'updatetime:integer'
        );
        return $sessTable->where('sid', $sid)->fetchRow();
    }

    /**
     * Returns the sessions attributes
     *
     * @access  public
     * @param   bool    $active Active session
     * @param   bool    $logged Logged user's session
                (null: all sessions, true: logged users's sessions, false: anonymous sessions)
     * @return  mixed   Sessions attributes if successfully, otherwise Jaws_Error
     */
    function GetSessions($active = true, $logged = null)
    {
        // remove expired session
        $this->DeleteExpiredSessions();

        $idle_timeout = (int)$GLOBALS['app']->Registry->fetch('session_idle_timeout', 'Policy');
        $onlinetime = time() - ($idle_timeout * 60);

        $sessTable = Jaws_ORM::getInstance()->table('session');
        $sessTable->select(
            'sid', 'domain', 'user', 'type', 'longevity', 'ip', 'agent', 'referrer',
            'data', 'checksum', 'createtime', 'updatetime:integer'
        );
        if ($active) {
            $sessTable->where('updatetime', $onlinetime, '>=');
        } elseif ($active === false) {
            $sessTable->where('updatetime', $onlinetime, '<');
        }
        if ($logged) {
            $sessTable->and()->where('user', 1, '>=');
        } elseif ($logged === false) {
            $sessTable->and()->where('user', 1, '<');
        }
        $sessions = $sessTable->orderBy('updatetime desc')->fetchAll();
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
     * @param   string  $text       Response text
     * @param   string  $resource   Response name
     * @param   string  $type       Response type
     * @param   mixed   $data       Response data
     * @return  void
     */
    function PushResponse($text, $resource = 'Response', $type = RESPONSE_NOTICE, $data = null)
    {
        $this->SetAttribute(
            $resource,
            array(
                'text' => $text,
                'type' => $type,
                'data' => $data
            )
        );
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
        $this->SetAttribute($resource, $data);
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
     * @param   string  $text   Response text
     * @param   string  $type   Response type
     * @param   mixed   $data   Response data
     * @return  void
     */
    function PushLastResponse($text, $type = RESPONSE_NOTICE, $data = null)
    {
        $this->SetAttribute(
            'LastResponses',
            array(
                'text' => $text,
                'type' => $type,
                'data' => $data
            )
        );
    }

    /**
     * Get the response
     *
     * @access  public
     * @param   string  $text   Response text
     * @param   string  $type   Response type
     * @param   mixed   $data   Response data
     * @return  array   Returns array include text, type and data class
     */
    function GetResponse($text, $type = RESPONSE_NOTICE, $data = null)
    {
        return array(
            'text' => $text,
            'type' => $type,
            'data' => $data
        );
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
        if (empty($responses[0]['text'])) {
            return false;
        }

        return $responses;
    }

}