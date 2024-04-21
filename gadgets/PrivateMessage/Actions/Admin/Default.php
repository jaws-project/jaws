<?php
/**
 * PrivateMessage Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     PrivateMessage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Actions_Admin_Default extends Jaws_Gadget_Action
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
        $actions = array('Properties');
        if (!in_array($action, $actions)) {
            $action = 'Properties';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageProperties')) {
            $menubar->AddOption('Properties',
                                Jaws::t('PROPERTIES'),
                                BASE_SCRIPT . '?reqGadget=PrivateMessage&amp;reqAction=Properties',
                                STOCK_PREFERENCES);
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}