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
class Search_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Updates searchable gadgets
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function SaveChanges()
    {
        $gadgets = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Settings');
        $model->SetSearchableGadgets($gadgets);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}