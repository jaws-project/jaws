<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Prepares the poll menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Polls', 'PollGroups', 'Reports');
        if (!in_array($action, $actions)) {
            $action = 'Polls';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManagePolls')) {
            $menubar->AddOption('Polls', $this::t('POLLS'),
                                BASE_SCRIPT . '?reqGadget=Poll&amp;reqAction=Polls', 'gadgets/Poll/Resources/images/polls_mini.png');
        }
        if ($this->gadget->GetPermission('ManageGroups')) {
            $menubar->AddOption('PollGroups', $this::t('GROUPS'),
                                BASE_SCRIPT . '?reqGadget=Poll&amp;reqAction=PollGroups', 'gadgets/Poll/Resources/images/groups_mini.png');
        }
        if ($this->gadget->GetPermission('ViewReports')) {
            $menubar->AddOption('Reports', $this::t('REPORTS'),
                                BASE_SCRIPT . '?reqGadget=Poll&amp;reqAction=Reports', 'gadgets/Poll/Resources/images/reports_mini.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }
}