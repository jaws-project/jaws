<?php
/**
 * Search Gadget Admin
 *
 * @category    GadgetModel
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2005-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
require_once JAWS_PATH . 'gadgets/Search/Model.php';

class SearchAdminModel extends SearchModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        // Registry keys
        $this->AddRegistry('searchable_gadgets', '*');
        $this->AddRegistry('min_key_len', '3');
        $this->AddRegistry('max_result_len', '500');
        $this->AddRegistry('results_limit', '10');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UninstallGadget()
    {
        // registry key
        $this->DelRegistry('searchable_gadgets');
        $this->DelRegistry('min_key_len');
        $this->DelRegistry('max_result_len');
        $this->DelRegistry('results_limit');

        return true;
    }

    /**
     * Updates the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.7.0', '<')) {
            // Registry keys.
            $this->AddRegistry('min_key_len', '3');
            $this->AddRegistry('max_result_len', '500');
        }
        $this->AddRegistry('results_limit', '10');

        return true;
    }

    /**
     * Sets searchable gadgets
     *
     * @access  public
     * @param   array   $gadgets    List of gadgets to be set as searchable
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function SetSearchableGadgets($gadgets)
    {
        $list = is_array($gadgets)? implode(', ', $gadgets) : '*';
        $res = $this->SetRegistry('searchable_gadgets', $list);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SEARCH_ERROR_NOT_SAVED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SEARCH_ERROR_NOT_SAVED'), _t('SEARCH_NAME'));
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('SEARCH_SAVED'), RESPONSE_NOTICE);
        return true;
    }
}