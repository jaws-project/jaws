<?php
/**
 * VisitCounter AJAX API
 *
 * @category   Ajax
 * @package    VisitCounter
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Cleans all the entries (records)
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function CleanEntries()
    {
        $this->gadget->CheckPermission('ResetCounter');
        $model = $this->gadget->model->loadAdmin('Visitors');
        $model->ClearVisitors();
        return $this->gadget->session->pop();
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
        $model = $this->gadget->model->loadAdmin('Visitors');
        $model->SetStartDate(date('yyyy-MM-dd HH:mm:ss'));
        $model->ResetCounter();
        return $this->gadget->session->pop();
    }

    /**
     * Gets the start date
     *
     * @access  public
     * @return  string  Start date(taken from registry)
     */
    function GetStartDate()
    {
        $date  = Jaws_Date::getInstance();
        $model = $this->gadget->model->load('Visitors');
        $start = $model->GetStartDate();
        return $date->Format($start);
    }

    /**
     * Updates properties
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties()
    {
        $this->gadget->CheckPermission('UpdateProperties');
        @list($counters, $numdays, $type, $mode, $custom_text) = $this->gadget->request->fetchAll('post');
        $custom_text = $this->gadget->request->fetch(4, 'post', false, array('filters' => false));
        $model = $this->gadget->model->loadAdmin('Properties');
        $model->UpdateProperties($counters, $numdays, $type, $mode, $custom_text);
        return $this->gadget->session->pop();
    }

    /**
     * Gets all entries/records for datagrid
     *
     * @access  public
     * @return  array   List of visits
     */
    function getData()
    {
        @list($offset) = $this->gadget->request->fetchAll('post');
        if (!is_numeric($offset)) {
            $offset = 0;
        }
        $gadget = $this->gadget->action->loadAdmin('VisitCounter');
        return $gadget->GetVisits($offset);
    }

}