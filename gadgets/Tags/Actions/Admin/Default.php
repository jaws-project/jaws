<?php
/**
 * Tags Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Tags
 */
class Tags_Actions_Admin_Default extends Jaws_Gadget_Action
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
        $actions = array('Tags', 'Properties');
        if (!in_array($action, $actions)) {
            $action = 'Tags';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Tags',
                            $this::t('TITLE'),
                            BASE_SCRIPT . '?reqGadget=Tags&amp;reqAction=Tags',
                            STOCK_NEW);
        if ($this->gadget->GetPermission('ManageProperties')) {
            $menubar->AddOption('Properties',
                                Jaws::t('PROPERTIES'),
                                BASE_SCRIPT . '?reqGadget=Tags&amp;reqAction=Properties',
                                STOCK_PREFERENCES);
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}