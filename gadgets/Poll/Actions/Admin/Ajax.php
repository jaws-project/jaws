<?php
/**
 * Poll AJAX API
 *
 * @category   Ajax
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Actions_Admin_Ajax extends Jaws_Gadget_Action
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
        @list($pid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Poll');
        $poll = $model->GetPoll($pid);
        if (Jaws_Error::IsError($poll)) {
            return false; //we need to handle errors on ajax
        }

        if (isset($poll['id'])) {
            $objDate = Jaws_Date::getInstance();
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
     * @internal param  string  $title          poll title
     * @internal param  int     $gid            group ID
     * @internal param  string  $start_time     poll start date time
     * @internal param  string  $stop_time      poll stop date time
     * @internal param  string  $type
     * @internal param  string  $restriction
     * @internal param  string  $result
     * @internal param  bool    $published
     * @return   array  Response array (notice or error)
     */
    function InsertPoll()
    {
        $this->gadget->CheckPermission('ManagePolls');
        @list($title, $gid, $start_time, $stop_time, $type,
              $restriction, $result, $published
        ) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Poll');
        $model->InsertPoll($title, $gid, $start_time, $stop_time, $type, $restriction, $result, $published);

        return $this->gadget->session->pop();
    }

    /**
     * Update a Poll
     *
     * @access   public
     * @internal param  int     $pid            poll ID
     * @internal param  string  $title          poll title
     * @internal param  int     $gid            group ID
     * @internal param  string  $start_time     poll start date time
     * @internal param  string  $stop_time      poll stop date time
     * @internal param  string  $type
     * @internal param  string  $restriction
     * @internal param  string  $result
     * @internal param  bool    $published
     * @return   array  Response array (notice or error)
     */
    function UpdatePoll()
    {
        $this->gadget->CheckPermission('ManagePolls');
        @list($pid, $title, $gid, $start_time, $stop_time,
             $type, $restriction, $result, $published
        ) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Poll');
        $model->UpdatePoll($pid, $title, $gid, $start_time, $stop_time, $type, $restriction, $result, $published);

        return $this->gadget->session->pop();
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
        @list($pid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Poll');
        $model->DeletePoll($pid);

        return $this->gadget->session->pop();
    }

    /**
     * Returns the poll answers form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollAnswersUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Poll');
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
        @list($pid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Poll');
        $answers = $model->GetPollAnswers($pid);
        if (Jaws_Error::IsError($answers)) {
            return false; //we need to handle errors on ajax
        }

        $poll = $this->GetPoll($pid);
        if ($poll == false) {
            return false;
        }

        return array('title'=>$poll['title'], 'Answers'=>$answers);
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
        @list($pid, $answers) = $this->gadget->request->fetchAll('post');
        $answers = $this->gadget->request->fetch('1:array', 'post');
        $model = $this->gadget->model->loadAdmin('Poll');
        $model->UpdatePollAnswers($pid, $answers);

        return $this->gadget->session->pop();
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
        @list($gid) = $this->gadget->request->fetchAll('post');
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
     * @internal param  bool    $published  published
     * @return   array  response array
     */
    function InsertPollGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($title, $published) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->InsertPollGroup($title, $published);

        return $this->gadget->session->pop();
    }

    /**
     * Update poll groups
     *
     * @access   public
     * @internal param  int     $gid        group ID
     * @internal param  string  $title      group title
     * @internal param  bool    $published  published
     * @return   array  response array
     */
    function UpdatePollGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid, $title, $published) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->UpdatePollGroup($gid, $title, $published);

        return $this->gadget->session->pop();
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
        @list($gid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->DeletePollGroup($gid);

        return $this->gadget->session->pop();
    }

    /**
     * Get the pollgroup-polls form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollGroupPollsUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Group');
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
        @list($gid) = $this->gadget->request->fetchAll('post');
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
        @list($gid, $polls) = $this->gadget->request->fetchAll('post');
        $polls = $this->gadget->request->fetch('1:array', 'post');
        $model = $this->gadget->model->loadAdmin('Poll');
        $model->AddPollsToPollGroup($gid, $polls);
        return $this->gadget->session->pop();
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
        @list($gid) = $this->gadget->request->fetchAll('post');
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
        @list($pid) = $this->gadget->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Report');
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
    function getData()
    {
        @list($offset, $grid) = $this->gadget->request->fetchAll('post');
        $pGadget = $this->gadget->action->loadAdmin('Poll');
        $gGadget = $this->gadget->action->loadAdmin('Group');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return ($grid == 'polls_datagrid')? $pGadget->GetPolls($offset) : $gGadget->GetPollGroups($offset);
    }

}