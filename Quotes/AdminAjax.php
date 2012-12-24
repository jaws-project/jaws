<?php
/**
 * Quotes AJAX API
 *
 * @category   Ajax
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_AdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Gets data of the quote
     *
     * @access  public
     * @param   int     $qid
     * @return  mixed   Quote data array or False on error
     */
    function GetQuote($qid)
    {
        $quote = $this->_Model->GetQuote($qid);
        if (Jaws_Error::IsError($quote)) {
            return false; //we need to handle errors on ajax
        }

        if (isset($quote['id'])) {
            $objDate = $GLOBALS['app']->loadDate();
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
     * @param   int     $qid    Quote ID
     * @param   int     $gid    Group ID
     * @return  mixed   Quotes data array or False on error
     */
    function GetQuotes($qid, $gid = -1)
    {
        $quoteInfo = $this->_Model->GetQuotes($qid, $gid);
        if (Jaws_Error::IsError($quoteInfo) || !isset($quoteInfo[0])) {
            return false; //we need to handle errors on ajax
        }

        return ($qid == -1)? $quoteInfo : $quoteInfo[0];
    }

    /**
     * Inserts a new quote
     *
     * @access  public
     * @param   string  $title
     * @param   string  $quotation
     * @param   int     $gid    group ID
     * @param   string  $start_time
     * @param   string  $stop_time
     * @param   bool    $show_title
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function InsertQuote($title, $quotation, $gid, $start_time, $stop_time, $show_title, $published)
    {
        $this->CheckSession('Quotes', 'ManageQuotes');

        $request =& Jaws_Request::getInstance();
        $quotation = $request->get(1, 'post', false);
        $this->_Model->InsertQuote($title, $quotation, $gid, $start_time, $stop_time, $show_title, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the quote
     *
     * @access  public
     * @param   int     $id         Quote ID
     * @param   string  $title
     * @param   string  $quotation
     * @param   int     $gid        Group ID
     * @param   string  $start_time
     * @param   string  $stop_time
     * @param   bool    $show_title
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function UpdateQuote($id, $title, $quotation, $gid, $start_time, $stop_time, $show_title, $published)
    {
        $this->CheckSession('Quotes', 'ManageQuotes');

        $request =& Jaws_Request::getInstance();
        $quotation = $request->get(2, 'post', false);
        $this->_Model->UpdateQuote($id, $title, $quotation, $gid, $start_time, $stop_time, $show_title, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the quote
     *
     * @access  public
     * @param   int     $id  Quote ID
     * @return  array   Response array (notice or error)
     */
    function DeleteQuote($id)
    {
        $this->CheckSession('Quotes', 'ManageQuotes');
        $this->_Model->DeleteQuote($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets data of a quote group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  mixed   Group data array or False on error
     */
    function GetGroup($gid)
    {
        $group = $this->_Model->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }

    /**
     * Inserts a new group
     *
     * @access  public
     * @param   string  $title
     * @param   int     $view_mode
     * @param   int     $view_type
     * @param   bool    $show_title
     * @param   int     $limit_count
     * @param   bool    $randomly
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function InsertGroup($title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published)
    {
        $this->CheckSession('Quotes', 'ManageQuoteGroups');
        $this->_Model->InsertGroup($title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the group
     *
     * @access  public
     * @param   int     $gid         Group ID
     * @param   string  $title
     * @param   int     $view_mode
     * @param   int     $view_type
     * @param   bool    $show_title
     * @param   int     $limit_count
     * @param   bool    $randomly
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup($gid, $title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published)
    {
        $this->CheckSession('Quotes', 'ManageQuoteGroups');
        $this->_Model->UpdateGroup($gid, $title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published);

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
        $gadget = $GLOBALS['app']->LoadGadget('Quotes', 'AdminHTML');
        return $gadget->GroupQuotesUI();
    }

    /**
     * Assigns quotes to a certain group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @param   array   $quotes Array of IDs
     * @return  array   Response array (notice or error)
     */
    function AddQuotesToGroup($gid, $quotes)
    {
        $this->CheckSession('Quotes', 'ManageQuoteGroups');
        $res = $this->_Model->AddQuotesToGroup($gid, $quotes);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup($gid)
    {
        $this->CheckSession('Quotes', 'ManageQuoteGroups');
        $this->_Model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}