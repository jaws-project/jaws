<?php
/**
 * Poll AJAX API
 *
 * @category   Ajax
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Get a Poll
     *
     * @access  public
     * @param   int     $pid    poll ID
     * @return  mixed   Poll info array or False on error
     */
    function GetPoll($pid)
    {
        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model', 'Poll');
        $poll = $model->GetPoll($pid);
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
     * @param   string  $question       poll question
     * @param   int     $gid            group ID
     * @param   string  $start_time     poll start date time
     * @param   string  $stop_time      poll stop date time
     * @param   string  $select_type
     * @param   string  $poll_type
     * @param   string  $result_view
     * @param   bool    $visible        is visible
     * @return  array   Response array (notice or error)
     */
    function InsertPoll($question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible)
    {
        $this->gadget->CheckPermission('ManagePolls');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel', 'Poll');
        $model->InsertPoll($question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update a Poll
     *
     * @access  public
     * @param   int     $pid            poll ID
     * @param   string  $question       poll question
     * @param   int     $gid            group ID
     * @param   string  $start_time     poll start date time
     * @param   string  $stop_time      poll stop date time
     * @param   string  $select_type
     * @param   string  $poll_type
     * @param   string  $result_view
     * @param   bool    $visible        is visible
     * @return  array   Response array (notice or error)
     */
    function UpdatePoll($pid, $question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible)
    {
        $this->gadget->CheckPermission('ManagePolls');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel', 'Poll');
        $model->UpdatePoll($pid, $question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a poll
     *
     * @access  public
     * @param   int     $pid  Poll ID
     * @return  array   Response array (notice or error)
     */
    function DeletePoll($pid)
    {
        $this->gadget->CheckPermission('ManagePolls');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel', 'Poll');
        $model->DeletePoll($pid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns the poll answers form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollAnswersUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML', 'Poll');
        return $gadget->PollAnswersUI();
    }

    /**
     * Get a Poll Answers
     *
     * @access  public
     * @param   int     $pid    poll ID
     * @return  mixed   Response array (notice or error) or False on error
     */
    function GetPollAnswers($pid)
    {
        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model', 'Poll');
        $answers = $model->GetPollAnswers($pid);
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
     * @param   int     $pid        poll ID
     * @param   array   $answers    poll answers array
     * @return  array   Response array (notice or error)
     */
    function UpdatePollAnswers($pid, $answers)
    {
        $this->gadget->CheckPermission('ManagePolls');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel', 'Poll');
        $model->UpdatePollAnswers($pid, $answers);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get a list of poll groups
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   Poll Groups list or False on error
     */
    function GetPollGroup($gid)
    {
        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model', 'Group');
        $group = $model->GetPollGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }

    /**
     * Insert poll groups
     *
     * @access  public
     * @param   string  $title      group title
     * @param   bool    $visible    is visible
     * @return  array   response array
     */
    function InsertPollGroup($title, $visible)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel', 'Group');
        $model->InsertPollGroup($title, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update poll groups
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   string  $title      group title
     * @param   bool    $visible    is visible
     * @return  array   response array
     */
    function UpdatePollGroup($gid, $title, $visible)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel', 'Group');
        $model->UpdatePollGroup($gid, $title, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an poll group
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  array   Response array (notice or error)
     */
    function DeletePollGroup($gid)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel', 'Group');
        $model->DeletePollGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get the pollgroup-polls form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollGroupPollsUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML', 'Group');
        return $gadget->PollGroupPollsUI();
    }

    /**
     * Get a list of polls
     *
     * @access  public
     * @param   int     $gid       group ID
     * @return  mixed   response array or false on error
     */
    function GetPollGroupPolls($gid)
    {
        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model', 'Poll');
        $polls = $model->GetPolls($gid);
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
     * @param   int     $gid    PollGroup's ID
     * @param   array   $Poll   Array with poll ids
     * @return  array   Response array (notice or error)
     */
    function AddPollsToPollGroup($gid, $polls)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel', 'Poll');
        $model->AddPollsToPollGroup($gid, $polls);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Retrieves Group Polls
     *
     * @access  public
     * @param   int     $gid        group ID
     * @return  mixed   array of Polls or false on error
     */
    function GetGroupPolls($gid)
    {
        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model', 'Poll');
        $polls = $model->GetPolls($gid);
        if (Jaws_Error::IsError($polls)) {
            return false; //we need to handle errors on ajax
        }

        return $polls;
    }

    /**
     * Get the poll results
     *
     * @access  public
     * @param   int     $pid    poll ID
     * @return  string  XHTML template content
     */
    function PollResultsUI($pid)
    {
        $this->gadget->CheckPermission('ViewReports');
        $gadget = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML', 'Report');
        return $gadget->PollResultsUI($pid);
    }

    /**
     * Prepare the datagrid of polls
     *
     * @access  public
     * @param   int     $offset     date offset
     * @param   int     $grid       gid
     * @return  string  The XHTML of a datagrid
     */
    function GetData($offset, $grid)
    {
        $pGadget = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML', 'Poll');
        $gGadget = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML', 'Group');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return ($grid == 'polls_datagrid')? $pGadget->GetPolls($offset) : $gGadget->GetPollGroups($offset);
    }

}