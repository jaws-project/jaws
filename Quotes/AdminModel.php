<?php
/**
 * Quotes Gadget
 *
 * @category    GadgetModel
 * @package     Quotes
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Quotes/Model.php';

class QuotesAdminModel extends QuotesModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', null, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $$this->AddRegistry('last_entries_limit',       '10');
        $$this->AddRegistry('last_entries_view_mode',   '0');
        $$this->AddRegistry('last_entries_view_type',   '0');
        $$this->AddRegistry('last_entries_show_title',  'true');
        $$this->AddRegistry('last_entries_view_random', 'false');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function UninstallGadget()
    {
        $tables = array('quotes',
                        'quotes_groups');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('QUOTES_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $this->DelRegistry('last_entries_limit');
        $this->DelRegistry('last_entries_view_mode');
        $this->DelRegistry('last_entries_view_type');
        $this->DelRegistry('last_entries_show_title');
        $this->DelRegistry('last_entries_view_random');

        return true;
    }

    /**
     * Updates the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function UpdateGadget($old, $new)
    {
        $result = $this->installSchema('schema.xml', '', "0.1.0.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

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
        $sql = '
            INSERT INTO [[quotes]]
                ([title], [quotation], [gid], [start_time], [stop_time],
                 [createtime], [updatetime], [show_title], [published])
            VALUES
                ({title}, {quotation}, {gid}, {start_time}, {stop_time},
                 {now}, {now}, {show_title}, {published})';

        $date = $GLOBALS['app']->loadDate();
        $params = array();
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

        $params['now']         = $GLOBALS['db']->Date();
        $params['show_title']  = (bool)$show_title;
        $params['published']   = (bool)$published;

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('QUOTES_QUOTE_NOT_ADDED'),_t('QUOTES_NAME'));
        }

        $response =  array();
        $response['id']      = $GLOBALS['db']->lastInsertID('quotes', 'id');
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
        $sql = '
            UPDATE [[quotes]] SET
                [title]       = {title},
                [quotation]   = {quotation},
                [gid]         = {gid},
                [start_time]  = {start_time},
                [stop_time]   = {stop_time},
                [updatetime]  = {now},
                [show_title]  = {show_title},
                [published]   = {published}
            WHERE [id] = {id}';

        $date = $GLOBALS['app']->loadDate();
        $params = array();
        $params['id']          = (int)$id;
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

        $params['now']         = $GLOBALS['db']->Date();
        $params['show_title']  = (bool)$show_title;
        $params['published']   = (bool)$published;

        $result = $GLOBALS['db']->query($sql, $params);
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
        $sql = 'DELETE FROM [[quotes]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
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
        $sql = 'SELECT COUNT([id]) FROM [[quotes_groups]] WHERE [title] = {title}';
        $gc = $GLOBALS['db']->queryOne($sql, array('title' => $title));
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_DUPLICATE_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $sql = '
            INSERT INTO [[quotes_groups]]
                ([title], [view_mode], [view_type], [show_title], [limit_count], [random], [published])
            VALUES
                ({title}, {view_mode}, {view_type}, {show_title}, {limit_count}, {random}, {published})';

        $params = array();
        $params['title']       = $title;
        $params['view_mode']   = $view_mode;
        $params['view_type']   = $view_type;
        $params['show_title']  = (bool)$show_title;
        $params['limit_count'] = ((empty($limit_count) || !is_numeric($limit_count))? 0 : $limit_count);
        $params['random']      = (bool)$random;
        $params['published']   = (bool)$published;
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $response =  array();
        $response['id']      = $GLOBALS['db']->lastInsertID('quotes_groups', 'id');
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
        $sql = 'SELECT COUNT([id]) FROM [[quotes_groups]] WHERE [id] != {gid} AND [title] = {title}';
        $gc = $GLOBALS['db']->queryOne($sql, array('gid' => $gid, 'title' => $title));
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_DUPLICATE_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $sql = '
            UPDATE [[quotes_groups]] SET
                [title]       = {title},
                [view_mode]   = {view_mode},
                [view_type]   = {view_type},
                [show_title]  = {show_title},
                [limit_count] = {limit_count},
                [random]      = {random},
                [published]   = {published}
            WHERE [id] = {id}';

        $params = array();
        $params['id']          = $gid;
        $params['title']       = $title;
        $params['view_mode']   = $view_mode;
        $params['view_type']   = $view_type;
        $params['show_title']  = (bool)$show_title;
        $params['limit_count'] = ((empty($limit_count) || !is_numeric($limit_count))? 0 : $limit_count);
        $params['random']      = (bool)$random;
        $params['published']   = (bool)$published;
        $res = $GLOBALS['db']->query($sql, $params);
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
        if (($qid != -1) && ($gid != -1)) {
            $sql = '
                UPDATE [[quotes]] SET
                [gid] = {new_gid}
                WHERE [[quotes]].[id] = {qid} AND [[quotes]].[gid] = {gid}';
        } elseif ($gid != -1) {
            $sql = '
                UPDATE [[quotes]] SET
                [gid] = {new_gid}
                WHERE [[quotes]].[gid] = {gid}';
        } elseif ($qid != -1) {
            $sql = '
                UPDATE [[quotes]] SET
                [gid] = {new_gid}
                WHERE [id] = {qid}';
        } else {
            $sql = '
                UPDATE [[quotes]] SET
                [gid] = {new_gid}';
        }

        $params = array();
        $params['qid']     = $qid;
        $params['gid']     = $gid;
        $params['new_gid'] = $new_gid;

        $result = $GLOBALS['db']->query($sql, $params);
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
        $sql = 'DELETE FROM [[quotes_groups]] WHERE [id] = {gid}';
        $res = $GLOBALS['db']->query($sql, array('gid' => $gid));
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_DELETED', $gid), RESPONSE_NOTICE);

        return true;
    }

}