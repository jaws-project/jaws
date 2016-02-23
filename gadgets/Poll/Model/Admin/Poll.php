<?php
/**
 * Poll Gadget
 *
 * @category   GadgetModel
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Model_Admin_Poll extends Poll_Model_Poll
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
        $date = Jaws_Date::getInstance();
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
            return new Jaws_Error(_t('POLL_ERROR_POLL_NOT_ADDED'));
        }

        // shout Activity event
        $this->gadget->event->shout('Activities', array('action'=>'Poll'));

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
        $date = Jaws_Date::getInstance();
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
            return new Jaws_Error(_t('POLL_ERROR_POLL_NOT_UPDATED'));
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
            return new Jaws_Error(_t('POLL_ERROR_POLL_NOT_DELETED'));
        }

        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $res = $table->delete()->where('pid', $pid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_EXCEPTION_ANSWER_NOT_DELETED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_POLLS_DELETED'), RESPONSE_NOTICE);
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
        $model = $this->gadget->model->loadAdmin('Answer');
        $oldAnswers = $this->GetPollAnswers($pid);
        if (Jaws_Error::IsError($oldAnswers)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_ANSWERS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_ANSWERS_NOT_UPDATED'));
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
                $model->DeleteAnswer($oldAnswer['id']);
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
                $res = $model->UpdateAnswer($newAnswer['id'], $newAnswer['answer'], $index);
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_ANSWER_NOT_UPDATED'), RESPONSE_ERROR);
                    return false;
                }
            } else {
                $res = $model->InsertAnswer($pid, $newAnswer['answer'], $index);
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_ANSWER_NOT_ADDED'), RESPONSE_ERROR);
                    return false;
                }
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ANSWERS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

}