<?php
/**
 * Forums Gadget
 *
 * @category    GadgetModel
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Model_Posts extends Jaws_Gadget_Model
{
    /**
     * Get post data
     *
     * @access  public
     * @param   int     $pid    Post ID
     * @param   int     $tid    Topic ID
     * @param   int     $fid    Forum ID
     * @return  mixed   Array of post data or Jaws_Error on failure
     */
    function GetPost($pid, $tid = null, $fid = null)
    {
        $params = array();
        $params['fid'] = (int)$fid;
        $params['tid'] = (int)$tid;
        $params['pid'] = (int)$pid;

        $sql = '
            SELECT
                [[forums_posts]].[id], [[forums_posts]].[uid], [tid], [message], [[forums_posts]].[createtime],
                [last_update_uid], [last_update_reason], [last_update_time], [[forums_posts]].[status],
                [[forums_topics]].[fid], [[forums_topics]].[subject],
                [first_post_id] as topic_first_post_id, [first_post_time] as topic_first_post_time,
                [last_post_id] as topic_last_post_id, [last_post_time] as topic_last_post_time,
                [[forums]].[title] as forum_title, [[forums]].[fast_url] as forum_fast_url,
                [[forums]].[last_topic_id] as forum_last_topic_id,
                [[users]].[username], [[users]].[nickname], [[users]].[registered_date]
            FROM
                [[forums_posts]]
            LEFT JOIN
                [[forums_topics]] ON [[forums_posts]].[tid] = [[forums_topics]].[id]
            LEFT JOIN
                [[forums]] ON [[forums_topics]].[fid] = [[forums]].[id]
            LEFT JOIN
                [[users]] ON [[forums_posts]].[uid] = [[users]].[id]
            WHERE
                [[forums_posts]].[id] = {pid}';

        if (!empty($tid)) {
            $sql .= ' AND [tid] = {tid}';
        }
        if (!empty($fid)) {
            $sql .= ' AND [fid] = {fid}';
        }

        $result = $GLOBALS['db']->queryRow($sql, $params);
        return $result;
    }

    /**
     * Get posts of topic
     *
     * @access  public
     * @param   int     $tid    Topic's ID
     * @param   bool    $limit  Count of topics to be returned
     * @param   int     $offset Offset of data array
     * @return  mixed   Array of topics or Jaws_Error on failure
     */
    function GetPosts($tid, $limit = false, $offset = null)
    {
        $params = array();
        $params['tid'] = $tid;

        $sql = '
            SELECT
                [[forums_posts]].[id], [[forums_posts]].[message], [[forums_posts]].[createtime],
                [[users]].[username], [[users]].[nickname], [[users]].[registered_date] AS user_joined_time,
                [[forums_posts]].[uid], [[forums_posts]].[last_update_uid], [[forums_posts]].[last_update_reason],
                [[forums_posts]].[last_update_time], [[forums_posts]].[status]
            FROM [[forums_posts]] 
            LEFT JOIN [[users]] ON [[forums_posts]].[uid] = [[users]].[id]
            WHERE [tid] = {tid}
            ORDER BY [createtime] ASC';

        $result = $GLOBALS['db']->queryAll($sql, $params);
        return $result;
    }

    /**
     * Get posts count of user
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @return  mixed   Count of user's posts or Jaws_Error on failure
     */
    function GetUserPostsCount($uid)
    {
        $params = array();
        $params['uid'] = (int)$uid;

        $sql = '
            SELECT COUNT([id])
            FROM [[forums_posts]]
            WHERE [uid] = {uid}';

        $count = $GLOBALS['db']->queryOne($sql, $params);
        return $count;
    }

    /**
     * Insert new post
     *
     * @access  public
     * @param   int     $uid        User's ID
     * @param   int     $tid        Topic ID
     * @param   int     $fid        Forum ID
     * @param   string  $message    Post content
     * @param   bool    $new_topic  Is this first post of topic?
     * @return  mixed   Post ID on successfully or Jaws_Error on failure
     */
    function InsertPost($uid, $tid, $fid, $message, $new_topic = false)
    {
        $params = array();
        $params['uid'] = (int)$uid;
        $params['tid'] = (int)$tid;
        $params['now'] = $GLOBALS['db']->Date();
        $params['ip']  = $_SERVER['REMOTE_ADDR'];
        $params['message'] = $message;

        $sql = '
            INSERT INTO [[forums_posts]]
                ([tid], [uid], [message], [ip], [createtime])
            VALUES
                ({tid}, {uid}, {message}, {ip}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $pid = $GLOBALS['db']->lastInsertID('forums_posts', 'id');
        if (Jaws_Error::IsError($pid)) {
            return $pid;
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        if (!Jaws_Error::IsError($tModel)) {
            $result = $tModel->UpdateTopicStatistics(
                $params['tid'],
                $pid,
                $params['now'],
                $new_topic? $pid : null
            );
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        if (!Jaws_Error::IsError($fModel)) {
            $result = $fModel->UpdateForumStatistics(
                $fid,
                $new_topic? $params['tid'] : null
            );
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return $pid;
    }

    /**
     * Update post
     *
     * @access  public
     * @param   int     $pid            Post ID
     * @param   int     $uid            User's ID
     * @param   string  $message        Post content
     * @param   string  $update_reason  Update reason text
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdatePost($pid, $uid, $message, $update_reason = '')
    {
        $params = array();
        $params['uid'] = (int)$uid;
        $params['pid'] = (int)$pid;
        $params['now'] = $GLOBALS['db']->Date();
        $params['message'] = $message;
        $params['update_reason'] = $update_reason;

        $sql = '
            UPDATE [[forums_posts]] SET
                [message]            = {message},
                [last_update_uid]    = {uid},
                [last_update_reason] = {update_reason},
                [last_update_time]   = {now}
            WHERE
                [id] = {pid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $pid;
    }

    /**
     * Delete post
     *
     * @access  public
     * @param   int     $pid                Post ID
     * @param   int     $tid                Topic ID
     * @param   int     $fid                Forum ID
     * @param   int     $last_post_id       Topic last post ID
     * @param   string  $last_post_time     Topic last post time
     * @param   int     $forum_last_post_id Forum last topic id
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function DeletePost($pid, $tid, $fid, $last_post_id, $last_post_time, $forum_last_post_id)
    {
        $params = array();
        $params['pid'] = (int)$pid;

        $sql = '
            DELETE FROM [[forums_posts]]
            WHERE
                [id] = {pid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $result = $tModel->UpdateTopicStatistics($tid, $last_post_id, $last_post_time);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        $result = $fModel->UpdateForumStatistics($fid, $forum_last_post_id);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Get last post ID for given topic
     *
     * @access  public
     * @param   int     $tid    Topic ID
     * @return  mixed   Array of last post data or Jaws_Error on failure
     */
    function GetLastPostTopicID($tid)
    {
        $params = array();
        $params['tid'] = $tid;

        $sql = '
            SELECT
                [id], [createtime]
            FROM
                [[forums_posts]]
            WHERE
                [tid] = {tid}
            ORDER BY
                [id] DESC';

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (empty($result)) {
            $result = array(
                'id'         => 0,
                'createtime' => null
            );
        }

        return $result;
    }

    /**
     * Get last post ID for given forum
     *
     * @access  public
     * @param   int     $fid    Forum ID
     * @return  mixed   Array of last post data or Jaws_Error on failure
     */
    function GetLastPostForumID($fid)
    {
        $params = array();
        $params['fid'] = $fid;

        $sql = '
            SELECT
                [[forums_posts]].[id], [[forums_posts]].[createtime]
            FROM
                [[forums_posts]]
            LEFT JOIN
                [[forums_topics]] ON [[forums_posts]].[tid] = [[forums_topics]].[id]
            LEFT JOIN
                [[forums]] ON [[forums_topics]].[fid] = [[forums]].[id]
            WHERE
                [[forums_topics]].[fid] = {fid}
            ORDER BY
                [[forums_posts]].[id] DESC';

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (empty($result)) {
            $result = array(
                'id'         => 0,
                'createtime' => null
            );
        }

        return $result;
    }

}