<?php
/**
 * Policy Gadget
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model_Agent extends Jaws_Gadget_Model
{

    /**
     * Checks the Agent is blocked or not
     *
     * @access  public
     * @param   string  $agent  Agent
     * @return  bool    True if the Agent is blocked
     */
    function IsAgentBlocked($agent)
    {
        $table = Jaws_ORM::getInstance()->table('policy_agentblock');
        $table->select('blocked:boolean');
        $table->where('agent', Jaws_XSS::filter($agent));
        $blocked = $table->fetchOne();
        if (!Jaws_Error::IsError($blocked) && !is_null($blocked)) {
            return $blocked;
        }

        return $this->gadget->registry->fetch('block_undefined_agent') == 'true';
    }

}