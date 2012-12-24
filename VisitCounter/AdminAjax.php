<?php
/**
 * VisitCounter AJAX API
 *
 * @category   Ajax
 * @package    VisitCounter
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_AdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Cleans all the entries (records)
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function CleanEntries()
    {
        $this->CheckSession('VisitCounter', 'ResetCounter');
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
        $this->CheckSession('VisitCounter', 'ResetCounter');
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
     * @param   int     $online         Number of online visitors
     * @param   int     $today          Number of today visitors
     * @param   int     $total          Number of total visitors
     * @param   string  $custom         Custome text to be displayed
     * @param   int     $numdays        Cookie lifetime in days
     * @param   string  $type           The type of visits being displayed
     * @param   int     $mode           Display mode
     * @param   string  $custom_text    User defined text to be displayed
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties($online, $today, $total, $custom, $numdays, $type, $mode, $custom_text)
    {
        $this->CheckSession('VisitCounter', 'UpdateProperties');

        $request =& Jaws_Request::getInstance();
        $custom_text = $request->get(7, 'post', false);
        $this->_Model->UpdateProperties($online, $today, $total, $custom, $numdays, $type, $mode, $custom_text);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets all entries/records for datagrid
     *
     * @access  public
     * @limit   int     $limit  Data limit to fetch
     * @return  array   List of visits
     */
    function GetData($limit)
    {
        $gadget = $GLOBALS['app']->LoadGadget('VisitCounter', 'AdminHTML');
        if (!is_numeric($limit)) {
            $limit = 0;
        }

        return $gadget->GetVisits($limit);
    }

}