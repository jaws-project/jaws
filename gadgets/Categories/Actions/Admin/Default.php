<?php
/**
 * Categories Core Gadget
 *
 * @category    Gadget
 * @package     Categories
 */
class Categories_Actions_Admin_Default extends Jaws_Gadget_Action
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
        $actions = array('Categories');
        if (!in_array($action, $actions)) {
            $action = 'Categories';
        }

        $menubar = new Jaws_Widgets_Menubar();

        $menubar->AddOption(
            'Categories',
            $this::t('CATEGORIES'),
            BASE_SCRIPT . '?reqGadget=Categories&amp;reqAction=Categories',
            STOCK_DOCUMENTS);
        $menubar->Activate($action);
        return $menubar->Get();
    }
}