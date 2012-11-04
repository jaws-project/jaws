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
     * @param   int     $pid    Post's ID
     * @return  mixed   Array of post data or Jaws_Error on failure
     */
    function GetPost($pid)
    {
        $params = array();
        $params['pid'] = (int)$pid;

        $sql = '
            SELECT
                [[forums_posts]].[id], [[forums_posts]].[tid], [[forums_posts]].[message],
                [[forums_posts]].[createtime], [[users]].[username], [[users]].[nickname],
                [[users]].[registered_date] AS user_joined_time, [[forums_posts]].[uid],
                [[forums_posts]].[last_update_uid], [[forums_posts]].[last_update_reason],
                [[forums_posts]].[last_update_time], [[forums_posts]].[status]
            FROM [[forums_posts]]
            LEFT JOIN [[users]] ON [[forums_posts]].[uid] = [[users]].[id]
            WHERE [[forums_posts]].[id] = {pid}';

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
     * @param   string  $message    Post content
     * @return  mixed   Post ID on successfully or Jaws_Error on failure
     */
    function InsertPost($uid, $tid, $message)
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
            $result = $tModel->UpdateTopicStatistics($params['tid'], $pid, $params['now']);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $topic = $tModel->GetTopic($params['tid']);
        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        if (!Jaws_Error::IsError($fModel)) {
            $result = $fModel->UpdateForumStatistics($topic['fid'], $pid, $params['now']);
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
     * @param   string  $subject        Topic subject
     * @param   string  $message        Post content
     * @param   string  $update_reason  Update reason text
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdatePost($pid, $uid, $subject, $message, $update_reason = '')
    {
        $params = array();
        $params['uid'] = (int)$uid;
        $params['pid'] = (int)$pid;
        $params['now'] = $GLOBALS['db']->Date();
        $params['subject'] = $subject;
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

        if (!empty($subject)) {
            // FIXME: check whether this post is first post of topic?
            $sql = '
                UPDATE [[forums_topics]] SET
                    [subject] = {subject}
                WHERE
                    [first_post_id] = {pid}';

            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Delete post
     *
     * @access  public
     * @param   int     $pid    Post ID
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function DeletePost($pid)
    {
        $pid = (int)$pid;
        $post = $this->GetPost($pid);
        if (Jaws_Error::IsError($post)) {
            return $post;
        }

        $params = array();
        $params['id'] = $pid;

        $sql = '
            DELETE FROM [[forums_posts]]
            WHERE
                [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $tModel->GetTopic($post['tid']);
        if (Jaws_Error::IsError($topic)) {
            return $topic;
        }

        $lastpost = $this->GetLastPostTopicID($topic['id']);
        if (Jaws_Error::IsError($lastpost)) {
            return $lastpost;
        }

        $result = $tModel->UpdateTopicStatistics($topic['id'], $lastpost['id'], $lastpost['createtime']);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $lastpost = $this->GetLastPostForumID($topic['fid']);
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