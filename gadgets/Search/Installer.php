<?php
/**
 * Search Installer
 *
 * @category    GadgetModel
 * @package     Search
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        // Registry keys
        $this->gadget->registry->insert('searchable_gadgets', '*');
        $this->gadget->registry->insert('min_key_len', '3');
        $this->gadget->registry->insert('max_result_len', '500');
        $this->gadget->registry->insert('results_limit', '10');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Uninstall()
    {
        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.9.0', '<')) {
            // Update layout actions
            $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('Search', 'Box', 'Box', 'Search');
                $layoutModel->EditGadgetLayoutAction('Search', 'SimpleBox', 'SimpleBox', 'Search');
                $layoutModel->EditGadgetLayoutAction('Search', 'AdvancedBox', 'AdvancedBox', 'Search');
            }
        }

        return true;
    }

}