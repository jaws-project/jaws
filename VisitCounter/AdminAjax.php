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
class VisitCounterAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     */
    function VisitCounterAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Clean all the entries (records)
     *
     * @access  public
     * @return  array   Response
     */
    function CleanEntries()
    {
        $this->CheckSession('VisitCounter', 'ResetCounter');
        $this->_Model->ClearVisitors();
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Reset the counter
     *
     * @access  public
     * @return  array   Response
     */
    function ResetCounter()
    {
        $this->CheckSession('VisitCounter', 'ResetCounter');
        $this->_Model->SetStartDate(date('Y-m-d H:i:s'));
        $this->_Model->ResetCounter();
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get the start date
     *
     * @access  public
     * @return  string  Start date (taken from registry)
     */
    function GetStartDate()
    {
        $date  = $GLOBALS['app']->loadDate();
        $start = $this->_Model->GetStartDate();
        return $date->Format($start);
    }

    /**
     * Update the properties
     *
     * @access  public
     * @param   int     $numdays Number of days
     * @param   string  $type    The type of visits being displayed
     * @return  array   Response
     */
    function UpdateProperties($online, $today, $total, $custom, $numdays, $type, $mode, $custom_text)
    {
        $this->CheckSession('VisitCounter', 'UpdateProperties');
        $this->_Model->UpdateProperties($online, $today, $total, $custom, $numdays, $type, $mode, $custom_text);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get all entries/records
     *
     * @access  public
     * @return  array   Array of webcams
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