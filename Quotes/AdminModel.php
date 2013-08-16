<?php
require_once JAWS_PATH . 'gadgets/Quotes/Model.php';
/**
 * Quotes Gadget
 *
 * @category    GadgetModel
 * @package     Quotes
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_AdminModel extends Quotes_Model
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
        $date = $GLOBALS['app']->loadDate();
        $now  = $GLOBALS['db']->Date();
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
            return new Jaws_Error(_t('QUOTES_QUOTE_NOT_ADDED'),_t('QUOTES_NAME'));
        }

        $response =  array();
        $response['id']      = $result;
        $response['title']   = $title;
        $response['message'] = _t('QUOTES_QUOTE_ADDED');

        $GLOBALS['app']->Session->PushLastResponse($response, RESPONSE_NOTICE);
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
        $date = $GLOBALS['app']->loadDate();
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

        $params['updatetime']  = $GLOBALS['db']->Date();
        $params['show_title']  = (bool)$show_title;
        $params['published']   = (bool)$published;

        $quotesTable = Jaws_ORM::getInstance()->table('quotes');
        $result = $quotesTable->update($params)->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_QUOTE_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('QUOTES_QUOTE_NOT_UPDATED'), _t('QUOTES_NAME'));
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
            return new Jaws_Error(_t('QUOTES_QUOTE_NOT_DELETED'), _t('QUOTES_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_QUOTE_DELETED'), RESPONSE_NOTICE);
        return true;
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
     * @param   bool    $random
     * @param   bool    $published
     * @return  bool    True on Success or False on failure
     */
    function InsertGroup($title, $view_mode, $view_type, $show_title, $limit_count, $random, $published)
    {
        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $gc = $table->select('count(id)')->where('title', $title)->fetchOne();
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_DUPLICATE_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $params['title']       = $title;
        $params['view_mode']   = $view_mode;
        $params['view_type']   = $view_type;
        $params['show_title']  = (bool)$show_title;
        $params['limit_count'] = ((empty($limit_count) || !is_numeric($limit_count))? 0 : $limit_count);
        $params['random']      = (bool)$random;
        $params['published']   = (bool)$published;

        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $res = $table->insert($params)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $response =  array();
        $response['id']      = $res;
        $response['title']   = $title;
        $response['message'] = _t('QUOTES_GROUPS_CREATED', $response['id']);

        $GLOBALS['app']->Session->PushLastResponse($response, RESPONSE_NOTICE);
        return true;
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
     * @param   bool    $random
     * @param   bool    $published
     * @return  bool    True on Success or False on failure
     */
    function UpdateGroup($gid, $title, $view_mode, $view_type, $show_title, $limit_count, $random, $published)
    {
        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $gc = $table->select('count(id)')->where('title', $title)->and()->where('id', $gid, '!=')->fetchOne();
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_DUPLICATE_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $params['title']       = $title;
        $params['view_mode']   = $view_mode;
        $params['view_type']   = $view_type;
        $params['show_title']  = (bool)$show_title;
        $params['limit_count'] = ((empty($limit_count) || !is_numeric($limit_count))? 0 : $limit_count);
        $params['random']      = (bool)$random;
        $params['published']   = (bool)$published;

        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $res = $table->update($params)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_UPDATED', $gid), RESPONSE_NOTICE);
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
        $AllQuotes = $this->GetQuotes(-1, -1);

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

    /**
     * Deletes the group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  bool   True on Success or False on failure
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_ERROR_GROUP_NOT_DELETABLE'), RESPONSE_ERROR);
            return false;
        }
        $group = $this->GetGroups($gid);
        if (Jaws_Error::IsError($group)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group[0]['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_GROUP_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $this->UpdateQuoteGroup(-1, $gid, 0);
        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $res = $table->delete()->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_DELETED', $gid), RESPONSE_NOTICE);

        return true;
    }

}