<?php
/**
 * Search Gadget Admin
 *
 * @category    GadgetModel
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2005-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_Model_Admin_Settings extends Jaws_Gadget_Model
{
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
        $res = $this->gadget->registry->update('searchable_gadgets', $list);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push($this::t('ERROR_NOT_SAVED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_NOT_SAVED'));
        }
        $this->gadget->session->push($this::t('SAVED'), RESPONSE_NOTICE);
        return true;
    }

}