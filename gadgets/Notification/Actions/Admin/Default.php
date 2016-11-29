<?php
/**
 * Notification Core Gadget
 *
 * @category    Gadget
 * @package     Notification
 */
class Notification_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Prepares the comments menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('NotificationDrivers', 'Settings');
        if (!in_array($action, $actions)) {
            $action = 'NotificationDrivers';
        }

        $menubar = new Jaws_Widgets_Menubar();

        if ($this->gadget->GetPermission('NotificationDrivers')) {
            $menubar->AddOption('NotificationDrivers',
                _t('NOTIFICATION_DRIVERS'),
                BASE_SCRIPT . '?gadget=Notification&amp;action=NotificationDrivers',
                STOCK_CONNECT);
        }

        if ($this->gadget->GetPermission('Settings')) {
            $menubar->AddOption(
                'Settings',
                _t('GLOBAL_SETTINGS'),
                BASE_SCRIPT . '?gadget=Notification&amp;action=Settings',
                STOCK_PREFERENCES);
        }

        $menubar->Activate($action);
        return $menubar->Get();
    }

}