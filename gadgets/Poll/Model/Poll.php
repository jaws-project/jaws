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
     * @param   int     $pid        Poll's ID
     * @param   array   $answers    Answer's IDs
     * @return  mixed   True if the poll answer was incremented and Jaws_Error on error
     */
    function AddAnswerVotes($pid, $answers)
    {
        $objORM = Jaws_ORM::getInstance();
        // begin transaction
        $objORM->beginTransaction();

        // insert user votes
        $data = array(
            'poll' => $pid,
            'votes' => implode(',', $answers),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user' => $GLOBALS['app']->Session->GetAttribute('user'),
            'session' => '',
            'insert_time' => time(),
        );
        $pResults = $objORM->table('poll_results')
        ->insert($data)->exec();
        if(Jaws_Error::IsError($pResults)) {
            return new Jaws_Error(_t('POLL_ERROR_VOTE_NOT_ADDED'));
        }

        // update total_votes
        $table = $objORM->table('poll');
        $result = $table->update(array('total_votes' => $table->expr('total_votes + ?', 1)))->where('id', $pid)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('POLL_ERROR_VOTE_NOT_ADDED'));
        }

        // update poll_answers votes
        foreach ($answers as $aid) {
            $table = $objORM->table('poll_answers');
            $table->update(array('votes' => $table->expr('votes + ?', 1)));
            $result = $table->where('id', $aid)->and()->where('poll', $pid)->exec();
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error(_t('POLL_ERROR_VOTE_NOT_ADDED'));
            }
        }

        //commit transaction
        $objORM->commit();
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
            'id:integer', 'group:integer', 'title', 'type:integer', 'restriction:integer', 'total_votes:integer',
            'result_view:boolean', 'start_time:integer', 'stop_time:integer', 'published:boolean');
        return $table->where('id', $id)->fetchRow();
    }

    /**
     * Check user allow vote for IP
     *
     * @access  public
     * @param   int     $id    poll id
     * @param   string  $ip    IP address
     * @return  bool    True or False
     */
    function CheckAllowVoteForIP($id, $ip)
    {
        $table = Jaws_ORM::getInstance()->table('poll_results');
        $res = $table->select('count(id):integer')
            ->where('poll', (int)$id)
            ->and()->where('ip', $ip)
            ->fetchOne();

        if (Jaws_Error::IsError($res)) {
            return $res;
        }
        return ($res > 0) ? false : true;
    }

    /**
     * Check user allow vote for User
     *
     * @access  public
     * @param   int     $id    poll id
     * @param   string  $user    User id
     * @return  bool    True or False
     */
    function CheckAllowVoteForUser($id, $user)
    {
        $table = Jaws_ORM::getInstance()->table('poll_results');
        $res = $table->select('count(id):integer')
            ->where('poll', (int)$id)
            ->and()->where('user', (int)$user)
            ->fetchOne();

        if (Jaws_Error::IsError($res)) {
            return $res;
        }
        return ($res > 0) ? false : true;
    }

    /**
     * Check user allow vote for session
     *
     * @access  public
     * @param   int     $id         poll id
     * @param   string  $session    Session str
     * @return  bool    True or False
     */
    function CheckAllowVoteForSession($id, $session)
    {
        $table = Jaws_ORM::getInstance()->table('poll_results');
        $res = $table->select('count(id):integer')
            ->where('poll', (int)$id)
            ->and()->where('session', $session)
            ->fetchOne();

        if (Jaws_Error::IsError($res)) {
            return $res;
        }
        return ($res > 0) ? false : true;
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
                    'id:integer', 'group:integer', 'title', 'type:integer', 'restriction:integer', 'total_votes:integer',
                    'result_view:boolean', 'start_time:integer', 'stop_time:integer', 'published:boolean');

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
            'id', 'group', 'title', 'type:integer', 'restriction:integer', 'total_votes:integer',
            'result_view:boolean', 'start_time:integer', 'stop_time:integer', 'published:integer');

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