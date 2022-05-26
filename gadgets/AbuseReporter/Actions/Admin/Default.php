<?php
/**
 * AbuseReporter Core Gadget
 *
 * @category    Gadget
 * @package     AbuseReporter
 */
class AbuseReporter_Actions_Admin_Default extends Jaws_Gadget_Action
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
        $actions = array('Reports');
        if (!in_array($action, $actions)) {
            $action = 'Reports';
        }

        $menubar = new Jaws_Widgets_Menubar();

        $menubar->AddOption(
            'Reports',
            $this::t('REPORTS'),
            BASE_SCRIPT . '?reqGadget=AbuseReporter&amp;reqAction=Reports',
            STOCK_DOCUMENTS);
        $menubar->Activate($action);
        return $menubar->Get();
    }

}