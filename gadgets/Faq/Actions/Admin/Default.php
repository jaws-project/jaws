<?php
/**
 * Faq Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Builds the menubar
     *
     * @access  public
     * @param   string  $selected   Selected action
     * @return  string  XHTML menu template
     */
    function MenuBar($selected)
    {
        $actions = array('Questions', 'Categories');

        if (!in_array($selected, $actions)) {
            $selected = 'Questions';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Questions', $this::t('LIST'),
                            BASE_SCRIPT . '?reqGadget=Faq&amp;reqAction=Questions', STOCK_DOCUMENTS);

        if ($this->gadget->GetPermission('ManageCategories')) {
            $menubar->AddOption('Categories', $this::t('CATEGORIES'),
                                BASE_SCRIPT . '?reqGadget=Faq&amp;reqAction=Categories',
                                'gadgets/Faq/Resources/images/categories.png');
        }

        $menubar->Activate($selected);

        return $menubar->Get();
    }

}