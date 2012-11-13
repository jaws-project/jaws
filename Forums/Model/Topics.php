<?php
/**
 * Forums Gadget
 *
 * @category    GadgetModel
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
  * @author     Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Model_Topics extends Jaws_Gadget_Model
{
    /**
     * Get topic info
     *
     * @access  public
     * @param   int     $tid    Topic ID
     * @param   int     $fid    Forum ID
     * @return  mixed   Array of topic info or Jaws_Error on failure
     */
    function GetTopic($tid, $fid = null)
    {
        $params = array();
        $params['tid'] = (int)$tid;
        $params['fid'] = (int)$fid;

        $sql = '
            SELECT
                [[forums_topics]].[id], [fid], [subject], [views], [replies],
                [first_post_id], [first_post_time], [last_post_id], [last_post_time],
                [[forums_topics]].[published], [[forums_topics]].[locked],
                [[forums]].[title] as forum_title, [[forums]].[fast_url] as forum_fast_url,
                [[forums]].[last_topic_id] as forum_last_topic_id,
                [[forums_posts]].[message], [[forums_posts]].[last_update_reason],
                [[users]].[username], [[users]].[nickname]
            FROM
                [[forums_topics]]
            LEFT JOIN
                [[forums]] ON [[forums_topics]].[fid] = [[forums]].[id]
            LEFT JOIN
                [[forums_posts]] ON [[forums_topics]].[first_post_id] = [[forums_posts]].[id]
            LEFT JOIN
                [[users]] ON [[forums_posts]].[uid] = [[users]].[id]
            WHERE
                [[forums_topics]].[id] = {tid}';

        if (!empty($fid)) {
            $sql .= ' AND [fid] = {fid}';
        }

        $types = array(
            'integer', 'integer', 'text', 'integer', 'integer',
            'integer', 'timestamp', 'integer', 'timestamp',
            'boolean', 'boolean',
            'text', 'text',
            'integer',
            'text', 'text',
            'text', 'text',
        );

        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
        return $result;
    }

    /**
     * Get topics of forum
     *
     * @access  public
     * @param   int     $fid        Forum ID
     * @param   int     $limit      Count of topics to be returned
     * @param   int     $offset     Offset of data array
     * @return  mixed   Array of topics or Jaws_Error on failure
     */
    function GetTopics($fid, $limit = 0, $offset = null)
    {
        $params = array();
        $params['fid'] = $fid;

        $sql = '
            SELECT
                [[forums_topics]].[id], [fid], [subject], [views], [replies],
                [first_post_id], [first_post_uid], [first_post_time],
                [last_post_id], [last_post_uid], [last_post_time],
                [username], [nickname], [locked], [published]
            FROM
                [[forums_topics]]
            LEFT JOIN 
                [[users]] ON [[forums_topics]].[last_post_uid] = [[users]].[id]
            WHERE
                [fid] = {fid}
            ORDER BY
                [last_post_time] DESC';

        $types = array(
            'integer', 'integer', 'text', 'integer', 'integer',
            'integer', 'integer', 'timestamp',
            'integer', 'integer', 'timestamp',
            'text', 'text', 'boolean', 'boolean',
        );

        if (!empty($limit)) {
            $result = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        return $result;
    }

    /**
     * Insert new topic
     *
     * @access  public
     * @param   int     $uid        User's ID
     * @param   int     $fid        Forum ID
     * @param   string  $subject    Topic subject
     * @param   string  $message    Topic first post content
     * @param   bool    $published  Must be published?
     * @return  mixed   Topic ID on successfully or Jaws_Error on failure
     */
    function InsertTopic($uid, $fid, $subject, $message, $published = true)
    {
        $params = array();
        $params['uid']       = $uid;
        $params['fid']       = (int)$fid;
        $params['subject']   = $subject;
        $params['published'] = (bool)$published;

        $sql = '
            INSERT INTO [[forums_topics]]
                ([fid], [subject], [first_post_uid], [last_post_uid], [published])
            VALUES
                ({fid}, {subject}, {uid}, {uid}, {published})';

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        $tid = $GLOBALS['db']->lastInsertID('forums_topics', 'id');
        if (Jaws_Error::IsError($tid)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $tid;
        }

        $pid = 0;
        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        if (!Jaws_Error::IsError($pModel)) {
            $pid = $pModel->InsertPost($params['uid'], $tid, $params['fid'], $message, true);
            if (Jaws_Error::IsError($pid)) {
                //Rollback Transaction
                $GLOBALS['db']->dbc->rollback();
                return $pid;
            }
        }

        //Commit Transaction
        $GLOBALS['db']->dbc->commit();

        return $tid;
    }

    /**
     * Update topic
     *
     * @access  public
     * @param   int     $fid            Forum ID
     * @param   int     $tid            Topic ID
     * @param   int     $pid            Topic first post ID
     * @param   int     $uid            User's ID
     * @param   string  $subject        Topic subject
     * @param   string  $message        First post content
     * @param   bool    $published      Topic publish status
     * @param   string  $update_reason  Update reason text
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdateTopic($fid, $tid, $pid, $uid, $subject, $message, $published = null, $update_reason = '')
    {
        $params = array();
        $params['fid'] = (int)$fid;
        $params['tid'] = (int)$tid;
        $params['subject']   = $subject;
        $params['published'] = true;

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $sql = '
            UPDATE [[forums_topics]] SET
                [fid]       = {fid},
                [subject]   = {subject},
                [published] = {published}
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        $result = $pModel->UpdatePost($pid, $uid, $message, $update_reason);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        //Commit Transaction
        $GLOBALS['db']->dbc->commit();

        return  $tid;
    }

    /**
     * Delete topic
     *
     * @access  public
     * @param   int     $tid            Topic ID
     * @param   int     $fid            Forum ID
     * @param   int     $last_topic_id  Forum last topic ID
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function DeleteTopic($tid, $fid, $last_topic_id)
    {
        $params = array();
        $params['tid'] = $tid;

        $sql = '
            DELETE FROM [[forums_posts]]
            WHERE
                [tid] = {tid}';

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        $sql = '
            DELETE FROM [[forums_topics]]
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        //Commit Transaction
        $GLOBALS['db']->dbc->commit();

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        if (!Jaws_Error::IsError($fModel)) {
            $result = $fModel->UpdateForumStatistics($fid, $last_topic_id);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Update last_post_id, last_post_time and count of replies
     *
     * @access  public
     * @param   int         $tid            Topic ID
     * @param   int         $last_post_id   Last post ID
     * @param   timestamp   $last_post_time Last post time
     * @param   timestamp   $first_post_id  First post ID
     * @return  mixed       True on successfully or Jaws_Error on failure
     */
    function UpdateTopicStatistics($tid, $last_post_id, $last_post_time, $first_post_id = null)
    {
        $params = array();
        $params['tid'] = (int)$tid;
        $params['first_post_id']   = $first_post_id;
        $params['first_post_time'] = $last_post_time;
        $params['last_post_id']    = $last_post_id;
        $params['last_post_time']  = $last_post_time;
        if (empty($first_post_id)) {
            $first_post_id   = '[first_post_id]';
            $first_post_time = '[first_post_time]';
        } else {
            $first_post_id   = '{first_post_id}';
            $first_post_time = '{first_post_time}';
        }

        $sql = "
            UPDATE [[forums_topics]] SET
                [first_post_id]   = $first_post_id,
                [first_post_time] = $first_post_time,
                [last_post_id]    = {last_post_id},
                [last_post_time]  = {last_post_time},
                [replies]         = (
                    SELECT
                        COUNT([[forums_posts]].[id])
                    FROM
                        [[forums_posts]]
                    WHERE
                        [[forums_posts]].[tid] = {tid}
                )
            WHERE
                [id] = {tid}";

        $result = $GLOBALS['db']->query($sql, $params);
        return $result;
    }

    /**
     * Lock/UnLock topic
     *
     * @access  public
     * @param   int     $tid        Topic ID
     * @param   bool    $locked     True: Locked, False: UnLocked
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function LockTopic($tid, $locked)
    {
        $params = array();
        $params['tid']    = (int)$tid;
        $params['locked'] = $locked;

        $sql = '
            UPDATE [[forums_topics]] SET
                [locked]   = {locked}
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        return $result;
    }

    /**
     * Update topic views
     *
     * @access  public
     * @param   int     $tid    Topic ID
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdateTopicViews($tid)
    {
        $params = array();
        $params['tid'] = (int)$tid;
        $params['one'] = 1;

        $sql = '
            UPDATE [[forums_topics]] SET
                [views] = [views] + {one}
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        return $result;
    }

}