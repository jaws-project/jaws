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
                [[users]].[username], [[users]].[nickname], [[users]].[registered_date] as user_registered_date,
                [[users]].[email], [[users]].[avatar], [[users]].[last_update] as user_last_update
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
     * @param   int     $tid    Topic ID
     * @param   int     $limit  Count of posts to be returned
     * @param   int     $offset Offset of data array
     * @return  mixed   Array of topics or Jaws_Error on failure
     */
    function GetPosts($tid, $limit = 0, $offset = null)
    {
        $params = array();
        $params['tid'] = $tid;

        $sql = '
            SELECT
                [[forums_posts]].[id], [uid], [message], [last_update_uid], [last_update_reason],
                [last_update_time], [[forums_posts]].[createtime], [[forums_posts]].[status],
                [[users]].[username], [[users]].[nickname], [[users]].[registered_date] as user_registered_date,
                [[users]].[email], [[users]].[avatar], [[users]].[last_update] as user_last_update
            FROM
                [[forums_posts]] 
            LEFT JOIN
                [[users]] ON [[forums_posts]].[uid] = [[users]].[id]
            WHERE
                [tid] = {tid}
            ORDER BY
                [createtime] ASC';

        if (!empty($limit)) {
            $result = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

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
            $result = $tModel->UpdateTopicStatistics($params['tid'], $new_topic? $pid : 0, $params['now']);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        if (!Jaws_Error::IsError($fModel)) {
            $result = $fModel->UpdateForumStatistics($fid);
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
     * @param   int     $pid    Post ID
     * @param   int     $tid    Topic ID
     * @param   int     $fid    Forum ID
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function DeletePost($pid, $tid, $fid)
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
        $result = $tModel->UpdateTopicStatistics($tid);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        $result = $fModel->UpdateForumStatistics($fid);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

}