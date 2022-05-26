<?php
/**
 * Subscription Core Gadget
 *
 * @category    Gadget
 * @package     Subscription
 */
class Subscription_Actions_Admin_Default extends Jaws_Gadget_Action
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
        $actions = array('Subscription');
        if (!in_array($action, $actions)) {
            $action = 'Subscription';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption(
            'Subscription',
            $this::t('SUBSCRIPTION'),
            BASE_SCRIPT . '?reqGadget=Subscription&amp;reqAction=Subscription',
            STOCK_PREFERENCES);

        $menubar->Activate($action);
        return $menubar->Get();
    }

}