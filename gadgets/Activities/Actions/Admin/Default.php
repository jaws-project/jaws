<?php
/**
 * Activities Core Gadget
 *
 * @category    Gadget
 * @package     Activities
 */
class Activities_Actions_Admin_Default extends Jaws_Gadget_Action
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
        $actions = array('Activities');
        if (!in_array($action, $actions)) {
            $action = 'Activities';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption(
            'Activities',
            $this::t('ACTIVITIES'),
            BASE_SCRIPT . '?reqGadget=Activities&amp;reqAction=Activities',
            STOCK_PREFERENCES);

        $menubar->Activate($action);
        return $menubar->Get();
    }

}