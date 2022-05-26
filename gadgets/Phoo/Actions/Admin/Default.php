<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Displays a menu bar for the control panel gadget.
     *
     * @access protected
     * @param   string   $action_selected    The item to display as selected.
     * @return  string   XHTML template content for menubar
     */
    function MenuBar($action_selected)
    {
        $actions = array('Photos', 'ManageComments', 'AdditionalSettings', 'Import');
        if (!in_array($action_selected, $actions))
            $action_selected = 'Photos';

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Photos', $this::t('PHOTOS'), BASE_SCRIPT . '?reqGadget=Phoo', STOCK_IMAGE);
        if (Jaws_Gadget::IsGadgetInstalled('Comments') && $this->gadget->GetPermission('ManageComments')) {
            $menubar->AddOption(
                'ManageComments',
                $this::t('COMMENTS'),
                BASE_SCRIPT . '?reqGadget=Phoo&amp;reqAction=ManageComments',
                'images/stock/stock-comments.png'
            );
        }
        if ($this->gadget->GetPermission('Settings')) {
            $menubar->AddOption(
                'AdditionalSettings',
                $this::t('ADDITIONAL_SETTINGS'),
                BASE_SCRIPT . '?reqGadget=Phoo&amp;reqAction=AdditionalSettings',
                'images/stock/properties.png'
            );
        }

        if ($this->gadget->GetPermission('Import')) {
            $menubar->AddOption(
                'Import',
                $this::t('IMPORT'),
                BASE_SCRIPT . '?reqGadget=Phoo&amp;reqAction=Import',
                STOCK_IMAGE
            );
        }

        $menubar->Activate($action_selected);

        return $menubar->Get();
    }

}