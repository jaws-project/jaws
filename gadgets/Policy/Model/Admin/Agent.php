<?php
/**
 * Policy Gadget Admin
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model_Admin_Agent extends Jaws_Gadget_Model
{
    /**
     * Get blocked agent
     *
     * @access  public
     * @param   int $id ID of the agent
     * @return  string agent or Jaws_Error on failure
     */
    function GetAgent($id)
    {
        $table = Jaws_ORM::getInstance()->table('policy_agentblock');
        $table->select('id', 'agent', 'script', 'blocked:boolean');
        return $table->where('id', (int)$id)->fetchRow();
    }

    /**
     * Returns total of blocked Agents
     *
     * @access  public
     * @return  DB Resource
     */
    function GetTotalOfBlockedAgents()
    {
        $table = Jaws_ORM::getInstance()->table('policy_agentblock');
        $table->select('COUNT(id)');
        return $table->fetchOne();
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
        $table = Jaws_ORM::getInstance()->table('policy_agentblock');
        $table->select('id', 'agent', 'script', 'blocked:boolean');
        $table->limit($limit, $offset);
        $table->orderBy('id desc');
        return $table->fetchAll();
    }

    /**
     * Block a new Agent
     *
     * @access  public
     * @param   string  The to-be-blocked Agent string
     * @return  True on success and Jaws error on failures
     */
    function AddAgent($agent, $script = 'index', $blocked = true)
    {
        $data = array();
        $data['agent']   = $agent;
        $data['script']  = $script;
        $data['blocked'] = (bool)$blocked;

        $table = Jaws_ORM::getInstance()->table('policy_agentblock');
        $res = $table->insert($data)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_NOT_ADDEDD'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLICY_RESPONSE_AGENT_NOT_ADDEDD', 'AddAgent'));
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
    function EditAgent($id, $agent, $script = 'index', $blocked = true)
    {
        $data = array();
        $data['agent']   = $agent;
        $data['script']  = $script;
        $data['blocked'] = (bool)$blocked;

        $table = Jaws_ORM::getInstance()->table('policy_agentblock');
        $res = $table->update($data)->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_NOT_EDITED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLICY_RESPONSE_AGENT_NOT_EDITED', 'EditAgent'));
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
        $table = Jaws_ORM::getInstance()->table('policy_agentblock');
        $res = $table->delete()->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage());
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_AGENT_DELETED'), RESPONSE_NOTICE);
        return true;
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
        $res = $this->gadget->registry->update('block_undefined_agent', $blocked? 'true' : 'false');
        return $res;
    }
}