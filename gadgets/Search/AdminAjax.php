<?php
/**
 * Search AJAX API
 *
 * @category   Ajax
 * @package    Search
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SearchAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     */
    function SearchAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Set searchable gadgets
     *
     * @access  public
     * @param   array    $gadgets Array with gadgets to be set as searchable
	 * @param   string   $display_gadgets Set the display_gadgets flag in registry
     */
    function SaveChanges($gadgets)
    {
		$this->_Model->SetSearchableGadgets($gadgets);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}