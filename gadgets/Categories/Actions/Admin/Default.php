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
            _t('CATEGORIES_CATEGORIES'),
            BASE_SCRIPT . '?gadget=Categories&amp;action=Categories',
            STOCK_DOCUMENTS);
        $menubar->Activate($action);
        return $menubar->Get();
    }
}