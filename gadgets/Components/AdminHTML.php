<?php
/**
 * Components (Jaws Management System) Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar <dufuz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Components_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Default admin action
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('ManageGadgets')) {
            $htmlGadgets = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'Gadgets');
            return $htmlGadgets->Gadgets();
        }

        $this->gadget->CheckPermission('ManagePlugins');
        $htmlPlugins = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'Plugins');
        return $htmlPlugins->Plugins();
    }

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

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageGadgets')) {
            $menubar->AddOption('Gadgets', _t('COMPONENTS_GADGETS'),
                                BASE_SCRIPT . '?gadget=Components&amp;action=Gadgets', 'gadgets/Components/Resources/images/gadgets.png');
        }
        if ($this->gadget->GetPermission('ManagePlugins')) {
            $menubar->AddOption('Plugins', _t('COMPONENTS_PLUGINS'),
                                BASE_SCRIPT . '?gadget=Components&amp;action=Plugins', 'gadgets/Components/Resources/images/plugins.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}