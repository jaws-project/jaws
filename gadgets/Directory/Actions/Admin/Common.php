<?php
/**
 * Directory Admin HTML file
 *
 * @category    GadgetAdmin
 * @package     Directory
 */
class Directory_Actions_Admin_Common extends Jaws_Gadget_Action
{
    /**
     * Displays admin menu bar according to selected action
     *
     * @access  public
     * @param   string  $action    selected action
     * @return  string XHTML template content
     */
    function MenuBar($action)
    {
        $actions = array('Directory', 'Comments');
        if (!in_array($action, $actions)) {
            $action = 'Directory';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Directory',$this::t('TITLE'),
            BASE_SCRIPT . '?reqGadget=Directory&amp;reqAction=Directory', 'images/stock/folder.png');

        if (Jaws_Gadget::IsGadgetInstalled('Comments') && $this->gadget->GetPermission('ManageComments')) {
            $menubar->AddOption('Comments', $this::t('FILE_COMMENTS'),
                BASE_SCRIPT . '?reqGadget=Directory&amp;reqAction=ManageComments', 'images/stock/stock-comments.png');
        }

        $menubar->Activate($action);

        return $menubar->Get();
    }
}