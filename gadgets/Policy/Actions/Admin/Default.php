<?php
/**
 * Policy Admin Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Display the sidebar
     *
     * @access  public
     * @param   string  $action Selected Action
     * @return  XHTML template content
     */
    function SideBar($action)
    {
        $actions = array('Zones', 'ZoneActions', 'IPBlocking', 'AgentBlocking', 'Encryption', 'AntiSpam',
                         'AdvancedPolicies');
        if (!in_array($action, $actions)) {
            $action = 'IPBlocking';
        }

        $sidebar = new Jaws_Widgets_Sidebar('policy');
        if ($this->gadget->GetPermission('ManageZones')) {
            $sidebar->AddOption('Zones', $this::t('ZONES'),
                BASE_SCRIPT . '?reqGadget=Policy&amp;reqAction=Zones',
                'images/stock/stop.png');
        }
        if ($this->gadget->GetPermission('ManageZoneActions')) {
            $sidebar->AddOption('ZoneActions', $this::t('ZONE_ACTIONS'),
                BASE_SCRIPT . '?reqGadget=Policy&amp;reqAction=ZoneActions',
                'images/stock/stop.png');
        }
        if ($this->gadget->GetPermission('IPBlocking')) {
            $sidebar->AddOption('IPBlocking', $this::t('IP_BLOCKING'),
                                BASE_SCRIPT . '?reqGadget=Policy&amp;reqAction=IPBlocking',
                                'images/stock/stop.png');
        }
        if ($this->gadget->GetPermission('AgentBlocking')) {
            $sidebar->AddOption('AgentBlocking', $this::t('AGENT_BLOCKING'),
                                BASE_SCRIPT . '?reqGadget=Policy&amp;reqAction=AgentBlocking',
                                'images/stock/stop.png');
        }
        if ($this->gadget->GetPermission('Encryption')) {
            $sidebar->AddOption('Encryption', $this::t('ENCRYPTION'),
                                BASE_SCRIPT . '?reqGadget=Policy&amp;reqAction=Encryption',
                                'gadgets/Policy/Resources/images/encryption.png');
        }
        if ($this->gadget->GetPermission('AntiSpam')) {
            $sidebar->AddOption('AntiSpam', $this::t('ANTISPAM'),
                                BASE_SCRIPT . '?reqGadget=Policy&amp;reqAction=AntiSpam',
                                'gadgets/Policy/Resources/images/antispam.png');
        }
        if ($this->gadget->GetPermission('AdvancedPolicies')) {
            $sidebar->AddOption('AdvancedPolicies', $this::t('ADVANCED_POLICIES'),
                                BASE_SCRIPT . '?reqGadget=Policy&amp;reqAction=AdvancedPolicies',
                                'gadgets/Policy/Resources/images/policies.png');
        }

        $sidebar->Activate($action);
        return $sidebar->Get();
    }

}