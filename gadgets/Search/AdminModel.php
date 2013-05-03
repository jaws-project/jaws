<?php
require_once JAWS_PATH . 'gadgets/Search/Model.php';
/**
 * Search Gadget Admin
 *
 * @category    GadgetModel
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_AdminModel extends Search_Model
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
        $res = $this->gadget->Registry->update('searchable_gadgets', $list);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SEARCH_ERROR_NOT_SAVED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SEARCH_ERROR_NOT_SAVED'), _t('SEARCH_NAME'));
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('SEARCH_SAVED'), RESPONSE_NOTICE);
        return true;
    }
}