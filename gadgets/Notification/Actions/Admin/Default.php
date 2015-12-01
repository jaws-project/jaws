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
        $actions = array('Settings');
        if (!in_array($action, $actions)) {
            $action = 'Settings';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption(
            'Settings',
            _t('GLOBAL_SETTINGS'),
            BASE_SCRIPT . '?gadget=Notification&amp;action=Settings',
            STOCK_PREFERENCES);

        $menubar->Activate($action);
        return $menubar->Get();
    }

}