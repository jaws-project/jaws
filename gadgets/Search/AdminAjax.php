<?php
/**
 * Search AJAX API
 *
 * @category    Ajax
 * @package     Search
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_AdminAjax extends Jaws_Gadget_HTML
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
        $model = $GLOBALS['app']->LoadGadget('Search', 'AdminModel', 'Settings');
        $model->SetSearchableGadgets($gadgets);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}