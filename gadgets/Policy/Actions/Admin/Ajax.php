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
class Policy_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Get blocked IP range
     *
     * @access  public
     * @return  array   IP range info
     */
    function GetIPRange()
    {
        $this->gadget->CheckPermission('ManageIPs');
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('IP');
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
     * @return  string  Response
     */
    function AddIPRange()
    {
        $this->gadget->CheckPermission('ManageIPs');
        @list($from_ip, $to_ip, $blocked) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('IP');
        $model->AddIPRange($from_ip, $to_ip, $blocked);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Edit blocked an IP range
     *
     * @access  public
     * @return  string  Response
     */
    function EditIPRange()
    {
        $this->gadget->CheckPermission('ManageIPs');
        @list($id, $from_ip, $to_ip, $blocked) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('IP');
        $model->EditIPRange($id, $from_ip, $to_ip, $blocked);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an IP range
     * 
     * @access  public
     * @return  string  Response
     */
    function DeleteIPRange()
    {
        $this->gadget->CheckPermission('ManageIPs');
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('IP');
        $model->DeleteIPRange($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get blocked agent
     *
     * @access  public
     * @return  string Agent
     */
    function GetAgent()
    {
        $this->gadget->CheckPermission('ManageAgents');
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Agent');
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
     * @return  string  Response
     */
    function AddAgent()
    {
        $this->gadget->CheckPermission('ManageAgents');
        @list($agent, $blocked) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Agent');
        $model->AddAgent($agent, $blocked);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Block an agent
     *
     * @access  public
     * @return  string  Response
     */
    function EditAgent()
    {
        $this->gadget->CheckPermission('ManageAgents');
        @list($id, $agent, $blocked) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Agent');
        $model->EditAgent($id, $agent, $blocked);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Unblock an agent
     *
     * @access  public
     * @return  string  Response
     */
    function DeleteAgent()
    {
        $this->gadget->CheckPermission('ManageAgents');
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Agent');
        $model->DeleteAgent($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Set IPBlocking block undefined ip
     *
     * @access  public
     * @return  bool    True on success and Jaws error on failure
     */
    function IPBlockingBlockUndefined()
    {
        $this->gadget->CheckPermission('ManageIPs');
        @list($blocked) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('IP');
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
     * @return  bool    True on success and Jaws error on failure
     */
    function AgentBlockingBlockUndefined()
    {
        $this->gadget->CheckPermission('ManageAgents');
        @list($blocked) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Agent');
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
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateEncryptionSettings()
    {
        $this->gadget->CheckPermission('Encryption');
        @list($enabled, $key_age, $key_len) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Encryption');
        $model->UpdateEncryptionSettings($enabled == 'true', $key_age, $key_len);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update AntiSpam Settings
     *
     * @access  public
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAntiSpamSettings()
    {
        $this->gadget->CheckPermission('AntiSpam');
        @list($filter, $default_captcha, $default_captcha_driver, $obfuscator) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('AntiSpam');
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
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAdvancedPolicies()
    {
        $this->gadget->CheckPermission('AdvancedPolicies');
        @list($passwd_complexity, $passwd_bad_count, $passwd_lockedout_time,
            $passwd_max_age, $passwd_min_length, $login_captcha, $login_captcha_driver,
            $xss_parsing_level, $session_idle_timeout, $session_remember_timeout
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('AdvancedPolicies');
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
     * @return  array   An array of IP/Agents
     */
    function getData()
    {
        @list($offset, $grid) = jaws()->request->fetchAll('post');
        $ipHTML = $this->gadget->action->loadAdmin('IP');
        $agentHTML = $this->gadget->action->loadAdmin('Agent');
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