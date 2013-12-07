<?php
/**
 * Quotes Gadget Action
 *
 * @category   GadgetAdmin
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Prepares the quotes menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Quotes', 'QuoteGroups');
        if (!in_array($action, $actions)) {
            $action = 'Quotes';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageQuotes')) {
            $menubar->AddOption(
                'Quotes',
                $this->gadget->title,
                BASE_SCRIPT . '?gadget=Quotes',
                'gadgets/Quotes/Resources/images/quotes_mini.png'
            );
        }
        if ($this->gadget->GetPermission('ManageQuoteGroups')) {
            $menubar->AddOption(
                'QuoteGroups',
                _t('QUOTES_GROUPS'),
                BASE_SCRIPT . '?gadget=Quotes&amp;action=QuoteGroups',
                'gadgets/Quotes/Resources/images/groups_mini.png'
            );
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }
}