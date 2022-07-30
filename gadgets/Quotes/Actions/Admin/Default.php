<?php
/**
 * Quotes Core Gadget
 *
 * @category    Gadget
 * @package     Quotes
 */
class Quotes_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Builds the users menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML menubar
     */
    function MenuBar($action)
    {
        $actions = array('quotes', 'categories');
        if (!in_array($action, $actions)) {
            $action = 'quotes';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageQuotes')) {
            $menubar->AddOption(
                'quotes',
                $this::t('QUOTES'),
                $this->gadget->url('quotes'),
                'gadgets/Quotes/Resources/images/quotes_mini.png'
            );
        }
        if ($this->gadget->GetPermission('ManageCategories')) {
            $menubar->AddOption(
                'categories',
                $this::t('GROUPS'),
                $this->gadget->url('categories'),
                'gadgets/Quotes/Resources/images/groups_mini.png'
            );
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }
}