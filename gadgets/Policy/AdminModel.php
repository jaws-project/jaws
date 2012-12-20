<?php
/**
 * Policy Gadget Admin
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
require_once JAWS_PATH . 'gadgets/Policy/Model.php';

class PolicyAdminModel extends PolicyModel
{
    /**
     * Installs the gadget
     *
     * @access    public
     * @return    boolean Returns true on a successfull attempt and Jaws Error otherwise
    */
    function InstallGadget()
    {
        $result = $this->gadget->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $this->gadget->AddRegistry(array(
            'block_undefined_ip' => 'false',
            'block_undefined_agent' => 'false',
            'allow_duplicate' => 'no',
            'filter' => 'DISABLED',
            'captcha' => 'DISABLED',
            'captcha_driver' => 'MathCaptcha',
            'obfuscator' => 'DISABLED',
            'akismet_key' => '',
            'typepad_key' => '',
            'crypt_enabled' => 'false',
            'crypt_pub_key' => '',
            'crypt_pvt_key' => '',
            'crypt_key_len' => '128',
            'crypt_key_age' => '86400',
            'crypt_key_start_date' => '0',
            'passwd_bad_count' => '7',
            'passwd_lockedout_time' => '60',      // per second
            'passwd_max_age' => '0',              // per day  0 = resistant
            'passwd_min_length' => '0',
            'passwd_complexity' => 'no',
            'xss_parsing_level' => 'paranoid',
            'session_idle_timeout' => '30',       // per minute
            'session_remember_timeout' => '720',  // hours = 1 month
        ));

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.1.1', '<')) {
            // Registry keys
            $obfuscator = $this->gadget->GetRegistry('obfuscator');
            if ($obfuscator == 'HideEmail') {
                $this->gadget->SetRegistry('obfuscator', 'EmailEncoder');
            }

            $tables = array('complexcaptcha',
                            'mathcaptcha',
                            'simplecaptcha');
            foreach ($tables as $table) {
                $result = $GLOBALS['db']->dropTable($table);
                if (Jaws_Error::IsError($result)) {
                    // do nothing
                }
            }

            $this->gadget->DelRegistry('complex_captcha');
            $this->gadget->DelRegistry('math_captcha');
            $this->gadget->DelRegistry('simple_captcha');
            $this->gadget->DelRegistry('hkcaptcha');
        }

        if (version_compare($old, '0.1.2', '<')) {
            $this->gadget->AddRegistry('typepad_key', '');
        }

        if (version_compare($old, '0.1.3', '<')) {
            $old_captch = $this->gadget->GetRegistry('captcha');
            if ($old_captch !== 'DISABLED') {
                $this->gadget->SetRegistry('captcha', 'ANONYMOUS');
                $this->gadget->AddRegistry('captcha_driver', $old_captch);
            } else {
                $this->gadget->AddRegistry('captcha_driver', 'MathCaptcha');
            }

            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Policy/ManageEncryptionKey', 'false');
        }

        if (version_compare($old, '0.2.0', '<')) {
            $result = $this->gadget->installSchema('schema.xml', '', '0.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $this->gadget->AddRegistry('block_by_ip');
            $this->gadget->AddRegistry('block_by_agent');
            $this->gadget->AddRegistry('block_undefined_ip',    'false');
            $this->gadget->AddRegistry('block_undefined_agent', 'false');
        }

        return true;
    }

    /**
     * Get blocked IP range
     *
     * @access  public
     * @param   int     $id ID of the to-be-blocked IP range addresses
     * @return  array IP range info or Jaws_Error on failure
     */
    function GetIPRange($id)
    {
        $sql = '
            SELECT [id], [from_ip], [to_ip], [blocked]
            FROM [[policy_ipblock]]
            WHERE [id] = {id}';

        $params = array();
        $params['id'] = $id;

        $types = array('integer', 'integer', 'integer', 'boolean');
        $res = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        if (isset($res['id'])) {
            $res['from_ip'] = long2ip($res['from_ip']);
            $res['to_ip']   = long2ip($res['to_ip']);
        }

        return $res;
    }

    /**
     * Get blocked agent
     *
     * @access  public
     * @param   int $id ID of the agent
     * @return  string agent or Jaws_Error on failure
     */
    function GetAgent($id)
    {
        $sql = '
            SELECT [id], [agent], [blocked]
            FROM [[policy_agentblock]]
            WHERE [id] = {id}';

        $params = array();
        $params['id'] = $id;

        $types = array('integer', 'text', 'boolean');
        $res = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }

    /**
     * Returns total of blocked IPs
     *
     * @access  public
     * @return  DB resource
     */
    function GetTotalOfBlockedIPs()
    {
        $sql = 'SELECT COUNT([id]) as total FROM [[policy_ipblock]]';
        $rs  = $GLOBALS['db']->queryOne($sql);
        return $rs;
    }

    /**
     * Returns total of blocked Agents
     *
     * @access  public
     * @return  DB Resource
     */
    function GetTotalOfBlockedAgents()
    {
        $sql = 'SELECT COUNT([id]) as total FROM [[policy_agentblock]]';
        $rs  = $GLOBALS['db']->queryOne($sql);
        return $rs;
    }

    /**
     * Retrive all blocked IPs
     *
     * @access  public
     * @param   mixed   $limit  Limit of data to retrieve (false by default, returns all)
     * @return  array   An array contains all IP and info. and Jaws_Error on error
     */
    function GetBlockedIPs($limit = 0, $offset = null)
    {
        if (!empty($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        $sql = '
            SELECT
                [id], [from_ip], [to_ip], [blocked]
            FROM [[policy_ipblock]]
            ORDER BY [id] DESC';

        $types = array('integer', 'integer', 'integer', 'boolean');
        $rs = $GLOBALS['db']->queryAll($sql, null, $types);
        if (Jaws_Error::IsError($rs)) {
            return new Jaws_Error($rs->getMessage(), 'SQL');
        }
        
        return $rs;
    }

    /**
     * Retrieve all blocked Agents
     *
     * @access  public
     * @param   mixed   $limit  Limit of data to retrieve (false by default, returns all)
     * @return  array   An array contains all blocked Agents
     */
    function GetBlockedAgents($limit = 0, $offset = null)
    {
        if (!empty($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        $sql = '
            SELECT
                [id], [agent], [blocked]
            FROM [[policy_agentblock]]
            ORDER BY [id] DESC';

        $types = array('integer', 'text', 'boolean');
        $rs = $GLOBALS['db']->queryAll($sql, null, $types);
        if (Jaws_Error::IsError($rs)) {
            return new Jaws_Error($rs->getMessage(), 'SQL');
        }

        return $rs;
    }

    /**
     * Block a new IP range
     *
     * @access  public
     * @param   string  $ip the to be blocked IP address
     * @return  bool    True on success and Jaws_Error on errors
     */
    function AddIPRange($from_ip, $to_ip = null, $blocked = true)
    {
        $from_ip = ip2long($from_ip);
        if ($from_ip < 0) {
            $from_ip = $from_ip + 0xffffffff + 1;
        }

        if (empty($to_ip)) {
            $to_ip = $from_ip;
        } else {
            $to_ip = ip2long($to_ip);
            if ($to_ip < 0) $to_ip = $to_ip + 0xffffffff + 1;
        }

        $sql = '
            INSERT INTO [[policy_ipblock]]
                ([from_ip], [to_ip], [blocked])
            VALUES
                ({from_ip}, {to_ip}, {blocked})';

        $params = array();
        $params['from_ip'] = $from_ip;
        $params['to_ip']   = $to_ip;
        $params['blocked'] = (bool)$blocked;

        $rs = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($rs)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLICY_RESPONSE_IP_NOT_ADDED', 'AddIPRange'), _t('POLICY_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Edit blocked IP range
     *
     * @access  public
     * @param   int     $id ID of the to-be-blocked IP range addresses
     * @param   string  $from_ip  The to-be-blocked from IP
     * @param   string  $to_ip    The to-be-blocked to IP
     * @return  bool    True on success and Jaws_Error on errors
     */
    function EditIPRange($id, $from_ip, $to_ip = null, $blocked = true)
    {
        $from_ip = ip2long($from_ip);
        if ($from_ip < 0) {
            $from_ip = $from_ip + 0xffffffff + 1;
        }

        if (empty($to_ip)) {
            $to_ip = $from_ip;
        } else {
            $to_ip = ip2long($to_ip);
            if ($to_ip < 0) $to_ip = $to_ip + 0xffffffff + 1;
        }

        $sql = '
            UPDATE [[policy_ipblock]] SET
                [from_ip] = {from_ip},
                [to_ip]   = {to_ip},
                [blocked] = {blocked}
            WHERE [id] = {id}';

        $params = array();
        $params['id']      = $id;
        $params['from_ip'] = $from_ip;
        $params['to_ip']   = $to_ip;
        $params['blocked'] = (bool)$blocked;

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLICY_RESPONSE_IP_NOT_DELETED', 'EditIPRange'), _t('POLICY_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_EDITED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Unblock an IP range
     *
     * @access  public
     * @param   int $id ID of the to be unblocked IP Band
     * @return  bool    True on successfull attempts and Jaws Error otherwise
     */
    function DeleteIPRange($id)
    {
        $sql = 'DELETE FROM [[policy_ipblock]] WHERE [id] = {id}';
        $rs  = $GLOBALS['db']->query($sql, array('id' => (int)$id));
        if (Jaws_Error::IsError($rs)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error($rs->getMessage(), 'SQL');
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Block a new Agent
     *
     * @access  public
     * @param   string  The to-be-blocked Agent string
     * @return  True on success and Jaws error on failures
     */
    function AddAgent($agent, $blocked = true)
    {
        $params = array();
        $params['agent']   = $agent;
        $params['blocked'] = (bool)$blocked;

        $sql = '
            INSERT INTO [[policy_agentblock]]
                ([agent], [blocked])
            VALUES
                ({agent}, {blocked})';

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_NOT_ADDEDD'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLICY_RESPONSE_AGENT_NOT_ADDEDD', 'AddAgent'), _t('POLICY_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Edit Blocked Agent
     *
     * @access  public
     * @param   int     $id     ID of the agent
     * @param   string  $agent  The to-be-blocked Agent string
     * @return  True on success and Jaws error on failures
     */
    function EditAgent($id, $agent, $blocked = true)
    {
        $params = array();
        $params['id']      = (int)$id;
        $params['agent']   = $agent;
        $params['blocked'] = (bool)$blocked;

        $sql = '
            UPDATE [[policy_agentblock]] SET
                [agent]   = {agent},
                [blocked] = {blocked}
            WHERE [id] = {id}';

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_NOT_EDITED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLICY_RESPONSE_AGENT_NOT_EDITED', 'EditAgent'), _t('POLICY_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_EDITED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Unblock an Agent
     *
     * @access  public
     * @param   int $id ID of the-to-be-unblocked-agent
     * @return  bool    true on success and Jaws error on failure
     */
    function DeleteAgent($id)
    {
        $sql = 'DELETE FROM [[policy_agentblock]] WHERE [id] = {id}';
        $rs  = $GLOBALS['db']->query($sql, array('id' => (int)$id));
        if (Jaws_Error::IsError($rs)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error($rs->getMessage(), 'SQL');
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Set IPBlocking block undefined ip
     *
     * @access  public
     * @param   bool    $blocked    blocked by default
     * @return  bool    True on success and Jaws error on failure
     */
    function IPBlockingBlockUndefined($blocked)
    {
        $res = $this->gadget->SetRegistry('block_undefined_ip',
                                              $blocked? 'true' : 'false');
        return $res;
    }

    /**
     * Set AgentBlocking block undefined agent
     *
     * @access  public
     * @param   bool    $blocked    blocked by default
     * @return  bool    True on success and Jaws error on failure
     */
    function AgentBlockingBlockUndefined($blocked)
    {
        $res = $this->gadget->SetRegistry('block_undefined_agent', $blocked? 'true' : 'false');
        return $res;
    }

    /**
     * Update  Encryption Settings
     *
     * @access  public
     * @param   bool    $enabled   Enable/Disable encryption
     * @param   bool    $key_age   Key age
     * @param   bool    $key_len   Key length
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateEncryptionSettings($enabled, $key_age, $key_len)
    {
        $this->gadget->SetRegistry('crypt_enabled', ($enabled? 'true' : 'false'));
        if ($GLOBALS['app']->Session->GetPermission('Policy', 'ManageEncryptionKey')) {
            $this->gadget->SetRegistry('crypt_key_age', (int)$key_age);
            if ($this->gadget->GetRegistry('crypt_key_len') != $key_len) {
                $this->gadget->SetRegistry('crypt_key_len', (int)$key_len);
                $this->gadget->SetRegistry('crypt_key_start_date', 0);
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_ENCRYPTION_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update  AntiSpam Settings
     *
     * @access  public
     * @param   bool    $allow_duplicate
     * @param   bool    $filter
     * @param   string  $captcha
     * @param   string  $captcha_driver
     * @param   bool    $obfuscator
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAntiSpamSettings($allow_duplicate, $filter, $captcha, $captcha_driver, $obfuscator)
    {
        $this->gadget->SetRegistry('allow_duplicate', $allow_duplicate);
        $this->gadget->SetRegistry('filter',          $filter);
        $this->gadget->SetRegistry('captcha',         $captcha);
        $this->gadget->SetRegistry('captcha_driver',  $captcha_driver);
        $this->gadget->SetRegistry('obfuscator',      $obfuscator);
        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_ANTISPAM_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update Advanced Policies
     *
     * @access  public
     * @param   string  $passwd_complexity
     * @param   int     $passwd_bad_count
     * @param   int     $passwd_lockedout_time
     * @param   int     $passwd_max_age
     * @param   int     $passwd_min_length
     * @param   string  $xss_parsing_level
     * @param   int     $session_idle_timeout
     * @param   int     $session_remember_timeout
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAdvancedPolicies($passwd_complexity, $passwd_bad_count, $passwd_lockedout_time,
                                    $passwd_max_age, $passwd_min_length, $xss_parsing_level,
                                    $session_idle_timeout, $session_remember_timeout)
    {
        $this->gadget->SetRegistry('passwd_complexity',     ($passwd_complexity=='yes')? 'yes' : 'no');
        $this->gadget->SetRegistry('passwd_bad_count',      (int)$passwd_bad_count);
        $this->gadget->SetRegistry('passwd_lockedout_time', (int)$passwd_lockedout_time);
        $this->gadget->SetRegistry('passwd_max_age',        (int)$passwd_max_age);
        $this->gadget->SetRegistry('passwd_min_length',     (int)$passwd_min_length);
        $this->gadget->SetRegistry('xss_parsing_level',     ($xss_parsing_level=='paranoid')? 'paranoid' : 'normal');
        $this->gadget->SetRegistry('session_idle_timeout',     (int)$session_idle_timeout);
        $this->gadget->SetRegistry('session_remember_timeout', (int)$session_remember_timeout);
        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_ADVANCED_POLICIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Get filters
     *
     * @access  public
     * @return  array Array with the filters names.
     */
    function GetFilters()
    {
        $result = array();
        $path = JAWS_PATH . 'gadgets/Policy/filters/';
        $adr = scandir($path);
        foreach ($adr as $file) {
            if (substr($file, -4) == '.php') {
                $result[$file] = substr($file, 0, -4);
            }
        }
        sort($result);
        return $result;
    }

    /**
     * Get captchas
     *
     * @access  public
     * @return  array Array with the captchas names.
     */
    function GetCaptchas()
    {
        $result = array();
        $path = JAWS_PATH . 'gadgets/Policy/captchas/';
        $adr = scandir($path);
        foreach ($adr as $file) {
            if (substr($file, -4) == '.php') {
                $result[$file] = substr($file, 0, -4);
            }
        }
        sort($result);
        return $result;
    }

    /**
     * Get filters
     *
     * @access  public
     * @return  array Array with the obfuscators names.
     */
    function GetObfuscators()
    {
        $result = array();
        $path = JAWS_PATH . 'gadgets/Policy/obfuscators/';
        $adr = scandir($path);
        foreach ($adr as $file) {
            if (substr($file, -4) == '.php') {
                $result[$file] = substr($file, 0, -4);
            }
        }
        sort($result);
        return $result;
    }

    /**
     * Submit spam
     *
     * @access  public
     * @param   string  $permalink
     * @param   string  $type
     * @param   string  $author
     * @param   string  $author_email
     * @param   string  $author_url
     * @param   string  $content
     * @return  void
     */
    function SubmitSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        $filter = preg_replace('/[^[:alnum:]_-]/', '', $this->gadget->GetRegistry('filter'));
        if ($filter == 'DISABLED' || !@include_once(JAWS_PATH . "gadgets/Policy/filters/$filter.php"))
        {
            return false;
        }

        static $objFilter;
        if (!isset($objFilter)) {
            $objFilter = new $filter();
        }

        $objFilter->SubmitSpam($permalink, $type, $author, $author_email, $author_url, $content);
    }

    /**
     * Submit ham
     *
     * @access  public
     * @param   string  $permalink
     * @param   string  $type
     * @param   string  $author
     * @param   string  $author_email
     * @param   string  $author_url
     * @param   string  $content
     * @return  void
     */
    function SubmitHam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        $filter = preg_replace('/[^[:alnum:]_-]/', '', $this->gadget->GetRegistry('filter'));
        if ($filter == 'DISABLED' || !@include_once(JAWS_PATH . "gadgets/Policy/filters/$filter.php"))
        {
            return false;
        }

        static $objFilter;
        if (!isset($objFilter)) {
            $objFilter = new $filter();
        }

        $objFilter->SubmitHam($permalink, $type, $author, $author_email, $author_url, $content);
    }

}