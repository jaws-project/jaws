<?php
/**
 * Comments Core Gadget
 *
 * @category    Gadget
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_Admin_Default extends Jaws_Gadget_Action
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
        $actions = array('Comments', 'Settings');
        if (!in_array($action, $actions)) {
            $action = 'Comments';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageComments')) {
            $menubar->AddOption(
                'Comments',
                _t('COMMENTS_TITLE'),
                BASE_SCRIPT . '?gadget=Comments&amp;action=Comments');
        }

        if ($this->gadget->GetPermission('Settings')) {
            $menubar->AddOption(
                'Settings',
                _t('GLOBAL_SETTINGS'),
                BASE_SCRIPT . '?gadget=Comments&amp;action=Settings',
                STOCK_PREFERENCES);
        }

        $menubar->Activate($action);
        return $menubar->Get();
    }

}