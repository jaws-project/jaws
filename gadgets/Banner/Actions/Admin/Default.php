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
     * Calls default admin action
     *
     * @access  public
     * @return  string  XTHML Template content
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('ManageBanners')) {
            $gadgetHTML = $this->gadget->loadAdminAction('Banners');
            return $gadgetHTML->Banners();
        } elseif ($this->gadget->GetPermission('ManageGroups')) {
            $gadgetHTML = $this->gadget->loadAdminAction('Groups');
            return $gadgetHTML->Groups();
        }

        $this->gadget->CheckPermission('ViewReports');
        $gadgetHTML = $this->gadget->loadAdminAction('Reports');
        return $gadgetHTML->Reports();
    }

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
            $menubar->AddOption('Banners', _t('BANNER_NAME'),
                                BASE_SCRIPT . '?gadget=Banner&amp;action=Admin', 'gadgets/Banner/Resources/images/banners_mini.png');
        }
        if ($this->gadget->GetPermission('ManageGroups')) {
            $menubar->AddOption('Groups', _t('BANNER_GROUPS_GROUPS'),
                                BASE_SCRIPT . '?gadget=Banner&amp;action=Groups', 'gadgets/Banner/Resources/images/groups_mini.png');
        }
        if ($this->gadget->GetPermission('ViewReports')) {
            $menubar->AddOption('Reports', _t('BANNER_REPORTS_REPORTS'),
                                BASE_SCRIPT . '?gadget=Banner&amp;action=Reports', 'gadgets/Banner/Resources/images/reports_mini.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}