<?php
/**
 * SiteActivity Core Gadget
 *
 * @category    Gadget
 * @package     SiteActivity
 */
class SiteActivity_Actions_Admin_Default extends Jaws_Gadget_Action
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
        $actions = array('SiteActivity');
        if (!in_array($action, $actions)) {
            $action = 'SiteActivity';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption(
            'SiteActivity',
            _t('SITEACTIVITY_SITEACTIVITY'),
            BASE_SCRIPT . '?gadget=SiteActivity&amp;action=SiteActivity',
            STOCK_PREFERENCES);

        $menubar->Activate($action);
        return $menubar->Get();
    }

}