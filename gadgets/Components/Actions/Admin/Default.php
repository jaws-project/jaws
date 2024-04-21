<?php
/**
 * Components (Jaws Management System) Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar <dufuz@php.net>
 * @copyright   2004-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Components_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Builds the menubar
     *
     * @access  public
     * @param   string  $action  Selected action
     * @return  string  XHTML UI
     */
    function Menubar($action)
    {
        $actions = array('Gadgets', 'Plugins');
        if (!in_array($action, $actions)) {
            $action = 'Gadgets';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageGadgets')) {
            $menubar->AddOption('Gadgets', $this::t('GADGETS'),
                                BASE_SCRIPT . '?reqGadget=Components&amp;reqAction=Gadgets', 'gadgets/Components/Resources/images/gadgets.png');
        }
        if ($this->gadget->GetPermission('ManagePlugins')) {
            $menubar->AddOption('Plugins', $this::t('PLUGINS'),
                                BASE_SCRIPT . '?reqGadget=Components&amp;reqAction=Plugins', 'gadgets/Components/Resources/images/plugins.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}