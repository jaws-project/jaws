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
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Policy_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

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
        $IPRange = $this->_Model->GetIPRange($id);
        if (Jaws_Error::IsError($IPRange)) {
            return false; //we need to handle errors on ajax
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
        $this->_Model->AddIPRange($from_ip, $to_ip, $blocked);
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
        $this->_Model->EditIPRange($id, $from_ip, $to_ip, $blocked);
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
        $this->_Model->DeleteIPRange($id);
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
        $agent = $this->_Model->GetAgent($id);
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
        $this->_Model->AddAgent($agent, $blocked);
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
        $this->_Model->EditAgent($id, $agent, $blocked);
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
        $this->_Model->DeleteAgent($id);
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
        $res = $this->_Model->IPBlockingBlockUndefined($blocked);
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
        $res = $this->_Model->AgentBlockingBlockUndefined($blocked);
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
        $this->_Model->UpdateEncryptionSettings($enabled == 'true', $key_age, $key_len);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update AntiSpam Settings
     *
     * @access  public
     * @param   bool    $allow_duplicate
     * @param   bool    $filter
     * @param   string  $default_captcha
     * @param   string  $default_captcha_driver
     * @param   bool    $obfuscator
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAntiSpamSettings($allow_duplicate, $filter, $default_captcha,
                                    $default_captcha_driver, $obfuscator)
    {
        $this->gadget->CheckPermission('AntiSpam');
        $this->_Model->UpdateAntiSpamSettings(
            $allow_duplicate,
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
        $this->_Model->UpdateAdvancedPolicies(
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
        $gadget = $GLOBALS['app']->LoadGadget('Policy', 'AdminHTML');
        if (!is_numeric($offset)) {
            $offset = null;
        }

        $dgData = '';
        switch ($grid) {
        case 'blocked_agents_datagrid':
            $dgData = $gadget->GetBlockedAgents($offset);
            break;
        case 'blocked_ips_datagrid':
            $dgData = $gadget->GetBlockedIPRanges($offset);
            break;
        default:
            break;
        }

        return $dgData;
    }

}