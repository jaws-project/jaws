<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2022 Jaws Development Group
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
                         'ManageCategories', 'Types', 'AdditionalSettings');
        if (!in_array($action_selected, $actions)) {
            $action_selected = 'ListEntries';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Summary',$this::t('SUMMARY'),
                                BASE_SCRIPT . '?reqGadget=Blog&amp;reqAction=Summary', 'images/stock/new.png');
        if ($this->gadget->GetPermission('AddEntries')) {
            $menubar->AddOption('NewEntry', $this::t('NEW_ENTRY'),
                                BASE_SCRIPT . '?reqGadget=Blog&amp;reqAction=NewEntry', 'images/stock/new.png');
        }
        $menubar->AddOption('ListEntries', $this::t('LIST_ENTRIES'),
                            BASE_SCRIPT . '?reqGadget=Blog&amp;reqAction=ListEntries', 'images/stock/edit.png');
        if (Jaws_Gadget::IsGadgetInstalled('Comments') && $this->gadget->GetPermission('ManageComments')) {
            $menubar->AddOption('ManageComments', $this::t('MANAGE_COMMENTS'),
                                BASE_SCRIPT . '?reqGadget=Blog&amp;reqAction=ManageComments', 'images/stock/stock-comments.png');
        }
        if ($this->gadget->GetPermission('ManageTrackbacks')) {
            $menubar->AddOption('ManageTrackbacks', $this::t('MANAGE_TRACKBACKS'),
                                BASE_SCRIPT . '?reqGadget=Blog&amp;reqAction=ManageTrackbacks', 'images/stock/stock-comments.png');
        }
        if ($this->gadget->GetPermission('ManageCategories')) {
            $menubar->AddOption('ManageCategories', $this::t('CATEGORIES'),
                                BASE_SCRIPT . '?reqGadget=Blog&amp;reqAction=ManageCategories', 'images/stock/edit.png');
        }
        if ($this->gadget->GetPermission('ManageTypes')) {
            $menubar->AddOption('Types', $this::t('TYPES'),
                                BASE_SCRIPT . '?reqGadget=Blog&amp;reqAction=Types',
                                'gadgets/Blog/Resources/images/categories.png');
        }
        if ($this->gadget->GetPermission('Settings')) {
            $menubar->AddOption('AdditionalSettings', $this::t('SETTINGS'),
                                BASE_SCRIPT . '?reqGadget=Blog&amp;reqAction=AdditionalSettings', 'images/stock/properties.png');
        }
        $menubar->Activate($action_selected);

        return $menubar->Get();
    }

}