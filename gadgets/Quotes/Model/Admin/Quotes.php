<?php
/**
 * Quotes Gadget
 *
 * @category    GadgetModel
 * @package     Quotes
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Model_Admin_Quotes extends Jaws_Gadget_Model
{
    /**
     * Inserts a new quote
     *
     * @access  public
     * @param   string  $title
     * @param   string  $quotation
     * @param   int     $gid        Group ID
     * @param   string  $start_time
     * @param   string  $stop_time
     * @param   bool    $show_title
     * @param   bool    $published
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function InsertQuote($title, $quotation, $gid, $start_time, $stop_time, $show_title, $published)
    {
        $date = Jaws_Date::getInstance();
        $now  = Jaws_DB::getInstance()->date();
        $params['title']       = $title;
        $params['quotation']   = $quotation;
        $params['gid']         = $gid;
        $params['start_time']  = null;
        $params['stop_time']   = null;
        if (!empty($start_time)) {
            $start_time = $date->ToBaseDate(preg_split('/[- :]/', $start_time), 'Y-m-d H:i:s');
            $params['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time,  'Y-m-d H:i:s');
        }
        if (!empty($stop_time)) {
            $stop_time  = $date->ToBaseDate(preg_split('/[- :]/', $stop_time), 'Y-m-d H:i:s');
            $params['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time,   'Y-m-d H:i:s');
        }
        $params['createtime']  = $now;
        $params['updatetime']  = $now;
        $params['show_title']  = (bool)$show_title;
        $params['published']   = (bool)$published;

        $quotesTable = Jaws_ORM::getInstance()->table('quotes');
        $result = $quotesTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('QUOTES_QUOTE_NOT_ADDED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(
            _t('QUOTES_QUOTE_ADDED'),
            RESPONSE_NOTICE,
            array('id' => $result, 'title' => $title)
        );

        return true;
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
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function UpdateQuote($id, $title, $quotation, $gid, $start_time, $stop_time, $show_title, $published)
    {
        $date = Jaws_Date::getInstance();
        $params['title']       = $title;
        $params['quotation']   = $quotation;
        $params['gid']         = $gid;
        $params['start_time']  = null;
        $params['stop_time']   = null;
        if (!empty($start_time)) {
            $start_time = $date->ToBaseDate(preg_split('/[- :]/', $start_time), 'Y-m-d H:i:s');
            $params['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time,  'Y-m-d H:i:s');
        }
        if (!empty($stop_time)) {
            $stop_time  = $date->ToBaseDate(preg_split('/[- :]/', $stop_time), 'Y-m-d H:i:s');
            $params['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time,   'Y-m-d H:i:s');
        }

        $params['updatetime']  = Jaws_DB::getInstance()->date();
        $params['show_title']  = (bool)$show_title;
        $params['published']   = (bool)$published;

        $quotesTable = Jaws_ORM::getInstance()->table('quotes');
        $result = $quotesTable->update($params)->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_QUOTE_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('QUOTES_QUOTE_NOT_UPDATED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_QUOTE_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the quote
     *
     * @access  public
     * @param   int     $id  Quote ID
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function DeleteQuote($id)
    {
        $quotesTable = Jaws_ORM::getInstance()->table('quotes');
        $result = $quotesTable->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_QUOTE_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('QUOTES_QUOTE_NOT_DELETED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_QUOTE_DELETED'), RESPONSE_NOTICE);
        return true;
    }


    /**
     * Updates group of the quote
     *
     * @access  public
     * @param   int     $qid        Quote ID
     * @param   int     $gid        Group ID
     * @param   int     $new_gid    New group ID
     * @return  bool    True on Success or False on failure
     */
    function UpdateQuoteGroup($qid, $gid, $new_gid)
    {
        $quotesTable = Jaws_ORM::getInstance()->table('quotes');
        $quotesTable->update(array('gid' => $new_gid));

        if (($qid != -1) && ($gid != -1)) {
            $quotesTable->where('id', $qid)->and()->where('gid', $gid);
        } elseif ($gid != -1) {
            $quotesTable->where('gid', $gid);
        } elseif ($qid != -1) {
            $quotesTable->where('id', $qid);
        } else {
            $quotesTable->where('gid', $new_gid);
        }

        $result = $quotesTable->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Updates the group ID of quotes
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @param   array   $quotes Array of IDs
     * @return  bool    Always True!
     */
    function AddQuotesToGroup($gid, $quotes)
    {
        $model = $this->gadget->model->load('Quotes');
        $AllQuotes = $model->GetQuotes(-1, -1);

        foreach ($AllQuotes as $quote) {
            if ($quote['gid'] == $gid) {
                if (!in_array($quote['id'], $quotes)) {
                    $this->UpdateQuoteGroup($quote['id'], -1, 0);
                }
            } else {
                if (in_array($quote['id'], $quotes)) {
                    $this->UpdateQuoteGroup($quote['id'], -1, $gid);
                }
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_UPDATED_QUOTES'), RESPONSE_NOTICE);

        return true;
    }


}