<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
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

        $this->gadget->CheckPermission('ManageSettings');
        $propHTML = $this->gadget->action->loadAdmin('Settings');
        return $propHTML->Settings();
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
        $actions = array('Users', 'Groups', 'ACLs', 'OnlineUsers', 'Settings');
        if (!in_array($action, $actions)) {
            $action = 'Users';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageUsers')) {
            $menubar->AddOption(
                'Users',
                _t('USERS_TITLE'),
                $this->gadget->url('Users'),
                'gadgets/Users/Resources/images/users_mini.png'
            );
        }
        if ($this->gadget->GetPermission('ManageGroups')) {
            $menubar->AddOption(
                'Groups',
                _t('USERS_GROUPS_GROUPS'),
                $this->gadget->url('Groups'),
                'gadgets/Users/Resources/images/groups_mini.png'
            );
        }
        $menubar->AddOption(
            'ACLs',
            _t('USERS_ACLS'),
            $this->gadget->url('ACLs'),
            'gadgets/Users/Resources/images/acls.png'
        );

        if ($this->gadget->GetPermission('ManageOnlineUsers')) {
            $menubar->AddOption(
                'OnlineUsers',
                _t('USERS_ONLINE_USERS'),
                $this->gadget->url('OnlineUsers'),
                STOCK_PREFERENCES
            );
        }
        if ($this->gadget->GetPermission('ManageSettings')) {
            $menubar->AddOption(
                'Settings',
                _t('GLOBAL_SETTINGS'),
                $this->gadget->url('Settings'),
                STOCK_PREFERENCES
            );
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}