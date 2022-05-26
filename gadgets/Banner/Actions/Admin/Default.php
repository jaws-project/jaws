<?php
/**
 * Banner Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Banner
 */
class Banner_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Prepares the banners menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML template of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Banners', 'Groups', 'Reports');
        if (!in_array($action, $actions)) {
            $action = 'Banners';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageBanners')) {
            $menubar->AddOption('Banners', $this->gadget->title,
                                BASE_SCRIPT . '?reqGadget=Banner&amp;reqAction=Banners', 'gadgets/Banner/Resources/images/banners_mini.png');
        }
        if ($this->gadget->GetPermission('ManageGroups')) {
            $menubar->AddOption('Groups', $this::t('GROUPS_GROUPS'),
                                BASE_SCRIPT . '?reqGadget=Banner&amp;reqAction=Groups', 'gadgets/Banner/Resources/images/groups_mini.png');
        }
        if ($this->gadget->GetPermission('ViewReports')) {
            $menubar->AddOption('Reports', $this::t('REPORTS_REPORTS'),
                                BASE_SCRIPT . '?reqGadget=Banner&amp;reqAction=Reports', 'gadgets/Banner/Resources/images/reports_mini.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}