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
        $actions = array('Messages', 'NotificationDrivers', 'Settings');
        if (!in_array($action, $actions)) {
            $action = 'Messages';
        }

        $menubar = new Jaws_Widgets_Menubar();

        if ($this->gadget->GetPermission('Messages')) {
            $menubar->AddOption('Messages',
                $this::t('MESSAGES'),
                BASE_SCRIPT . '?reqGadget=Notification&amp;reqAction=Messages',
                STOCK_OPEN);
        }

        if ($this->gadget->GetPermission('NotificationDrivers')) {
            $menubar->AddOption('NotificationDrivers',
                $this::t('DRIVERS'),
                BASE_SCRIPT . '?reqGadget=Notification&amp;reqAction=NotificationDrivers',
                STOCK_CONNECT);
        }

        if ($this->gadget->GetPermission('Settings')) {
            $menubar->AddOption(
                'Settings',
                Jaws::t('SETTINGS'),
                BASE_SCRIPT . '?reqGadget=Notification&amp;reqAction=Settings',
                STOCK_PREFERENCES);
        }

        $menubar->Activate($action);
        return $menubar->Get();
    }

}