<?php
/**
 * Search AJAX API
 *
 * @category    Ajax
 * @package     Search
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2005-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_AdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Updates searchable gadgets
     *
     * @access  public
     * @param   array   $gadgets    Array with gadgets to be set as searchable
     * @return  array   Response array (notice or error)
     */
    function SaveChanges($gadgets)
    {
        $this->_Model->SetSearchableGadgets($gadgets);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}