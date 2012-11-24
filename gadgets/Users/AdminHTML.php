<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UsersAdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default admin action
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Admin()
    {
        if ($this->GetPermission('ManageUsers')) {
            $userHTML = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Users');
            return $userHTML->Users();
        } elseif ($this->GetPermission('ManageGroups')) {
            $groupHTML = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Groups');
            return $groupHTML->Groups();
        } elseif ($this->GetPermission('ManageOnlineUsers')) {
            $onlineHTML = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'OnlineUsers');
            return $onlineHTML->OnlineUsers();
        }

        $this->CheckPermission('ManageProperties');
        $propHTML = $GLOBALS['app']->LoadGadget('Users', 'AdminHTML', 'Properties');
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

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->GetPermission('ManageUsers')) {
            $menubar->AddOption('Users',
                                _t('USERS_NAME'),
                                BASE_SCRIPT . '?gadget=Users&amp;action=Users',
                                'gadgets/Users/images/users_mini.png');
        }
        if ($this->GetPermission('ManageGroups')) {
            $menubar->AddOption('Groups',
                                _t('USERS_GROUPS_GROUPS'),
                                BASE_SCRIPT . '?gadget=Users&amp;action=Groups',
                                'gadgets/Users/images/groups_mini.png');
        }
        if ($this->GetPermission('ManageOnlineUsers')) {
            $menubar->AddOption('OnlineUsers',
                _t('USERS_ONLINE_USERS'),
                BASE_SCRIPT . '?gadget=Users&amp;action=OnlineUsers',
                STOCK_PREFERENCES);
        }
        if ($this->GetPermission('ManageProperties')) {
            $menubar->AddOption('Properties',
                                _t('GLOBAL_PROPERTIES'),
                                BASE_SCRIPT . '?gadget=Users&amp;action=Properties',
                                STOCK_PREFERENCES);
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}