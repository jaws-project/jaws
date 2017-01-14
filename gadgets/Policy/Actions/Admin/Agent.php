<?php
/**
 * Policy Admin Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Actions_Admin_Agent extends Policy_Actions_Admin_Default
{
    /**
     * Returns an array with all the blocked agents
     *
     * @access  public
     * @param   int     $offset  offset of data needed
     * @return  array   Array of blocked agents
     */
    function GetBlockedAgents($offset = 0)
    {
        $model  = $this->gadget->model->loadAdmin('Agent');
        $agents = $model->GetBlockedAgents(12, $offset);
        if (Jaws_Error::IsError($agents)) {
            return array();
        }

        $newData = array();
        foreach ($agents as $agent) {
            $agentData = array();
            $agentData['agent'] = $agent['agent'];

            $actions = '';
            if ($this->gadget->GetPermission('ManageAgents')) {
                $ipWidget =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                    "javascript:editAgent(this, '".$agent['id']."');",
                    STOCK_EDIT);
                $actions.= $ipWidget->Get().'&nbsp;';

                $agWidget =& Piwi::CreateWidget('Link',
                    _t('GLOBAL_DELETE' ,_t('POLICY_AGENT')),
                    "javascript:deleteAgent(this, '".$agent['id']."');",
                    STOCK_DELETE);
                $actions .= $agWidget->Get();
            }
            $agentData['actions'] = $actions;
            $newData[] = $agentData;
        }

        return $newData;
    }

    /**
     * Returns the Blocked Agents datagrid
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AgentsDatagrid()
    {
        $model = $this->gadget->model->loadAdmin('Agent');
        $totalAgents = $model->GetTotalOfBlockedAgents();

        $grid =& Piwi::CreateWidget('DataGrid', array(), null);
        $grid->SetID('blocked_agents_datagrid');
        $grid->TotalRows($totalAgents);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('POLICY_AGENT'));
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'));
        $column2->SetStyle('width: 60px; white-space:nowrap;');
        $grid->AddColumn($column2);

        return $grid->Get();
    }

    /**
     * AgentBlokcing action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function AgentBlocking()
    {
        $this->gadget->CheckPermission('AgentBlocking');
        $this->AjaxMe('script.js');
        $this->gadget->layout->setVariable('incompleteFields',   _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'));
        $this->gadget->layout->setVariable('confirmAgentDelete', _t('POLICY_RESPONSE_CONFIRM_DELETE_AGENT'));

        $tpl = $this->gadget->template->loadAdmin('AgentBlocking.html');
        $tpl->SetBlock('agentblocking');

        // Sidebar
        $tpl->SetVariable('sidebar', $this->SideBar('AgentBlocking'));
        $tpl->SetVariable('blocked_agents_datagrid', $this->AgentsDatagrid());

        $default = $this->gadget->registry->fetch('block_undefined_agent') == 'true';
        $blockUndefined =& Piwi::CreateWidget('CheckButtons', 'agentblocking');
        $blockUndefined->AddOption(_t('POLICY_AGENT_BLOCK_UNDEFINED'),
            'true',
            'block_undefined_agent',
            $default);
        $blockUndefined->AddEvent(ON_CLICK, 'javascript:setBlockUndefinedAgent();');
        $tpl->SetVariable('enabled_option', $blockUndefined->Get());

        $tpl->SetVariable('legend_title', _t('POLICY_AGENT'));
        $agentEntry =& Piwi::CreateWidget('Entry', 'agent', '');
        $agentEntry->setSize(24);
        $tpl->SetVariable('lbl_agent', _t('POLICY_AGENT'));
        $tpl->SetVariable('agent', $agentEntry->Get());

        $blocked =& Piwi::CreateWidget('Combo', 'blocked');
        $blocked->SetID('blocked');
        $blocked->setStyle('width: 120px;');
        $blocked->AddOption(_t('GLOBAL_NO'),  0);
        $blocked->AddOption(_t('GLOBAL_YES'), 1);
        $blocked->SetDefault('1');
        $tpl->SetVariable('lbl_blocked', _t('POLICY_BLOCKED'));
        $tpl->SetVariable('blocked', $blocked->Get());

        if ($this->gadget->GetPermission('ManageAgents')) {
            $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, 'javascript:saveAgent();');
            $tpl->SetVariable('btn_save', $btnSave->Get());

            $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
            $btnCancel->AddEvent(ON_CLICK, 'javascript:stopAction();');
            $tpl->SetVariable('btn_cancel', $btnCancel->Get());
        }

        $tpl->ParseBlock('agentblocking');

        return $tpl->Get();
    }
}