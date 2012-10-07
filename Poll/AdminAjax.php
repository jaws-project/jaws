<?php
/**
 * Poll AJAX API
 *
 * @category   Ajax
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PollAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     */
    function PollAdminAjax(&$model)
    {
        $this->_Model  =& $model;
    }

    /**
     * Get a Poll
     *
     * @access  public
     * @return  array   Poll info
     */
    function GetPoll($pid)
    {
        $poll = $this->_Model->GetPoll($pid);
        if (Jaws_Error::IsError($poll)) {
            return false; //we need to handle errors on ajax
        }

        if (isset($poll['id'])) {
            $objDate = $GLOBALS['app']->loadDate();
            if (!empty($poll['start_time'])) {
                $poll['start_time'] = $objDate->Format($poll['start_time'], 'Y-m-d H:i:s');
            }
            if (!empty($poll['stop_time'])) {
                $poll['stop_time'] = $objDate->Format($poll['stop_time'], 'Y-m-d H:i:s');
            }
        }

        return $poll;
    }

    /**
     * Insert a Poll
     *
     * @access  public
     * @return  array   Response (notice or error)
     */
    function InsertPoll($question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible)
    {
        $this->CheckSession('Poll', 'ManagePolls');
        $this->_Model->InsertPoll($question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update a Poll
     *
     * @access  public
     * @return  array   Response (notice or error)
     */
    function UpdatePoll($pid, $question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible)
    {
        $this->CheckSession('Poll', 'ManagePolls');
        $this->_Model->UpdatePoll($pid, $question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a poll
     *
     * @access  public
     * @param   int     $pid  Poll ID
     * @return  array   Response (notice or error)
     */
    function DeletePoll($pid)
    {
        $this->CheckSession('Poll', 'ManagePolls');
        $this->_Model->DeletePoll($pid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns the poll answers form
     *
     * @access  public
     * @return  string  XHTML
     */
    function PollAnswersUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML');
        return $gadget->PollAnswersUI();
    }

    /**
     * Get a Poll Answers
     *
     * @access  public
     * @return  array   Response (notice or error)
     */
    function GetPollAnswers($pid)
    {
        $answers = $this->_Model->GetPollAnswers($pid);
        if (Jaws_Error::IsError($answers)) {
            return false; //we need to handle errors on ajax
        }

        $poll = $this->GetPoll($pid);
        if ($poll == false) {
            return false;
        }

        return array('question'=>$poll['question'], 'Answers'=>$answers);
    }

    /**
     * Update a Poll Answers
     *
     * @access  public
     * @return  array   Response (notice or error)
     */
    function UpdatePollAnswers($pid, $answers)
    {
        $this->CheckSession('Poll', 'ManagePolls');
        $this->_Model->UpdatePollAnswers($pid, $answers);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get a list of poll groups
     *
     * @access  public
     * @return  array   Poll Groups list
     */
    function GetPollGroup($gid)
    {
        $group = $this->_Model->GetPollGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }

    /**
     * Insert poll groups
     *
     * @access  public
     * @return  array   Poll list
     */
    function InsertPollGroup($title, $visible)
    {
        $this->CheckSession('Poll', 'ManageGroups');
        $this->_Model->InsertPollGroup($title, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update poll groups
     *
     * @access  public
     * @return  array   Poll list
     */
    function UpdatePollGroup($gid, $title, $visible)
    {
        $this->CheckSession('Poll', 'ManageGroups');
        $this->_Model->UpdatePollGroup($gid, $title, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an poll group
     *
     * @access  public
     * @param   int     $gid   group ID
     * @return  array   Response (notice or error)
     */
    function DeletePollGroup($gid)
    {
        $this->CheckSession('Poll', 'ManageGroups');
        $this->_Model->DeletePollGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get the pollgroup-polls form
     *
     * @access  public
     * @return  string
     */
    function PollGroupPollsUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML');
        return $gadget->PollGroupPollsUI();
    }

    /**
     * Get a list of polls
     *
     * @access  public
     * @param   integer  $gid
     * @return  array   Polls list
     */
    function GetPollGroupPolls($gid)
    {
        $polls = $this->_Model->GetPolls($gid);
        if (Jaws_Error::IsError($polls)) {
            return false; //we need to handle errors on ajax
        }

        $pollGroup = $this->GetPollGroup($gid);
        if ($pollGroup == false) {
            return false; //we need to handle errors on ajax
        }

        return array('title'=>$pollGroup['title'], 'Polls'=>$polls);
    }

    /**
     * Add a group of Poll (by they ids) to a certain poll group
     *
     * @access  public
     * @param   int     $gid  PollGroup's ID
     * @param   array   $Poll Array with poll id
     * @return  array   Response (notice or error)
     */
    function AddPollsToPollGroup($gid, $polls)
    {
        $this->CheckSession('Poll', 'ManageGroups');
        $res = $this->_Model->AddPollsToPollGroup($gid, $polls);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    function GetGroupPolls($gid)
    {
        $polls = $this->_Model->GetPolls($gid);
        if (Jaws_Error::IsError($polls)) {
            return false; //we need to handle errors on ajax
        }

        return $polls;
    }

    /**
     * Get the poll results
     *
     * @access  public
     * @return  string
     */
    function PollResultsUI($pid)
    {
        $this->CheckSession('Poll', 'ViewReports');
        $gadget = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML');
        return $gadget->PollResultsUI($pid);
    }

    /**
     * Prepare the datagrid of polls
     *
     * @access  public
     * @return  string  The XHTML of a datagrid
     */
    function GetData($offset, $grid)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return ($grid == 'polls_datagrid')? $gadget->GetPolls($offset) : $gadget->GetPollGroups($offset);
    }
}