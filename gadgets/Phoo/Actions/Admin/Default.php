<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2014 Jaws Development Group
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
        $actions = array('Photos', 'Groups', 'ManageComments', 'AdditionalSettings', 'Import');
        if (!in_array($action_selected, $actions))
            $action_selected = 'Photos';

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Photos', _t('PHOO_PHOTOS'), BASE_SCRIPT . '?gadget=Phoo', STOCK_IMAGE);
        if ($this->gadget->GetPermission('Groups')) {
            $menubar->AddOption(
                'Groups',
                _t('GLOBAL_GROUPS'),
                BASE_SCRIPT . '?gadget=Phoo&amp;action=Groups',
                'gadgets/Phoo/Resources/images/groups_mini.png'
            );
        }
        if (Jaws_Gadget::IsGadgetInstalled('Comments') && $this->gadget->GetPermission('ManageComments')) {
            $menubar->AddOption(
                'ManageComments',
                _t('PHOO_COMMENTS'),
                BASE_SCRIPT . '?gadget=Phoo&amp;action=ManageComments',
                'images/stock/stock-comments.png'
            );
        }
        if ($this->gadget->GetPermission('Settings')) {
            $menubar->AddOption(
                'AdditionalSettings',
                _t('PHOO_ADDITIONAL_SETTINGS'),
                BASE_SCRIPT . '?gadget=Phoo&amp;action=AdditionalSettings',
                'images/stock/properties.png'
            );
        }

        if ($this->gadget->GetPermission('Import')) {
            $menubar->AddOption(
                'Import',
                _t('PHOO_IMPORT'),
                BASE_SCRIPT . '?gadget=Phoo&amp;action=Import',
                STOCK_IMAGE
            );
        }

        $menubar->Activate($action_selected);

        return $menubar->Get();
    }

}