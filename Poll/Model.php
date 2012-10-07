<?php
/**
 * Poll Gadget
 *
 * @category   GadgetModel
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PollModel extends Jaws_Model
{
    /**
     * Add a new vote to the poll's answer
     *
     * @access  public
     * @param   int     $pid    Poll's ID
     * @param   int     $aid    Answer's ID
     * @return  mixed   True if the poll answer was incremented and Jaws_Error on error
     */
    function AddAnswerVote($pid, $aid)
    {
        $sql = '
            UPDATE [[poll_answers]] SET
                [votes] = [votes] + 1
            WHERE [id] = {aid} AND [pid] = {pid}';

        $params        = array();
        $params['pid'] = $pid;
        $params['aid'] = $aid;

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('POLL_ERROR_VOTE_NOT_ADDED'), _t('POLL_NAME'));
        }

        return true;
    }

    /**
     * Get Poll Answers
     *
     * @access  public
     * @param   int     $pid    Poll's ID
     * @return  mixed   An array with the information of the answer and Jaws_Error on error
     */
    function GetPollAnswers($pid)
    {
        $sql = "
            SELECT [id], [answer], [votes]
            FROM [[poll_answers]]
            WHERE [pid] = {pid}
            ORDER BY [rank] ASC";
        $result = $GLOBALS['db']->queryAll($sql, array('pid' => $pid));
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get Poll data
     *
     * @access  public
     * @param   int     $pid    poll ID
     * @return  mixed   An array of poll properties and Jaws_Error on error
     */
    function GetPoll($pid)
    {
        $sql = '
            SELECT  [id], [gid], [question], [select_type], [poll_type], [result_view],
                    [start_time], [stop_time], [visible]
            FROM [[poll]]
            WHERE [id] = {pid}';

        $result = $GLOBALS['db']->queryRow($sql, array('pid' => $pid));
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Gets the last visible poll
     *
     * @access  public
     * @return  mixed   An array with the last visible and returns Jaws_Error or false on error
     */
    function GetLastPoll()
    {
        $sql = '
            SELECT MAX([id])
            FROM [[poll]]
            WHERE ([visible] = {visible}) AND
                  (([start_time] IS NULL) OR ({now} >= [start_time])) AND
                  (([stop_time] IS NULL) OR ({now} <= [stop_time]))';

        $params = array();
        $params['now']     = $GLOBALS['db']->Date();
        $params['visible'] = 1;
        $max = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($max)) {
            return new Jaws_Error($max->getMessage(), 'SQL');
        }

        if ($max > 0) {
            return $this->GetPoll($max);
        }

        return false;
    }

    /**
     * Get the list of polls
     *
     * @access  public
     * @param   int     $gid            group ID
     * @param   bool    $onlyVisible    only show visible polls
     * @param   int     $limit          limit polls
     * @param   int     $offset         offset data by
     * @return  mixed   An array of available polls and Jaws_Error on error
     */
    function GetPolls($gid = null, $onlyVisible = false, $limit = 0, $offset = null)
    {
        $sql = '
                SELECT  [id], [gid], [question], [select_type], [poll_type], [result_view],
                        [start_time], [stop_time], [visible]';

        if (!empty($gid)) {
            $sql .= '
                FROM [[poll]]
                WHERE [[poll]].[gid] = {gid}'.($onlyVisible?' AND [visible] = {visible} ':' ').'ORDER BY [[poll]].[id] ASC';
        } else {
            $sql .= '
                FROM [[poll]]'.($onlyVisible?' WHERE [visible] = {visible} ':' ').'ORDER BY [id] ASC';
        }

        $params            = array();
        $params['gid']     = $gid;
        $params['visible'] = 1;

        if (!empty($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Retrieve information of a group
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   An array of group's data and Jaws_Error on error
     */
    function GetPollGroup($gid)
    {
        $sql = '
            SELECT
                [id], [title], [visible]
            FROM [[poll_groups]]
            WHERE
                [id] = {gid}';

        $params        = array();
        $params['gid'] = $gid;

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }    

    /**
     * Retrieve poll groups
     *
     * @access  public
     * @param   int     $limit  limit groups
     * @param   int     $offset offset groups
     * @return  mixed   An array of available poll groups and Jaws_Error on error
     */
    function GetPollGroups($limit = 0, $offset = null)
    {
        $sql = '
            SELECT
                [id], [title], [visible]
            FROM [[poll_groups]]
            ORDER BY
                [id] ASC';

        if (!empty($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }    

    /**
     * Get the list of visible polls
     *
     * @access  public
     * @return  mixed   An array with the visible polls and returns Jaws_Error on error
     */
    function GetEnabledPolls()
    {
        $params = array();
        $params['visible'] = 1;
        $sql = "
            SELECT
                [id], [question], [visible], [create_time]
            FROM [[poll]]
            WHERE [visible] = {visible}
            ORDER BY [create_time] DESC";

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }
}