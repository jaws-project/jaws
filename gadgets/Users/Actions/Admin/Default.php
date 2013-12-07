<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Calls default admin action
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('ManageUsers')) {
            $userHTML = $this->gadget->action->loadAdmin('Users');
            return $userHTML->Users();
        } elseif ($this->gadget->GetPermission('ManageGroups')) {
            $groupHTML = $this->gadget->action->loadAdmin('Groups');
            return $groupHTML->Groups();
        } elseif ($this->gadget->GetPermission('ManageOnlineUsers')) {
            $onlineHTML = $this->gadget->action->loadAdmin('OnlineUsers');
            return $onlineHTML->OnlineUsers();
        }

        $this->gadget->CheckPermission('ManageProperties');
        $propHTML = $this->gadget->action->loadAdmin('Properties');
        return $propHTML->Properties();
    }

    /**
     * Builds the users menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML menubar
     */
    function MenuBar($action)
    {
        $actions = array('Users', 'Groups', 'OnlineUsers', 'Properties');
        if (!in_array($action, $actions)) {
            $action = 'Users';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageUsers')) {
            $menubar->AddOption('Users',
                                $this->gadget->name,
                                BASE_SCRIPT . '?gadget=Users&amp;action=Users',
                                'gadgets/Users/Resources/images/users_mini.png');
        }
        if ($this->gadget->GetPermission('ManageGroups')) {
            $menubar->AddOption('Groups',
                                _t('USERS_GROUPS_GROUPS'),
                                BASE_SCRIPT . '?gadget=Users&amp;action=Groups',
                                'gadgets/Users/Resources/images/groups_mini.png');
        }
        if ($this->gadget->GetPermission('ManageOnlineUsers')) {
            $menubar->AddOption('OnlineUsers',
                _t('USERS_ONLINE_USERS'),
                BASE_SCRIPT . '?gadget=Users&amp;action=OnlineUsers',
                STOCK_PREFERENCES);
        }
        if ($this->gadget->GetPermission('ManageProperties')) {
            $menubar->AddOption('Properties',
                                _t('GLOBAL_PROPERTIES'),
                                BASE_SCRIPT . '?gadget=Users&amp;action=Properties',
                                STOCK_PREFERENCES);
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}