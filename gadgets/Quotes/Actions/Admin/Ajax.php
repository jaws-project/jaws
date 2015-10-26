<?php
/**
 * Quotes AJAX API
 *
 * @category   Ajax
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Gets data of the quote
     *
     * @access  public
     * @return  mixed   Quote data array or False on error
     */
    function GetQuote()
    {
        @list($qid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Quotes');
        $quote = $model->GetQuote($qid);
        if (Jaws_Error::IsError($quote)) {
            return false; //we need to handle errors on ajax
        }

        if (isset($quote['id'])) {
            $objDate = Jaws_Date::getInstance();
            if (!empty($quote['start_time'])) {
                $quote['start_time'] = $objDate->Format($quote['start_time'], 'Y-m-d H:i:s');
            }
            if (!empty($quote['stop_time'])) {
                $quote['stop_time'] = $objDate->Format($quote['stop_time'], 'Y-m-d H:i:s');
            }
        }

        return $quote;
    }

    /**
     * Gets data of quotes
     *
     * @access  public
     * @return  mixed   Quotes data array or False on error
     */
    function GetQuotes()
    {
        @list($qid, $gid) = jaws()->request->fetchAll('post');
        $gid = empty($gid)? -1 : $gid;
        $model = $this->gadget->model->load('Quotes');
        $quoteInfo = $model->GetQuotes($qid, $gid);
        if (Jaws_Error::IsError($quoteInfo) || !isset($quoteInfo[0])) {
            return false; //we need to handle errors on ajax
        }

        return ($qid == -1)? $quoteInfo : $quoteInfo[0];
    }

    /**
     * Inserts a new quote
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertQuote()
    {
        $this->gadget->CheckPermission('ManageQuotes');
        @list($title, $quotation, $gid, $start_time, $stop_time,
            $show_title, $published
        ) = jaws()->request->fetchAll('post');
        $quotation = jaws()->request->fetch(1, 'post', 'strip_crlf');
        $model = $this->gadget->model->loadAdmin('Quotes');
        $model->InsertQuote($title, $quotation, $gid, $start_time, $stop_time, $show_title, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the quote
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateQuote()
    {
        $this->gadget->CheckPermission('ManageQuotes');
        @list($id, $title, $quotation, $gid, $start_time, $stop_time,
            $show_title, $published
        ) = jaws()->request->fetchAll('post');
        $quotation = jaws()->request->fetch(2, 'post', 'strip_crlf');
        $model = $this->gadget->model->loadAdmin('Quotes');
        $model->UpdateQuote($id, $title, $quotation, $gid, $start_time, $stop_time, $show_title, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the quote
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteQuote()
    {
        $this->gadget->CheckPermission('ManageQuotes');
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Quotes');
        $model->DeleteQuote($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets data of a quote group
     *
     * @access  public
     * @return  mixed   Group data array or False on error
     */
    function GetGroup()
    {
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Groups');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }

    /**
     * Inserts a new group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertGroup()
    {
        $this->gadget->CheckPermission('ManageQuoteGroups');
        @list($title, $view_mode, $view_type, $show_title,
            $limit_count, $randomly, $published
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->InsertGroup($title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup()
    {
        $this->gadget->CheckPermission('ManageQuoteGroups');
        @list($gid, $title, $view_mode, $view_type, $show_title,
            $limit_count, $randomly, $published
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->UpdateGroup($gid, $title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets the group-quotes form
     *
     * @access  public
     * @return  string  XHTML template content of group-quotes
     */
    function GroupQuotesUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Groups');
        return $gadget->GroupQuotesUI();
    }

    /**
     * Assigns quotes to a certain group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddQuotesToGroup()
    {
        $this->gadget->CheckPermission('ManageQuoteGroups');
        @list($gid, $quotes) = jaws()->request->fetch(array('0', '1:array'), 'post');
        $model = $this->gadget->model->loadAdmin('Quotes');
        $model->AddQuotesToGroup($gid, $quotes);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup()
    {
        $this->gadget->CheckPermission('ManageQuoteGroups');
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}