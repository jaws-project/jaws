<?php
/**
 * Faq Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
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
        $actions = array('ManageQuestions', 'AddNewQuestion', 'AddNewCategory');

        if (!in_array($selected, $actions)) {
            $selected = 'ManageQuestions';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('ManageQuestions', _t('FAQ_LIST'),
                            BASE_SCRIPT . '?gadget=Faq&amp;action=ManageQuestions', STOCK_DOCUMENTS);

        if ($this->gadget->GetPermission('AddNewQuestion')) {
            $menubar->AddOption('AddNewQuestion', _t('FAQ_ADD_QUESTION'),
                                BASE_SCRIPT . '?gadget=Faq&amp;action=EditQuestion', STOCK_NEW);
        }

        if ($this->gadget->GetPermission('ManageCategories')) {
            $menubar->AddOption('AddNewCategory', _t('FAQ_ADD_CATEGORY'),
                                BASE_SCRIPT . '?gadget=Faq&amp;action=EditCategory', STOCK_NEW);
        }

        $menubar->Activate($selected);

        return $menubar->Get();
    }

}