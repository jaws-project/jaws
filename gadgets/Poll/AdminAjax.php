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
class Poll_AdminAjax extends Jaws_Gadget_Action
{
    /**
     * Get a Poll
     *
     * @access   public
     * @internal param  int     $pid    poll ID
     * @return   mixed  Poll info array or False on error
     */
    function GetPoll()
    {
        @list($pid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Poll');
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
     * @access   public
     * @internal param  string  $question       poll question
     * @internal param  int     $gid            group ID
     * @internal param  string  $start_time     poll start date time
     * @internal param  string  $stop_time      poll stop date time
     * @internal param  string  $select_type
     * @internal param  string  $poll_type
     * @internal param  string  $result_view
     * @internal param  bool    $visible        is visible
     * @return   array  Response array (notice or error)
     */
    function InsertPoll()
    {
        $this->gadget->CheckPermission('ManagePolls');
        @list($question, $gid, $start_time, $stop_time, $select_type,
              $poll_type, $result_view, $visible
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Poll');
        $model->InsertPoll($question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update a Poll
     *
     * @access   public
     * @internal param  int     $pid            poll ID
     * @internal param  string  $question       poll question
     * @internal param  int     $gid            group ID
     * @internal param  string  $start_time     poll start date time
     * @internal param  string  $stop_time      poll stop date time
     * @internal param  string  $select_type
     * @internal param  string  $poll_type
     * @internal param  string  $result_view
     * @internal param  bool    $visible        is visible
     * @return   array  Response array (notice or error)
     */
    function UpdatePoll()
    {
        $this->gadget->CheckPermission('ManagePolls');
        @list($pid, $question, $gid, $start_time, $stop_time,
             $select_type, $poll_type, $result_view, $visible
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Poll');
        $model->UpdatePoll($pid, $question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a poll
     *
     * @access   public
     * @internal param  int     $pid    Poll ID
     * @return   array  Response array (notice or error)
     */
    function DeletePoll()
    {
        $this->gadget->CheckPermission('ManagePolls');
        @list($pid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Poll');
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
        $gadget = $this->gadget->loadAdminAction('Poll');
        return $gadget->PollAnswersUI();
    }

    /**
     * Get a Poll Answers
     *
     * @access   public
     * @internal param  int     $pid    poll ID
     * @return   mixed  Response array (notice or error) or False on error
     */
    function GetPollAnswers()
    {
        @list($pid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Poll');
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
     * @access   public
     * @internal param  int     $pid        poll ID
     * @internal param  array   $answers    poll answers array
     * @return   array  Response array (notice or error)
     */
    function UpdatePollAnswers()
    {
        $this->gadget->CheckPermission('ManagePolls');
        @list($pid, $answers) = jaws()->request->fetchAll('post');
        $answers = jaws()->request->fetch('1:array', 'post');
        $model = $this->gadget->model->loadAdmin('Poll');
        $model->UpdatePollAnswers($pid, $answers);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get a list of poll groups
     *
     * @access   public
     * @internal param  int     $gid    group ID
     * @return   mixed  Poll Groups list or False on error
     */
    function GetPollGroup()
    {
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Group');
        $group = $model->GetPollGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }

    /**
     * Insert poll groups
     *
     * @access   public
     * @internal param  string  $title      group title
     * @internal param  bool    $visible    is visible
     * @return   array  response array
     */
    function InsertPollGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($title, $visible) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->InsertPollGroup($title, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update poll groups
     *
     * @access   public
     * @internal param  int     $gid        group ID
     * @internal param  string  $title      group title
     * @internal param  bool    $visible    is visible
     * @return   array  response array
     */
    function UpdatePollGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid, $title, $visible) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->UpdatePollGroup($gid, $title, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an poll group
     *
     * @access   public
     * @internal param  int     $gid    group ID
     * @return   array  Response array (notice or error)
     */
    function DeletePollGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
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
        $gadget = $this->gadget->loadAdminAction('Group');
        return $gadget->PollGroupPollsUI();
    }

    /**
     * Get a list of polls
     *
     * @access   public
     * @internal param  int     $gid    group ID
     * @return   mixed  response array or false on error
     */
    function GetPollGroupPolls()
    {
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Poll');
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
     * @access   public
     * @internal param  int     $gid    PollGroup's ID
     * @internal param  array   $Poll   Array with poll ids
     * @return   array  Response array (notice or error)
     */
    function AddPollsToPollGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid, $polls) = jaws()->request->fetchAll('post');
        $polls = jaws()->request->fetch('1:array', 'post');
        $model = $this->gadget->model->loadAdmin('Poll');
        $model->AddPollsToPollGroup($gid, $polls);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Retrieves Group Polls
     *
     * @access   public
     * @internal param  int     $gid    group ID
     * @return   mixed  array of Polls or false on error
     */
    function GetGroupPolls()
    {
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Poll');
        $polls = $model->GetPolls($gid);
        if (Jaws_Error::IsError($polls)) {
            return false; //we need to handle errors on ajax
        }

        return $polls;
    }

    /**
     * Get the poll results
     *
     * @access   public
     * @internal param  int     $pid    poll ID
     * @return   string XHTML template content
     */
    function PollResultsUI()
    {
        $this->gadget->CheckPermission('ViewReports');
        @list($pid) = jaws()->request->fetchAll('post');
        $gadget = $this->gadget->loadAdminAction('Report');
        return $gadget->PollResultsUI($pid);
    }

    /**
     * Prepare the datagrid of polls
     *
     * @access   public
     * @internal param  int     $offset     date offset
     * @internal param  int     $grid       gid
     * @return   string The XHTML of a datagrid
     */
    function GetData()
    {
        @list($offset, $grid) = jaws()->request->fetchAll('post');
        $pGadget = $this->gadget->loadAdminAction('Poll');
        $gGadget = $this->gadget->loadAdminAction('Group');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return ($grid == 'polls_datagrid')? $pGadget->GetPolls($offset) : $gGadget->GetPollGroups($offset);
    }

}