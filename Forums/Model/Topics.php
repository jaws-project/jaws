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
                [[forums_topics]].[published], [[forums_topics]].[locked],
                [[forums_topics]].[first_post_id], [[forums_topics]].[createtime],
                [[forums_topics]].[last_post_id], [[forums_topics]].[last_post_time],
                [[forums]].[title], [[forums]].[fast_url] as forums_fast_url,
                [[forums_posts]].[message]
            FROM
                [[forums_topics]]
            LEFT JOIN
                [[forums]] ON [[forums_topics]].[fid] = [[forums]].[id]
            LEFT JOIN
                [[forums_posts]] ON [[forums_topics]].[first_post_id] = [[forums_posts]].[id]
            WHERE
                [[forums_topics]].[id] = {tid}';

        if (!empty($fid)) {
            $sql .= ' AND [fid] = {fid}';
        }

        $types = array(
            'integer', 'integer', 'text', 'integer', 'integer',
            'boolean', 'boolean',
            'integer', 'timestamp',
            'integer', 'timestamp',
            'text', 'text',
            'text',
        );

        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
        return $result;
    }

    /**
     * Get topics of forum
     *
     * @access  public
     * @param   int     $fid        Forum's ID
     * @param   bool    $limit      Count of topics to be returned
     * @param   int     $offset     Offset of data array
     * @return  mixed   Array of topics or Jaws_Error on failure
     */
    function GetTopics($fid, $limit = false, $offset = null)
    {
        $params = array();
        $params['fid'] = $fid;

        $sql = '
            SELECT
                [[forums_topics]].[id], [[forums_topics]].[subject], [views], [[forums_topics]].[published],
                [[forums_topics]].[createtime], [replies], [[forums_topics]].[locked], [last_post_time],
                [[users]].[username], [[users]].[nickname],
                [[forums_topics]].[first_post_id], [[forums_topics]].[last_post_id],
                [[forums_topics]].[uid]
            FROM [[forums_topics]] 
                LEFT JOIN [[forums_posts]] ON [[forums_topics]].[last_post_id] = [[forums_posts]].[id]
                LEFT JOIN [[users]] ON [[forums_posts]].[uid] = [[users]].[id]
            WHERE
                [fid] = {fid}
            ORDER BY
                [last_post_time] DESC';

        $result = $GLOBALS['db']->queryAll($sql, $params);
        return $result;
    }

    /**
     * Insert new topic
     *
     * @access  public
     * @param   int     $uid        User's ID
     * @param   int     $fid        Forum ID
     * @param   string  $subject    Topic subject
     * @param   string  $fast_url   Topic fast-url
     * @param   string  $message    Topic first post content
     * @param   bool    $published  Must be published?
     * @return  mixed   Topic ID on successfully or Jaws_Error on failure
     */
    function InsertTopic($uid, $fid, $subject, $fast_url, $message, $published = true)
    {
        $fast_url = empty($fast_url)? $subject : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'forums_topics');

        $params = array();
        $params['uid']       = $uid;
        $params['fid']       = (int)$fid;
        $params['subject']   = $subject;
        $params['fast_url']  = $fast_url;
        $params['now']       = $GLOBALS['db']->Date();
        $params['published'] = (bool)$published;

        $sql = '
            INSERT INTO [[forums_topics]]
                ([uid], [fid], [subject], [fast_url], [last_post_time], [createtime], [published])
            VALUES
                ({uid}, {fid}, {subject}, {fast_url}, {now}, {now}, {published})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $tid = $GLOBALS['db']->lastInsertID('forums_topics', 'id');
        if (Jaws_Error::IsError($tid)) {
            return $tid;
        }

        $pid = 0;
        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        if (!Jaws_Error::IsError($pModel)) {
            $pid = $pModel->InsertPost($params['uid'], $tid, $message);
            if (Jaws_Error::IsError($pid)) {
                return $pid;
            }
        }

        $params['tid'] = $tid;
        $params['first_post_id'] = $pid;

        $sql = '
            UPDATE [[forums_topics]] SET
                [first_post_id] = {first_post_id}
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $tid;
    }

    /**
     * Update topic
     *
     * @access  public
     * @param   int     $fid            Forum ID
     * @param   int     $tid            Topic ID
     * @param   int     $pid            Topic first post ID
     * @param   string  $subject        Topic subject
     * @param   string  $fast_url       Topic fast url
     * @param   string  $message        First post content
     * @param   bool    $published      Topic publish status
     * @param   string  $update_reason  Update reason text
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdateTopic($fid, $tid, $pid, $subject, $fast_url, $message, $published = null, $update_reason = '')
    {
        $fast_url = empty($fast_url)? $subject : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'forums_topics', false);

        $params = array();
        $params['fid'] = (int)$fid;
        $params['tid'] = (int)$tid;
        $params['pid'] = (int)$pid;
        $params['now'] = $GLOBALS['db']->Date();
        $params['subject']  = $subject;
        $params['fast_url'] = $fast_url;
        $params['message']  = $message;
        $params['published'] = true;
        $params['update_reason'] = $update_reason;

        $sql = '
            UPDATE [[forums_topics]] SET
                [fid]       = {fid},
                [subject]   = {subject},
                [fast_url]  = {fast_url},
                [published] = {published}
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $sql = '
            UPDATE [[forums_posts]] SET
                [message]            = {message},
                [last_update_reason] = {update_reason},
                [last_update_time]   = {now}
            WHERE
                [id] = {pid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return  $tid;
    }

    /**
     * Delete topic
     *
     * @access  public
     * @param   int     $tid    Topic ID
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function DeleteTopic($tid)
    {
        $tid = (int)$tid;
        $topic = $this->GetTopic($tid);
        if (Jaws_Error::IsError($topic)) {
            return $topic;
        }

        $params = array();
        $params['fid'] = $topic['fid'];
        $params['tid'] = $topic['id'];
        $sql = '
            DELETE FROM [[forums_posts]]
            WHERE
                [tid] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $sql = '
            DELETE FROM [[forums_topics]]
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        if (!Jaws_Error::IsError($pModel)) {
            $lastpost = $pModel->GetLastPostForumID($topic['fid']);
            if (Jaws_Error::IsError($lastpost)) {
                return $lastpost;
            }

            $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
            if (!Jaws_Error::IsError($fModel)) {
                $result = $fModel->UpdateForumStatistics($topic['fid'], $lastpost['id'], $lastpost['createtime']);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        }

        return true;
    }

    /**
     * Update last_post_id, last_post_time and count of replies
     *
     * @access  public
     * @param   int         $tid                Topic ID
     * @param   int         $last_post_id       Last post ID
     * @param   timestamp   $last_post_time     Last post time
     * @return  mixed       True on successfully or Jaws_Error on failure
     */
    function UpdateTopicStatistics($tid, $last_post_id, $last_post_time)
    {
        $params = array();
        $params['tid']            = (int)$tid;
        $params['last_post_id']   = $last_post_id;
        $params['last_post_time'] = $last_post_time;

        $sql = '
            UPDATE [[forums_topics]] SET
                [last_post_id]   = {last_post_id},
                [last_post_time] = {last_post_time},
                [replies]        = (
                    SELECT
                        COUNT([[forums_posts]].[id])
                    FROM
                        [[forums_posts]]
                    WHERE
                        [[forums_posts]].[tid] = {tid}
                )
            WHERE
                [id] = {tid}';

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