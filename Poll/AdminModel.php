<?php
require_once JAWS_PATH . 'gadgets/Poll/Model.php';
/**
 * Poll Gadget
 *
 * @category   GadgetModel
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_AdminModel extends Poll_Model
{
    /**
     * Insert a Poll
     *
     * @access  public
     * @param   string  $question       poll question
     * @param   int     $gid            group ID
     * @param   string  $start_time     start date time
     * @param   string  $stop_time      stop date time
     * @param   string  $select_type
     * @param   string  $poll_type
     * @param   string  $result_view
     * @param   bool    $visible
     * @return  mixed   Response array (notice or error) or Jaws_Error on failure
     */
    function InsertPoll($question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible)
    {
        $date = $GLOBALS['app']->loadDate();
        $pollData = array();
        $pollData['question'] = $question;
        $pollData['gid'] = $gid;
        $pollData['start_time'] = null;
        $pollData['stop_time'] = null;
        if (!empty($start_time)) {
            $start_time = $date->ToBaseDate(preg_split('/[- :]/', $start_time), 'Y-m-d H:i:s');
            $pollData['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time,  'Y-m-d H:i:s');
        }
        if (!empty($stop_time)) {
            $stop_time  = $date->ToBaseDate(preg_split('/[- :]/', $stop_time), 'Y-m-d H:i:s');
            $pollData['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time,   'Y-m-d H:i:s');
        }
        $pollData['select_type'] = $select_type;
        $pollData['poll_type'] = $poll_type;
        $pollData['result_view'] = $result_view;
        $pollData['visible'] = $visible;

        $table = Jaws_ORM::getInstance()->table('poll');
        $result = $table->insert($pollData)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_POLL_NOT_ADDED'), _t('POLL_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_POLLS_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the question of a poll
     *
     * @access  public
     * @param   int     $pid        Poll's ID
     * @param   string  $question   Poll's Question
     * @param   int     $gid        group ID
     * @param   string  $start_time     start date time
     * @param   string  $stop_time      stop date time
     * @param   string  $select_type
     * @param   string  $poll_type
     * @param   string  $result_view
     * @param   bool    $visible
     * @return  mixed   True if the poll was updated and Jaws_Error on error
     */
    function UpdatePoll($pid, $question, $gid, $start_time, $stop_time, $select_type, $poll_type, $result_view, $visible)
    {
        $date = $GLOBALS['app']->loadDate();
        $pollData = array();
        $pollData['question'] = $question;
        $pollData['gid'] = $gid;
        $pollData['start_time'] = null;
        $pollData['stop_time'] = null;
        if (!empty($start_time)) {
            $start_time = $date->ToBaseDate(preg_split('/[- :]/', $start_time), 'Y-m-d H:i:s');
            $pollData['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time,  'Y-m-d H:i:s');
        }
        if (!empty($stop_time)) {
            $stop_time  = $date->ToBaseDate(preg_split('/[- :]/', $stop_time), 'Y-m-d H:i:s');
            $pollData['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time,   'Y-m-d H:i:s');
        }
        $pollData['select_type'] = $select_type;
        $pollData['poll_type'] = $poll_type;
        $pollData['result_view'] = $result_view;
        $pollData['visible'] = $visible;

        $table = Jaws_ORM::getInstance()->table('poll');
        $result = $table->update($pollData)->where('id', (int)$pid)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_POLL_NOT_UPDATED'), _t('POLL_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_POLLS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes a poll
     *
     * @access  public
     * @param   int     $pid    Poll's ID
     * @return  mixed   True if the poll was deleted and Jaws_Error on error
     */
    function DeletePoll($pid)
    {
        $table = Jaws_ORM::getInstance()->table('poll');
        $res = $table->delete()->where('id', $pid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_POLL_NOT_DELETED'), _t('POLL_NAME'));
        }

        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $res = $table->delete()->where('pid', $pid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_EXCEPTION_ANSWER_NOT_DELETED'), _t('POLL_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_POLLS_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update a Poll Answers
     *
     * @access  public
     * @param   int     $pid    poll ID
     * @param   array   $answers
     * @return  bool    Response array (notice or error) or False on error
     */
    function UpdatePollAnswers($pid, $answers)
    {
        $oldAnswers = $this->GetPollAnswers($pid);
        if (Jaws_Error::IsError($oldAnswers)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_ANSWERS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_ANSWERS_NOT_UPDATED'), _t('POLL_NAME'));
        }

        foreach ($oldAnswers as $oldAnswer) {
            $found = false;
            foreach ($answers as $newAnswer) {
                if ($oldAnswer['id'] == $newAnswer['id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->DeleteAnswer($oldAnswer['id']);
            }
        }

        //-- for adding new answers and update old answers
        foreach ($answers as $index => $newAnswer) {
            $found = false;
            foreach ($oldAnswers as $oldAnswer) {
                if ($newAnswer['id'] == $oldAnswer['id']) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $res = $this->UpdateAnswer($newAnswer['id'], $newAnswer['answer'], $index);
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_ANSWER_NOT_UPDATED'), RESPONSE_ERROR);
                    return false;
                }
            } else {
                $res = $this->InsertAnswer($pid, $newAnswer['answer'], $index);
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_ANSWER_NOT_ADDED'), RESPONSE_ERROR);
                    return false;
                }
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ANSWERS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Insert a new answer
     *
     * @access  public
     * @param   int     $pid        Poll's ID
     * @param   string  $answer     Answer
     * @param   string  $rank       
     * @return  mixed   True if the answer was created and Jaws_Error on error
     */
    function InsertAnswer($pid, $answer, $rank)
    {
        $data = array();
        $data['pid'] = $pid;
        $data['answer'] = $answer;
        $data['rank'] = (int)$rank;

        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('POLL_ERROR_ANSWER_NOT_ADDED'), _t('POLL_NAME'));
        }

        return true;
    }

    /**
     * Updates the answer
     *
     * @access  public
     * @param   string  $aid        Answer's Question
     * @param   int     $answer     Answer's ID
     * @param   string  $rank       
     * @return  mixed   True if the answer was updated and Jaws_Error on error
     */
    function UpdateAnswer($aid, $answer, $rank)
    {
        $data = array();
        $data['answer'] = $answer;
        $data['rank'] = (int)$rank;

        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $result = $table->update($data)->where('id', $aid)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('POLL_ERROR_ANSWER_NOT_UPDATED'), _t('POLL_NAME'));
        }

        return true;
    }

    /**
     * Deletes an answer
     *
     * @access  public
     * @param   int     $aid    Answer's ID
     * @return  mixed   True if the answer was deleted and Jaws_Error on error
     */
    function DeleteAnswer($aid)
    {
        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $result = $table->delete()->where('id', $aid)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_ANSWER_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_ANSWER_NOT_DELETED'), _t('POLL_NAME'));
        }

        return true;
    }

    /**
    * Insert a poll group
    *
    * @access  public
    * @param    string  $title      group title
    * @param    bool    $visible    is visible
    * @return   bool    True on Success or False Failure
    */
    function InsertPollGroup($title, $visible)
    {
        $table = Jaws_ORM::getInstance()->table('poll_groups');
        $count = $table->select('COUNT([id])')->where('title', $title)->getOne();
        if (Jaws_Error::IsError($count)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($count > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_GROUP_TITLE_DUPLICATE'), RESPONSE_ERROR);
            return false;
        }

        $data = array();
        $data['title']   = $title;
        $data['visible'] = $visible;
        $table->reset();
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_GROUP_NOT_ADDED'), _t('POLL_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_GROUPS_CREATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
    * Update a poll group
    * 
    * @access  public
    * @param    int     $gid        group ID
    * @param    string  $title      group title
    * @param    bool    $visible    is visible
    * @return   mixed   True on Success, Jaws_Error or False on Failure
    */
    function UpdatePollGroup($gid, $title, $visible)
    {
        $table = Jaws_ORM::getInstance()->table('poll_groups');
        $count = $table->select('COUNT([id])')
            ->where('id', $gid, '!=')->and()
            ->where('title', $title)->getOne();
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_GROUP_TITLE_DUPLICATE'), RESPONSE_ERROR);
            return false;
        }

        $data = array();
        $data['title'] = $title;
        $data['visible'] = $visible;
        $table->reset();
        $result = $table->update($data)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_GROUP_NOT_UPDATED'), _t('POLL_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_GROUPS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a poll group
     *
     * @access  public
     * @param   int     $gid    The poll group that will be deleted
     * @return  mixed   True if query was successful and Jaws_Error or False on error
     */
    function DeletePollGroup($gid)
    {
        if ($gid == 1) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_GROUP_NOT_DELETED'), RESPONSE_ERROR);
            return false;
        }

        $group = $this->GetPollGroup($gid);
        if (Jaws_Error::IsError($group)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_GROUP_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $this->UpdateGroupsOfPolls(-1, $gid, 0);

        $table = Jaws_ORM::getInstance()->table('poll_groups');
        $result = $table->delete()->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_GROUP_NOT_DELETED'), _t('POLL_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_GROUPS_DELETED', $gid), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Add a group of poll (by they ids) to a certain poll group
     *
     * @access  public
     * @param   int     $gid    PollGroup's ID
     * @param   array   $polls  Array with poll id
     * @return  bool    True always
     */
    function AddPollsToPollGroup($gid, $polls)
    {
        $AllPolls = $this->GetPolls();
        foreach ($AllPolls as $poll) {
            if ($poll['gid'] == $gid) {
                if (!in_array($poll['id'], $polls)) {
                    $this->UpdateGroupsOfPolls($poll['id'], -1, 0);
                }
            } else {
                if (in_array($poll['id'], $polls)) {
                    $this->UpdateGroupsOfPolls($poll['id'], -1, $gid);
                }
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_GROUPS_UPDATED_POLLS'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Adds a poll to a group
     *
     * @access  public
     * @param   int     $pid        Poll's ID
     * @param   int     $gid        PollGroup's ID
     * @param   int     $new_gid    PollGroup's ID
     * @return  bool    True if poll was sucessfully added to the group, false if not
     */
    function UpdateGroupsOfPolls($pid, $gid, $new_gid)
    {
        $table = Jaws_ORM::getInstance()->table('poll');
        $table->update(array('gid' => $new_gid));
        if (($pid != -1) && ($gid != -1)) {
            $table->where('id', $pid)->and()->where('gid', $gid);
        } elseif ($gid != -1) {
            $table->where('gid', $gid);
        } elseif ($pid != -1) {
            $table->where('id', $pid);
        }
        $result = $table->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

}