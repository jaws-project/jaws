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
class QuotesAdminAjax extends Jaws_Ajax
{
    /**
     * Get information of a quote
     *
     * @access  public
     * @param   int     $qid
     * @return  array   Quote(s) information
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
     * Get information of a quotes
     *
     * @access  public
     * @param   int     $qid
     * @param   int      $gid
     * @return  array   Quote(s) information
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
     * Insert the information of a Quote
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    function InsertQuote($title, $quotation, $gid, $start_time, $stop_time, $show_title, $published)
    {
        $this->CheckSession('Quotes', 'ManageQuotes');
        $this->_Model->InsertQuote($title, $quotation, $gid, $start_time, $stop_time, $show_title, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update the information of a Quote
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    function UpdateQuote($id, $title, $quotation, $gid, $start_time, $stop_time, $show_title, $published)
    {
        $this->CheckSession('Quotes', 'ManageQuotes');
        $this->_Model->UpdateQuote($id, $title, $quotation, $gid, $start_time, $stop_time, $show_title, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a Quote
     *
     * @access  public
     * @param   int    $id Quote ID
     * @return  array  Response (notice or error)
     */
    function DeleteQuote($id)
    {
        $this->CheckSession('Quotes', 'ManageQuotes');
        $this->_Model->DeleteQuote($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get information of a quote group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  array   Group information
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
     * Insert groups
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    function InsertGroup($title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published)
    {
        $this->CheckSession('Quotes', 'ManageQuoteGroups');
        $this->_Model->InsertGroup($title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update groups
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    function UpdateGroup($gid, $title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published)
    {
        $this->CheckSession('Quotes', 'ManageQuoteGroups');
        $this->_Model->UpdateGroup($gid, $title, $view_mode, $view_type, $show_title, $limit_count, $randomly, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get the quotes-group form
     *
     * @access  public
     * @return  string
     */
    function GroupQuotesUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Quotes', 'AdminHTML');
        return $gadget->GroupQuotesUI();
    }

    /**
     * Add a group of quote (by they ids) to a certain group
     *
     * @access  public
     * @param   int     $gid  Group's ID
     * @param   array   $quotes Array with quote id
     * @return  array   Response (notice or error)
     */
    function AddQuotesToGroup($gid, $quotes)
    {
        $this->CheckSession('Quotes', 'ManageQuoteGroups');
        $res = $this->_Model->AddQuotesToGroup($gid, $quotes);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an group
     *
     * @access  public
     * @param   int     $gid   group ID
     * @return  array   Response (notice or error)
     */
    function DeleteGroup($gid)
    {
        $this->CheckSession('Quotes', 'ManageQuoteGroups');
        $this->_Model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}