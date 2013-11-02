<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Displays admin menu bar according to selected action
     *
     * @access  public
     * @param   string  $action_selected    selected action
     * @return  string XHTML template content
     */
    function MenuBar($action_selected)
    {
        $actions = array('Summary', 'NewEntry', 'ListEntries',
                         'ManageComments', 'ManageTrackbacks',
                         'ManageCategories', 'AdditionalSettings');
        if (!in_array($action_selected, $actions)) {
            $action_selected = 'ListEntries';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Summary',_t('BLOG_SUMMARY'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=Summary', 'images/stock/new.png');
        if ($this->gadget->GetPermission('AddEntries')) {
            $menubar->AddOption('NewEntry', _t('BLOG_NEW_ENTRY'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=NewEntry', 'images/stock/new.png');
        }
        $menubar->AddOption('ListEntries', _t('BLOG_LIST_ENTRIES'),
                            BASE_SCRIPT . '?gadget=Blog&amp;action=ListEntries', 'images/stock/edit.png');
        if (Jaws_Gadget::IsGadgetInstalled('Comments') && $this->gadget->GetPermission('ManageComments')) {
            $menubar->AddOption('ManageComments', _t('BLOG_MANAGE_COMMENTS'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=ManageComments', 'images/stock/stock-comments.png');
        }
        if ($this->gadget->GetPermission('ManageTrackbacks')) {
            $menubar->AddOption('ManageTrackbacks', _t('BLOG_MANAGE_TRACKBACKS'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=ManageTrackbacks', 'images/stock/stock-comments.png');
        }
        if ($this->gadget->GetPermission('ManageCategories')) {
            $menubar->AddOption('ManageCategories', _t('BLOG_CATEGORIES'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=ManageCategories', 'images/stock/edit.png');
        }
        if ($this->gadget->GetPermission('Settings')) {
            $menubar->AddOption('AdditionalSettings', _t('BLOG_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=AdditionalSettings', 'images/stock/properties.png');
        }
        $menubar->Activate($action_selected);

        return $menubar->Get();
    }

}