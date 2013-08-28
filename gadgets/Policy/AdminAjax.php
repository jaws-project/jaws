<?php
/**
 * Policy Ajax API
 *
 * @category   Ajax
 * @package    Policy 
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Get blocked IP range
     *
     * @access  public
     * @param   int     $id ID of IP range addresses
     * @return  array   IP range info
     */
    function GetIPRange($id)
    {
        $this->gadget->CheckPermission('ManageIPs');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'IP');
        $IPRange = $model->GetIPRange($id);
        if (Jaws_Error::IsError($IPRange)) {
            return false; //we need to handle errors on ajax
        }

        if (isset($IPRange['id'])) {
            $IPRange['from_ip'] = long2ip($IPRange['from_ip']);
            $IPRange['to_ip']   = long2ip($IPRange['to_ip']);
        }

        return $IPRange;
    }

    /**
     * Block an IP range
     *
     * @access  public
     * @param   string  $from_ip  The from IP
     * @param   string  $to_ip    The to IP
     * @param   bool    $blocked
     * @return  string  Response
     */
    function AddIPRange($from_ip, $to_ip = null, $blocked)
    {
        $this->gadget->CheckPermission('ManageIPs');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'IP');
        $model->AddIPRange($from_ip, $to_ip, $blocked);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Edit blocked an IP range
     *
     * @access  public
     * @param   int     $id ID of the to-be-blocked IP range addresses
     * @param   string  $from_ip  The from IP
     * @param   string  $to_ip    The to IP
     * @param   bool    $blocked
     * @return  string  Response
     */
    function EditIPRange($id, $from_ip, $to_ip, $blocked)
    {
        $this->gadget->CheckPermission('ManageIPs');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'IP');
        $model->EditIPRange($id, $from_ip, $to_ip, $blocked);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an IP range
     * 
     * @access  public
     * @param   int $id ID of the-to-be-unblocked IP range addresses
     * @return  string  Response
     */
    function DeleteIPRange($id)
    {
        $this->gadget->CheckPermission('ManageIPs');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'IP');
        $model->DeleteIPRange($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get blocked agent
     *
     * @access  public
     * @param   int $id ID of the agent
     * @return  string Agent
     */
    function GetAgent($id)
    {
        $this->gadget->CheckPermission('ManageAgents');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'Agent');
        $agent = $model->GetAgent($id);
        if (Jaws_Error::IsError($agent)) {
            return false; //we need to handle errors on ajax
        }

        return $agent;
    }

    /**
     * Block an agent
     *
     * @access  public
     * @param   string  $agent   Which Agent is supposed to be blocked or allowed
     * @param   bool    $blocked
     * @return  string  Response
     */
    function AddAgent($agent, $blocked)
    {
        $this->gadget->CheckPermission('ManageAgents');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'Agent');
        $model->AddAgent($agent, $blocked);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Block an agent
     *
     * @access  public
     * @param   int     $id     ID of the agent
     * @param   string  $agent  Which Agent is supposed to be blocked or allowed
     * @param   bool    $blocked
     * @return  string  Response
     */
    function EditAgent($id, $agent, $blocked)
    {
        $this->gadget->CheckPermission('ManageAgents');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'Agent');
        $model->EditAgent($id, $agent, $blocked);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Unblock an agent
     *
     * @access  public
     * @param   int $id ID of the agent
     * @return  string  Response
     */
    function DeleteAgent($id)
    {
        $this->gadget->CheckPermission('ManageAgents');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'Agent');
        $model->DeleteAgent($id);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        $this->gadget->CheckPermission('ManageIPs');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'IP');
        $res = $model->IPBlockingBlockUndefined($blocked);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
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
        $this->gadget->CheckPermission('ManageAgents');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'Agent');
        $res = $model->AgentBlockingBlockUndefined($blocked);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
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
        $this->gadget->CheckPermission('Encryption');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'Encryption');
        $model->UpdateEncryptionSettings($enabled == 'true', $key_age, $key_len);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update AntiSpam Settings
     *
     * @access  public
     * @param   bool    $filter
     * @param   string  $default_captcha
     * @param   string  $default_captcha_driver
     * @param   bool    $obfuscator
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAntiSpamSettings($filter, $default_captcha,
                                    $default_captcha_driver, $obfuscator)
    {
        $this->gadget->CheckPermission('AntiSpam');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'AntiSpam');
        $model->UpdateAntiSpamSettings(
            $filter,
            $default_captcha,
            $default_captcha_driver,
            $obfuscator
        );
        return $GLOBALS['app']->Session->PopLastResponse();
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
     * @param   string  $login_captcha
     * @param   string  $login_captcha_driver
     * @param   string  $xss_parsing_level
     * @param   int     $session_idle_timeout
     * @param   int     $session_remember_timeout
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAdvancedPolicies($passwd_complexity, $passwd_bad_count, $passwd_lockedout_time,
                                    $passwd_max_age, $passwd_min_length, $login_captcha, $login_captcha_driver,
                                    $xss_parsing_level, $session_idle_timeout, $session_remember_timeout)
    {
        $this->gadget->CheckPermission('AdvancedPolicies');
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel', 'AdvancedPolicies');
        $model->UpdateAdvancedPolicies(
            $passwd_complexity, $passwd_bad_count, $passwd_lockedout_time,
            $passwd_max_age, $passwd_min_length, $login_captcha, $login_captcha_driver,
            $xss_parsing_level, $session_idle_timeout, $session_remember_timeout
        );
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Rebuild the datagrid
     *
     * @access  public
     */
    function GetData($offset, $grid)
    {
        $ipHTML = $GLOBALS['app']->LoadGadget('Policy', 'AdminHTML', 'IP');
        $agentHTML = $GLOBALS['app']->LoadGadget('Policy', 'AdminHTML', 'Agent');
        if (!is_numeric($offset)) {
            $offset = null;
        }

        $dgData = '';
        switch ($grid) {
        case 'blocked_agents_datagrid':
            $dgData = $agentHTML->GetBlockedAgents($offset);
            break;
        case 'blocked_ips_datagrid':
            $dgData = $ipHTML->GetBlockedIPRanges($offset);
            break;
        default:
            break;
        }

        return $dgData;
    }

}