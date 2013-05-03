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
        // registry key
        $this->gadget->registry->delete('searchable_gadgets');
        $this->gadget->registry->delete('min_key_len');
        $this->gadget->registry->delete('max_result_len');
        $this->gadget->registry->delete('results_limit');

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
        if (version_compare($old, '0.7.0', '<')) {
            // Registry keys.
            $this->gadget->registry->insert('min_key_len', '3');
            $this->gadget->registry->insert('max_result_len', '500');
        }
        $this->gadget->registry->insert('results_limit', '10');

        return true;
    }

}