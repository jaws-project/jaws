<?php
/**
 * VisitCounter AJAX API
 *
 * @category   Ajax
 * @package    VisitCounter
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function VisitCounter_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

    /**
     * Cleans all the entries (records)
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function CleanEntries()
    {
        $this->gadget->CheckPermission('ResetCounter');
        $this->_Model->ClearVisitors();
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Resets the counter
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function ResetCounter()
    {
        $this->gadget->CheckPermission('ResetCounter');
        $this->_Model->SetStartDate(date('Y-m-d H:i:s'));
        $this->_Model->ResetCounter();
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets the start date
     *
     * @access  public
     * @return  string  Start date(taken from registry)
     */
    function GetStartDate()
    {
        $date  = $GLOBALS['app']->loadDate();
        $start = $this->_Model->GetStartDate();
        return $date->Format($start);
    }

    /**
     * Updates properties
     *
     * @access  public
     * @param   string  $counters       Enabled visit counters
     * @param   int     $numdays        Cookie lifetime in days
     * @param   string  $type           The type of visits being displayed
     * @param   int     $mode           Display mode
     * @param   string  $custom_text    User defined text to be displayed
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties($counters, $numdays, $type, $mode, $custom_text)
    {
        $this->gadget->CheckPermission('UpdateProperties');

        $request =& Jaws_Request::getInstance();
        $custom_text = $request->get(4, 'post', false);
        $this->_Model->UpdateProperties($counters, $numdays, $type, $mode, $custom_text);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets all entries/records for datagrid
     *
     * @access  public
     * @param   int     $offset  Data offset to fetch
     * @return  array   List of visits
     */
    function GetData($offset)
    {
        $gadget = $GLOBALS['app']->LoadGadget('VisitCounter', 'AdminHTML');
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        return $gadget->GetVisits($offset);
    }

}