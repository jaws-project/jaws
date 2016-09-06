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
class Poll_Model_Poll extends Jaws_Gadget_Model
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
        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $table->update(array('votes' => $table->expr('votes + ?', 1)));
        $result = $table->where('id', $aid)->and()->where('poll', $pid)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('POLL_ERROR_VOTE_NOT_ADDED'));
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
        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $table->select('id:integer', 'title', 'order:integer', 'votes');
        $result = $table->where('poll', $pid)->orderBy('order asc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

    /**
     * Get Poll data
     *
     * @access  public
     * @param   int     $id    poll ID
     * @return  mixed   An array of poll properties and Jaws_Error on error
     */
    function GetPoll($id)
    {
        $table = Jaws_ORM::getInstance()->table('poll');
        $table->select(
            'id:integer', 'group:integer', 'title', 'type:integer', 'restriction:integer',
            'result:integer', 'start_time:integer', 'stop_time:integer', 'published:boolean');
        return $table->where('id', $id)->fetchRow();
    }

    /**
     * Gets the last published poll
     *
     * @access  public
     * @return  mixed   An array with the last published and returns Jaws_Error or false on error
     */
    function GetLastPoll()
    {
        $now = time();
        $table = Jaws_ORM::getInstance()->table('poll');
        $table->select(
                    'id:integer', 'group:integer', 'title', 'type:integer', 'restriction:integer',
                    'result:integer', 'start_time:integer', 'stop_time:integer', 'published:boolean');

        $table->where('published', true)->and();
        $table->openWhere()->where('start_time', '', 'is null')->or();
        $table->where('start_time', $now, '<=')->closeWhere()->and();
        $table->openWhere()->where('stop_time', '', 'is null')->or();
        $table->where('stop_time', $now, '>=')->closeWhere();
        return $table->orderBy('id')->fetchRow();
    }

    /**
     * Get the list of polls
     *
     * @access  public
     * @param   int     $group          group ID
     * @param   bool    $onlyPublished  only show published polls
     * @param   int     $limit          limit polls
     * @param   int     $offset         offset data by
     * @return  mixed   An array of available polls and Jaws_Error on error
     */
    function GetPolls($group = null, $onlyPublished = false, $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('poll');
        $table->select(
            'id', 'group', 'title', 'type:integer', 'restriction:integer',
            'result:integer', 'start_time:integer', 'stop_time:integer', 'published:integer');

        if ($onlyPublished) {
            $table->where('published', true);
        }

        if (!empty($group)) {
            $table->and()->where('group', $group);
        }

        if (!empty($limit)) {
            $table->limit($limit, $offset);
        }

        $result = $table->orderBy('id asc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

}