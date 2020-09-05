<?php
/**
 * Responses error/warning/notice
 */
define('RESPONSE_ERROR',   'alert-danger');
define('RESPONSE_WARNING', 'alert-warning');
define('RESPONSE_NOTICE',  'alert-success');

/**
 * Session variable scope gadget/app/user
 */
define('SESSION_SCOPE_GADGET', 0);
define('SESSION_SCOPE_APP',    1);
define('SESSION_SCOPE_USER',   2);

/**
 * Class to manage User session.
 *
 * @category   Session
 * @package    Core
 * @author     Ivan -sk8- Chavero <imcsk8@gluch.org.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Session
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * session array
     * @var     array   $session
     * @access  protected
     */
    protected $session = array(
        'id'        => 0,
        'domain'    => '',
        'user'      => 0,
        'salt'      => '',
        'type'      => JAWS_APPTYPE,
        'auth'      => '',
        'hidden'    => false,
        'longevity' => 0,
        'ip'        => 0,
        'agent'     => 0,
        'webpush'   => '',
    );

    /**
     * request session id/salt
     * @var     array   $request
     * @access  protected
     */
    protected $request = array(
        'id'   => 0,
        'salt' => ''
    );

    /**
     * session changed flag 
     * @var     bool    $changed
     * @access  protected
     */
    protected $changed = false;

    /**
     * user attributes array
     * @var     array   $userAttributes
     * @access  protected
     * @see     __get(), __set()
     */
    protected $userAttributes = array();

    /**
     * Attributes array
     * @var     array   $attributes
     * @access  protected
     * @see     SetAttribute(), GetAttibute()
     */
    protected $attributes = array();

    /**
     * Attributes array trash
     * @var     array $trash
     * @access  protected
     * @see     setAttribute(), getAttibute()
     */
    protected $trash = array();

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    protected function __construct()
    {
        $this->app = Jaws::getInstance();
        // get ip record id from database
        $addr = Jaws_Utils::GetRemoteAddress();
        $hash = hash64($addr['proxy'] . $addr['client']);
        $this->app->ip = Jaws_ORM::getInstance()
            ->table('ip')
            ->select('id:integer', 'robot:boolean')
            ->igsert(
                array(
                    'hash'   => $hash,
                    'proxy'  => $addr['proxy'],
                    'client' => $addr['client'],
                    'robot'  => false
                )
            )->where('hash', $hash)
            ->and()
            ->where('proxy', $addr['proxy'])
            ->and()
            ->where('client', $addr['client'])
            ->exec();
        if (Jaws_Error::IsError($this->app->ip)) {
            Jaws_Error::Fatal('Internal error(1)!, please try again');
        }

        // get agent record id from database
        $agent = Jaws_XSS::filter($_SERVER['HTTP_USER_AGENT']);
        $hash  = hash64($agent);
        $this->app->agent = Jaws_ORM::getInstance()
            ->table('agent')
            ->select('id:integer', 'robot:boolean')
            ->igsert(
                array(
                    'hash'  => $hash,
                    'agent' => $agent,
                    'robot' => false
                )
            )->where('hash', $hash)
            ->exec();
        if (Jaws_Error::IsError($this->app->agent)) {
            Jaws_Error::Fatal('Internal error(2)!, please try again');
        }
    }

    /**
     * An interface for available drivers
     *
     * @access  public
     * @return  object  Jaws_Session type object
     */
    static function factory()
    {
        if (!defined('JAWS_APPTYPE')) {
            $apptype = Jaws::getInstance()->request->fetch('apptype');
            $apptype = empty($apptype)? 'Web' : preg_replace('/[^[:alnum:]_\-]/', '', ucfirst(strtolower($apptype)));
            define('JAWS_APPTYPE', $apptype);
        }

        $file = ROOT_JAWS_PATH . 'include/Jaws/Session/'. JAWS_APPTYPE. '.php';
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
    function init()
    {
        // Delete expired sessions
        if (mt_rand(1, 32) == mt_rand(1, 32)) {
            $this->deleteExpiredSessions();
        }
    }

    /**
     * Logout from session and reset session values
     *
     * @access  public
     * @return  void
     */
    function logout()
    {
        // logout event logging
        $this->app->listener->Shout(
            'Session',
            'Log',
            array(
                'gadget'   => 'Users',
                'action'   => 'Logout',
                'priority' => JAWS_NOTICE,
                'result'   => 200,
                'status'   => true,
            )
        );
        // let everyone know a user has been logout
        $this->app->listener->Shout('Session', 'LogoutUser', $this->attributes);
        $this->reset();
        $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Session logout');
    }

    /**
     * Loads Jaws Session
     *
     * @access  protected
     * @param   string  $sessid Session identifier
     * @return  bool    True if can load session, false if not
     */
    function load($sessid)
    {
        @list($this->request['id'], $this->request['salt']) = explode('-', $sessid);
        $this->request['id'] = (int)$this->request['id'];

        $session = $this->getSession($this->request['id']);
        try {
            // session exists
            if (Jaws_Error::IsError($session) || empty($session)) {
                throw new Exception('No previous session exists', JAWS_LOG_INFO);
            }

            $this->session = $session;
            $this->attributes         = unserialize($this->session['data']);
            $this->userAttributes     = unserialize($this->session['user_attributes']);
            $this->session['webpush'] = unserialize($this->session['webpush']);
            $checksum = md5($this->session['user'] . $this->session['user_attributes']);
            unset($this->session['data'], $this->session['user_attributes']);

            // browser agent
            if ($this->app->agent['id'] != $this->session['agent']) {
                throw new Exception('Previous session agent has been changed', JAWS_LOG_NOTICE);
            }

            // session longevity
            $expTime = time() - 60 * (int)$this->app->registry->fetch('session_idle_timeout', 'Policy');
            if ($this->session['update_time'] < ($expTime - $this->session['longevity'])) {
                throw new Exception('Previous session has expired', JAWS_LOG_INFO);
            }

            // salt
            if ($this->request['salt'] !== $this->session['salt']) {
                define('SESSION_INVALID', true);
                // no permission for execution all actions
                define('SESSION_RESTRICTED_GADGETS', '');
                $this->reset();
                $GLOBALS['log']->Log(JAWS_LOG_INFO, 'Session salt has been changed');
            }

            // checksum
            if ($checksum !== $this->session['checksum']) {
                throw new Exception('Session checksum has been changed', JAWS_LOG_NOTICE);
            }

            if (!empty($this->session['user'])) {
                // user expiry date
                $expiry_date = $this->userAttributes['expiry_date'];
                if (!empty($expiry_date) && $expiry_date <= time()) {
                    throw new Exception('This username is expired', JAWS_LOG_NOTICE);
                }

                // logon hours
                $logon_hours = $this->userAttributes['logon_hours'];
                if (!empty($logon_hours)) {
                    $wdhour = explode(',', $this->app->UTC2UserTime(time(), 'w,G', true));
                    $lhByte = hexdec($logon_hours[$wdhour[0]*6 + intval($wdhour[1]/4)]);
                    if ((pow(2, fmod($wdhour[1], 4)) & $lhByte) == 0) {
                        throw new Exception('Logon hours terminated', JAWS_LOG_NOTICE);
                    }
                }

                // concurrent logins
                if ($this->session['update_time'] < $expTime) {
                    $logins = $this->userAttributes['concurrents'];
                    $existSessions = $this->getUserSessionsCount($this->session['user'], true);
                    if (!empty($existSessions) && !empty($logins) && $existSessions >= $logins) {
                        throw new Exception('Maximum number of concurrent logins reached', JAWS_LOG_NOTICE);
                    }
                }
            }

            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Session was OK');
            return true;
        } catch (Exception $error) {
            $GLOBALS['log']->Log($error->getCode(), $error->getMessage());
            return false;
        }
    }

    /**
     * Create a new session for a given data
     *
     * @access  protected
     * @param   array   $userAttributes User's attributes
     * @param   bool    $remember       Remember me
     * @return  bool    True if can create session
     */
    function create($userAttributes = array(), $remember = false)
    {
        $this->userAttributes = array(
            'id'          => 0,
            'internal'    => false,
            'auth'        => '',
            'domain'      => '',
            'username'    => '',
            'superadmin'  => false,
            'groups'      => array(),
            'logon_hours' => '',
            'expiry_date' => 0,
            'concurrents' => 0,
            'logged'      => false,
            'layout'      => 0,
            'nickname'    => '',
            'email'       => '',
            'mobile'      => '',
            'ssn'         => '',
            'avatar'      => '',
        );

        if (!empty($userAttributes)) {
            $userAttributes['logged'] = !empty($userAttributes['id']);
            // set given valid user attributes
            $attributes = array_intersect(array_keys($userAttributes), array_keys($this->userAttributes));

            foreach ($attributes as $attribute) {
                $this->userAttributes[$attribute] = $userAttributes[$attribute];
            }
        }

        // ip
        $ip = 0;
        if (preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $_SERVER['REMOTE_ADDR'])) {
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
            $ip = ($ip < 0)? ($ip + 0xffffffff + 1) : $ip;
        }

        $this->session = array(
            'id'        => $this->session['id'],
            'domain'    => $this->userAttributes['domain'],
            'user'      => $this->userAttributes['id'],
            'salt'      => uniqid('', true),
            'type'      => JAWS_APPTYPE,
            'auth'      => $this->userAttributes['auth'],
            'hidden'    => $this->session['hidden'],
            'longevity' => $remember? (int)$this->app->registry->fetch('session_remember_timeout', 'Policy')*3600 : 0,
            'ip'        => $this->app->ip['id'],
            'agent'     => $this->app->agent['id'],
            'webpush'   => $this->session['webpush'],
        );

        $this->changed = true;
        if (empty($this->session['id'])) {
            $this->session['id'] = $this->insert();
        } else {
            $this->update(true);
        }

        return true;
    }

    /**
     * Extra session check
     *
     * @access  public
     * @return  bool    True if can create session
     */
    function extraCheck()
    {
        if (isset($_SERVER['ORIGIN'])) {
            $GLOBALS['log']->Log(JAWS_LOG_NOTICE, 'cross-origin resource sharing detected');
            return false;
        }

        return true;
    }

    /**
     * Reset current session
     *
     * @access  protected
     * @return  bool    True if can reset it
     */
    function reset()
    {
        // session
        $this->session['user']      = 0;
        $this->session['auth']      = '';
        $this->session['domain']    = 0;
        $this->session['hidden']    = false;
        $this->session['longevity'] = 0;

        // attributes
        $this->attributes = array();

        // user attributes
        $this->userAttributes = array(
            'id'          => 0,
            'internal'    => false,
            'auth'        => '',
            'domain'      => '',
            'username'    => '',
            'superadmin'  => false,
            'groups'      => array(),
            'logon_hours' => '',
            'expiry_date' => 0,
            'concurrents' => 0,
            'logged'      => false,
            'layout'      => 0,
            'nickname'    => '',
            'email'       => '',
            'mobile'      => '',
            'ssn'         => '',
            'avatar'      => '',
        );

        $this->changed = true;
        return true;
    }

    /**
     * Set a session attribute
     *
     * @access  public
     * @param   string  $name       Attribute name
     * @param   mixed   $value      Attribute value
     * @param   bool    $trashed    Trashed attribute(eliminated end of current request)
     * @param   string  $component  Component name
     * @return  bool    True if can set value
     */
    function setAttribute($name, $value, $trashed = false, $component = '')
    {
        if ($trashed) {
            $this->trash[$component][$name] = $value;
        } else {
            $this->changed = true;
            if (is_array($value) && $name == 'LastResponses') {
                $this->attributes[$component]['LastResponses'][] = $value;
            } else {
                $this->attributes[$component][$name] = $value;
            }
        }

        return true;
    }

    /**
     * Get a session attribute
     *
     * @access  public
     * @param   string  $name       Attribute name
     * @param   string  $component  Component name
     * @return  mixed   Value of the attribute or Null if not exist
     */
    function getAttribute($name, $component = '')
    {
        if (array_key_exists($component, $this->attributes) &&
            array_key_exists($name, $this->attributes[$component])
        ) {
            return $this->attributes[$component][$name];
        } elseif (array_key_exists($component, $this->trash) && 
            array_key_exists($name, $this->trash[$component])
        ) {
            return $this->trash[$component][$name];
        }

        return null;
    }

    /**
     * Get value of given session's attributes
     *
     * @access  public
     * @param   string  $component  Component name
     * @param   array   $attributes Attributes array, if not pass retuen all attributes of component
     * @return  array   Value of the attributes
     */
    function getAttributes($component = '', $attributes = array())
    {
        if (empty($attributes)) {
            if (array_key_exists($component, $this->attributes)) {
                return $this->attributes[$component];
            }

            return array();
        }

        $result = array();
        foreach ($attributes as $attribute) {
            $result[$attribute] = $this->getAttribute($attribute, $component);
        }

        return $result;
    }

    /**
     * Delete a session attribute
     *
     * @access  public
     * @param   string  $name       Attribute name
     * @param   bool    $trashed    Move attribute to trash before delete
     * @param   string  $component  Component name
     * @return  bool    True if can delete value
     */
    function deleteAttribute($name, $trashed = false, $component = '')
    {
        if (array_key_exists($component, $this->attributes) &&
            array_key_exists($name, $this->attributes[$component])
        ) {
            $this->changed = true;
            if ($trashed) {
                $this->trash[$component][$name] = $this->attributes[$component][$name];
            }
            unset($this->attributes[$component][$name]);
        } elseif (!$trashed && array_key_exists($component, $this->trash) &&
            array_key_exists($name, $this->trash[$component])
        ) {
            unset($this->trash[$component][$name]);
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
    function getPermission($gadget, $key, $subkey = '', $together = true)
    {
        $user = $this->session['user'];
        $groups = $this->userAttributes['groups'];
        $keys = array_filter(array_map('trim', explode(',', $key)));
        $perms = array();
        foreach ($keys as $key) {
            $perm = $this->app->acl->GetFullPermission(
                $user,
                array_keys($groups),
                $gadget,
                $key,
                $subkey,
                $this->userAttributes['superadmin']
            );
            if (!is_null($perm)) {
                $perms[] = $perm;
            }
        }

        return empty($perms)? null : ($together? @min($perms) : @max($perms));
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
    function checkPermission($gadget, $key, $subkey = '', $together = true, $errorMessage = '')
    {
        if ($perm = $this->getPermission($gadget, $key, $subkey, $together)) {
            return $perm;
        }

        if (empty($errorMessage)) {
            $errorMessage = 'User '.$this->userAttributes['username'].
                ' don\'t have permission to execute '.$gadget.'::'.$key. (empty($subkey)? '' : "($subkey)");
        }

        Jaws_Error::Fatal($errorMessage, 1, 403);
    }

    /**
     * update current session
     *
     * @access  public
     * @param   bool    $updateSalt     Update session salt?
     * @return  mixed   Session ID if success, otherwise Jaws_Error or false
     */
    function update($updateSalt = false)
    {
        if (defined('SESSION_INVALID')) {
            return false;
        }

        $sessTable = Jaws_ORM::getInstance()->table('session');
        if ($this->changed) {
            $this->changed = false;
            $userAttributes_serialized = serialize($this->userAttributes);
            $updData = array(
                'domain'      => $this->session['domain'],
                'user'        => $this->session['user'],
                'user_attributes' => $userAttributes_serialized,
                'data'        => serialize($this->attributes),
                'auth'        => $this->session['auth'],
                'longevity'   => $this->session['longevity'],
                'checksum'    => md5($this->session['user'] . $userAttributes_serialized),
                'ip'          => $this->session['ip'],
                'agent'       => $this->session['agent'],
                'webpush'     => serialize($this->session['webpush']),
            );

            if ($updateSalt) {
                $updData['salt'] = $this->session['salt'];
            }
        }

        $updData['update_time'] = time();
        $sessTable->update($updData);
        $result = $sessTable->where('id', $this->session['id'])->exec();
        if (!Jaws_Error::IsError($result)) {
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Session synchronized successfully');
            return $this->session['id'];
        }

        return false;
    }

    /**
     * insert new session
     *
     * @access  public
     * @return  mixed   Session ID if success, otherwise Jaws_Error or false
     */
    function insert()
    {
        $max_active_sessions = (int)$this->app->registry->fetch('max_active_sessions', 'Policy');
        if (!empty($max_active_sessions)) {
            $activeSessions = $this->getSessionsCount(true);
            if ($activeSessions >= $max_active_sessions) {
                // remove expired session
                $this->deleteExpiredSessions();
                Jaws_Error::Fatal(Jaws::t('HTTP_ERROR_CONTENT_503_OVERLOAD'), 0, 503);
            }
        }

        $update_time = time();
        $userAttributes_serialized = serialize($this->userAttributes);
        $result = Jaws_ORM::getInstance()->table('session')->insert(
            array(
                'domain'      => $this->session['domain'],
                'user'        => $this->session['user'],
                'user_attributes' => $userAttributes_serialized,
                'data'        => serialize($this->attributes),
                'type'        => $this->session['type'],
                'longevity'   => $this->session['longevity'],
                'salt'        => $this->session['salt'],
                'checksum'    => md5($this->session['user'] . $userAttributes_serialized),
                'ip'          => $this->session['ip'],
                'agent'       => $this->session['agent'],
                'webpush'     => serialize($this->session['webpush']),
                'insert_time' => $update_time,
                'update_time' => $update_time
            )
        )->exec();
        if (!Jaws_Error::IsError($result)) {
            return $result;
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
    function delete($sid)
    {
        $result = true;
        if (!empty($sid)) {
            $sessTable = Jaws_ORM::getInstance()->table('session');
            if (is_array($sid)) {
                $result = $sessTable->delete()->where('id', $sid, 'in')->exec();
            } else {
                $result = $sessTable->delete()->where('id', $sid)->exec();
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
    function deleteUserSessions($user)
    {
        //Get the sessions ID of the user
        $sessTable = Jaws_ORM::getInstance()->table('session');
        $result = $sessTable->delete()->where('user', (int)$user)->exec();
        return Jaws_Error::IsError($result)? false : true;
    }

    /**
     * Delete expired sessions
     *
     * @access  public
     * @return  bool    True if success, otherwise False
     */
    function deleteExpiredSessions()
    {
        $expired = time() - ($this->app->registry->fetch('session_idle_timeout', 'Policy') * 60);
        $sessTable = Jaws_ORM::getInstance()->table('session');
        $result = $sessTable->delete()
            ->where('update_time', $sessTable->expr('? - longevity', $expired), '<')
            ->exec();
        return Jaws_Error::IsError($result)? false : true;
    }

    /**
     * Returns all user's sessions count
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   bool    $onlyOnline Optional only count of online sessions
     * @return  mixed   Sessions    count/False if error occurs when runing query
     */
    function getUserSessionsCount($user, $onlyOnline = false)
    {
        $expired = time() - ($this->app->registry->fetch('session_idle_timeout', 'Policy') * 60);
        $sessTable = Jaws_ORM::getInstance()->table('session');
        $sessTable->select('count(user)')->where('user', (int)$user);
        if ($onlyOnline) {
            $sessTable->and()->where('update_time', $expired, '>=');
        }
        $result = $sessTable->fetchOne();
        return Jaws_Error::isError($result)? false : (int)$result;
    }

    /**
     * Returns the session's attributes
     *
     * @access  private
     * @param   int     $sid    Session ID
     * @return  mixed   Session's attributes if exist, otherwise False
     */
    function getSession($sid)
    {
        $sessTable = Jaws_ORM::getInstance()->table('session');
        $sessTable->select(
            'id:integer', 'salt', 'domain', 'user:integer', 'type', 'auth', 'longevity', 'ip:integer',
            'agent:integer', 'user_attributes', 'data', 'webpush', 'hidden:boolean', 'checksum',
            'update_time:integer'
        );
        return $sessTable->where('id', (int)$sid)->fetchRow();
    }

    /**
     * Returns the sessions attributes
     *
     * @access  public
     * @param   bool    $logged Logged user's session
                (null: all sessions, true: logged users sessions, false: anonymous sessions, numericL specific user)
     * @param   bool    $active Active session
     * @param   string  $type   Session type
     * @param   int     $limit
     * @param   int     $offset
     * @return  mixed   Sessions attributes if successfully, otherwise Jaws_Error
     */
    function getSessions($logged = null, $active = null, $type = null, $limit = 0, $offset = null)
    {
        // remove expired session
        $this->deleteExpiredSessions();

        $idle_timeout = (int)$this->app->registry->fetch('session_idle_timeout', 'Policy');
        $onlinetime = time() - ($idle_timeout * 60);

        $sessTable = Jaws_ORM::getInstance()->table('session');
        $sessTable->select(
            'session.id', 'domain', 'user', 'type', 'auth', 'longevity',
            'ip:integer', 'session.agent:integer', 'ip.proxy', 'ip.client', 'agent.agent as agent_text',
            'user_attributes', 'webpush', 'checksum', 'insert_time', 'update_time:integer'
        );
        $sessTable->join('ip', 'ip.id', 'session.ip');
        $sessTable->join('agent', 'agent.id', 'session.agent');

        if ($active === true) {
            $sessTable->where('update_time', $onlinetime, '>=');
        } elseif ($active === false) {
            $sessTable->where('update_time', $onlinetime, '<');
        }

        if ($logged === true) {
            $sessTable->and()->where('user', 0, '<>');
        } elseif ($logged === false) {
            $sessTable->and()->where('user', 0);
        } elseif (is_numeric($logged)) {
            $sessTable->and()->where('user', (int)$logged);
        }

        if (!empty($type)) {
            $sessTable->and()->where('type', $type);
        }
        $sessions = $sessTable->orderBy('update_time desc')->limit($limit, $offset)->fetchAll();
        if (Jaws_Error::isError($sessions)) {
            return $sessions;
        }

        foreach ($sessions as $key => $session) {
            $sessions[$key]['proxy']  = inet_ntop(base64_decode($sessions[$key]['proxy']));
            $sessions[$key]['client'] = inet_ntop(base64_decode($sessions[$key]['client']));

            if ($userAttributes = @unserialize($session['user_attributes'])) {
                $sessions[$key]['internal']   = $userAttributes['internal'];
                $sessions[$key]['username']   = $userAttributes['username'];
                $sessions[$key]['superadmin'] = $userAttributes['superadmin'];
                $sessions[$key]['groups']     = $userAttributes['groups'];
                $sessions[$key]['nickname']   = $userAttributes['nickname'];
                $sessions[$key]['email']      = $userAttributes['email'];
                $sessions[$key]['mobile']     = $userAttributes['mobile'];
                $sessions[$key]['avatar']     = $userAttributes['avatar'];
                $sessions[$key]['online']     = $session['update_time'] > (time() - ($idle_timeout * 60));
            }

            unset($sessions[$key]['userAttributes'], $sessions[$key]['data']);
        }

        return $sessions;
    }

    /**
     * Returns the count of active sessions
     *
     * @access  public
     * @param   bool    $logged Logged user's session
                (null: all sessions, true: logged users sessions, false: anonymous sessions, numericL specific user)
     * @param   bool    $active Active session
     * @param   string  $type   Session type
     * @return  mixed   Active sessions count if successfully, otherwise Jaws_Error
     */
    function getSessionsCount($logged = null, $active = null, $type = null)
    {
        $idle_timeout = (int)$this->app->registry->fetch('session_idle_timeout', 'Policy');
        $onlinetime = time() - ($idle_timeout * 60);

        $sessTable = Jaws_ORM::getInstance()->table('session');
        $sessTable->select('count(id):integer');

        if ($active === true) {
            $sessTable->where('update_time', $onlinetime, '>=');
        } elseif ($active === false) {
            $sessTable->where('update_time', $onlinetime, '<');
        }
        if ($logged === true) {
            $sessTable->and()->where('user', 0, '<>');
        } elseif ($logged === false) {
            $sessTable->and()->where('user', 0);
        } elseif (is_numeric($logged)) {
            $sessTable->and()->where('user', (int)$logged);
        }

        if (!empty($type)) {
            $sessTable->and()->where('type', $type);
        }

        $result = $sessTable->fetchOne();
        return Jaws_Error::isError($result)? 0 : (int)$result;
    }

    /**
     * Push response data
     *
     * @access  public
     * @param   string  $text       Response text
     * @param   string  $type       Response type
     * @param   string  $resource   Response name
     * @param   mixed   $data       Response data
     * @param   int     $code       Response code
     * @param   string  $component  Component name
     * @return  void
     */
    function pushResponse(
        $text, $type = RESPONSE_NOTICE, $resource = 'Response', $data = null, $code = 0, $component = ''
    ) {
        $resource_data = $this->getAttribute($resource, $component);
        if (!is_array($resource_data)) {
            $resource_data = array();
        }

        array_push(
            $resource_data,
            array(
                'text' => $text,
                'type' => $type,
                'data' => $data,
                'code' => $code
            )
        );

        $this->setAttribute(
            $resource,
            $resource_data,
            false,
            $component
        );
    }

    /**
     * Returns the response data
     *
     * @access  public
     * @param   string  $resource   Resource's name
     * @param   bool    $remove     Optional remove popped response
     * @param   string  $component  Component name
     * @return  mixed   Response data, or Null if resource not found
     */
    function popResponse($resource = 'Response', $remove = true, $component = '')
    {
        $resource_data = $this->getAttribute($resource, $component);
        if (!is_array($resource_data)) {
            $resource_data = array();
        }
        $response = array_pop($resource_data);

        if (is_null($resource_data)) {
            $this->deleteAttribute($resource, false, $component);
        } else {
            $this->setAttribute(
                $resource,
                $resource_data,
                false,
                $component
            );
        }

        return $response;
    }

    /**
     * Get the response
     *
     * @access  public
     * @param   string  $text   Response text
     * @param   string  $type   Response type
     * @param   mixed   $data   Response data
     * @param   int     $code   Response code
     * @return  array   Returns array include text, type, data and code class
     */
    function getResponse($text, $type = RESPONSE_NOTICE, $data = null, $code = 0)
    {
        return array(
            'text' => $text,
            'type' => $type,
            'data' => $data,
            'code' => $code
        );
    }

    /**
     * Overloading __get magic method
     *
     * @access  public
     * @param   string  $property   Property name
     * @return  mixed   Requested property otherwise Jaws_Error
     */
    function __get($property)
    {
        // user attributes
        if ($property == 'user') {
            //FIXME: temporary, find better way
            $userAttributes = (object)$this->userAttributes;
            return $userAttributes;
        }

        // session
        if (array_key_exists($property, $this->session)) {
            //id, type, auth, domain, webpush
            return $this->session[$property];
        }

        return Jaws_Error::raiseError("Property '$property' not exists!", __FUNCTION__);
    }

    /**
     * Overloading __set magic method
     *
     * @access  public
     * @param   string  $property   Property name
     * @param   mixed   $value      Property value
     * @return  void
     */
    function __set($property, $value)
    {
        switch ($property) {
            // user attributes
            case 'user':
                if (is_array($value)) {
                    $this->changed = true;
                    // set given valid user attributes
                    $attributes = array_intersect(array_keys($value), array_keys($this->userAttributes));
                    foreach ($attributes as $attribute) {
                        $this->userAttributes[$attribute] = $value[$attribute];

                        // set session attribute those related to user's attributes
                        if (in_array($attribute, array('domain', 'groups', 'auth'))) {
                            $this->session[$attribute] = $value[$attribute];
                        }
                    }
                }
                break;

            case 'webpush':
                $this->changed = true;
                $this->session['webpush'] = $value;
                break;

            default:
                Jaws_Error::raiseError("Property '$property' not exists!", __FUNCTION__);
                break;
        }

        return;
    }

    /**
     * Overloading __isset magic method
     * Triggered by calling isset() or empty()on inaccessible (protected or private) or non-existing properties
     *
     * @access  public
     * @param   string  $property   Property name
     * @return  bool    Requested property otherwise Jaws_Error
     */
    function __isset($property)
    {
        return array_key_exists($property, $this->session);
    }

}