<?php
/**
 * Forum Gadget
 *
 * @category   GadgetModel
 * @package    Forum
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forum_Model_Posts extends Jaws_Gadget_Model
{
    /**
     * Get posts count of user
     *
     * @access  public
     * @param   int     $user_id    User's ID
     * @return  int     Count Of User's Posts
     */
    function GetUserPostsCount($user_id)
    {
        $params = array();
        $params['uid'] = (int)$user_id;
        $sql = 'SELECT COUNT([id]) FROM [[forums_posts]] WHERE [uid] = {uid}';
        $count = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($count) || !$count) {
            return $count;
        }

        return $count;
    }

    /**
     * Get posts of topic
     *
     * @access  public
     * @param   int     $tid        Topic's ID
     * @param   bool    $limit      Count of topics to be returned
     * @param   int     $offset     Offset of data array
     * @return  array   Array of topics or Jaws_Error on failure
     */
    function GetPosts($tid, $limit = false, $offset = null)
    {
        $sql = '
            SELECT
                [[forums_posts]].[id], [[forums_posts]].[message], [[forums_posts]].[createtime],
                [[users]].[username], [[users]].[nickname], [[users]].[registered_date] AS user_joined_time,
                [[forums_posts]].[uid], [[forums_posts]].[last_update_uid], [[forums_posts]].[last_update_reason],
                [[forums_posts]].[last_update_time], [[forums_posts]].[status]
            FROM [[forums_posts]] 
                LEFT JOIN [[users]] ON [[forums_posts]].[uid] = [[users]].[id] ';
        $sql.= (empty($tid)? '' : 'WHERE [tid] = {tid} ') . 'ORDER BY [createtime] ASC';

        $params = array();
        $params['tid'] = $tid;

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_GET_POSTS'), _t('FORUM_NAME'));
        }

        return $result;
    }

    /**
     * Get post's info
     *
     * @access  public
     * @param   int     $pid        Post's ID
     * @return  array   Array of selected post's info
     */
    function GetPostInfo($pid)
    {
        $sql = '
            SELECT
                [[forums_posts]].[id], [[forums_posts]].[tid], [[forums_posts]].[message], [[forums_posts]].[createtime],
                [[users]].[username], [[users]].[nickname], [[users]].[registered_date] AS user_joined_time,
                [[forums_posts]].[uid], [[forums_posts]].[last_update_uid], [[forums_posts]].[last_update_reason],
                [[forums_posts]].[last_update_time], [[forums_posts]].[status]
            FROM [[forums_posts]] 
                LEFT JOIN [[users]] ON [[forums_posts]].[uid] = [[users]].[id] 
                WHERE [[forums_posts]].[id] = {pid}';

        $params = array();
        $params['pid'] = (int)$pid;

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_GET_POST_INFO'), _t('FORUM_NAME'));
        }

        return $result;
    }

    /**
     * Insert new post
     *
     * @access  public
     * @param   int     $uid
     * @param   int     $tid
     * @param   string  $message
     */
    function InsertPost($uid, $tid, $message)
    {
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['uid']        = (int)$uid;
        $params['tid']        = (int)$tid;
        $params['now']        = $GLOBALS['db']->Date();

        $sql = "
            INSERT INTO [[forums_posts]]
                ([tid], [uid], [message], [ip], [createtime])
            VALUES
                ({tid}, {uid}, {message}, {ip}, {now})";

        $params['message']   = $xss->filter($message);
        $params['ip']        = $_SERVER['REMOTE_ADDR'];
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_INSERT_POST'), _t('FORUM_NAME'));
        }
        $post_id = $GLOBALS['db']->lastInsertID('forums_posts', 'id');

        $tModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        $result = $tModel->UpdateTopicStatistics($params['tid'], $post_id, $params['now']);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $topicInfo = $tModel->GetTopic($params['tid']);
        $fModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Forums');
        $result = $fModel->UpdateForumStatistics($topicInfo['fid'], $post_id, $params['now']);
        if (Jaws_Error::IsError($result)) {
            return false;
        }
        return $post_id;
    }

    /**
     * Update post
     *
     * @access  public
     * @param   int        $pid             Post's ID
     * @param   int        $uid             User's ID
     * @param   string     $update_reason   Last Update Reason
     * @param   string  $message
     */
    function UpdatePost($pid, $uid, $subject, $message, $update_reason)
    {
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['uid']           = (int)$uid;
        $params['pid']           = (int)$pid;
        $params['now']           = $GLOBALS['db']->Date();
        $params['subject']       = $xss->filter($subject);
        $params['message']       = $xss->filter($message);
        $params['update_reason'] = $xss->filter($update_reason);

        $sql = 'UPDATE [[forums_posts]] SET
                    [message]            = {message},
                    [last_update_uid]    = {uid},
                    [last_update_reason] = {update_reason},
                    [last_update_time]   = {now}
                WHERE [id] = {pid}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_EDIT_POST'), _t('FORUM_NAME'));
        }

        if (!empty($subject)) {
            $sql = 'UPDATE [[forums_topics]] SET
                        [subject]         = {subject}
                    WHERE [first_post_id] = {pid}';
            $result = $GLOBALS['db']->query($sql, $params);
        }

        return true;
    }

    /**
     * Delete post
     *
     * @access  public
     * @param   int        $pid             Post's ID
     */
    function DeletePost($pid)
    {
        $pid = (int)$pid;
        $postInfo = $this->GetPostInfo($pid);
        if (Jaws_Error::IsError($postInfo)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_POST_NOT_DELETED'), _t('FORUM_NAME'));
        }

        $params = array();
        $params['id']  = $pid;
        $sql = "DELETE FROM [[forums_posts]] WHERE [id] = {id}";
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_POST_NOT_DELETED'), _t('FORUM_NAME'));
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        $topicInfo = $tModel->GetTopic($postInfo['tid']);

        $lastPostInfo = $this->GetLastPostTopicID($topicInfo['id']);
        $result = $tModel->UpdateTopicStatistics($topicInfo['id'], $lastPostInfo['id'], $lastPostInfo['createtime']);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Forums');
        $lastPostInfo = $this->GetLastPostForumID($topicInfo['fid']);
        if (!Jaws_Error::IsError($lastPostInfo)) {
            if (empty($lastPostInfo['id'])) {
                $lastPostInfo['id'] = 0;
                $lastPostInfo['createtime'] = 0;
            }
            $result = $fModel->UpdateForumStatistics($topicInfo['fid'], $lastPostInfo['id'], $lastPostInfo['createtime']);
        } else {
            return false;
        }

        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Return LastPost's ID  For Selected Topic
     *
     * @access  public
     * @param   int        $tid             Topic's ID
     */
    function GetLastPostTopicID($tid)
    {
        $sql = 'SELECT [id], [createtime]
                    FROM [[forums_posts]]
                    WHERE [tid] = {tid}
                    ORDER BY [id] DESC';

        $params = array();
        $params['tid'] = $tid;
        $result = $GLOBALS['db']->queryRow($sql, $params);

        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_GET_POST_INFO'), _t('FORUM_NAME'));
        }

        return $result;
    }

    /**
     * Return LastPost's ID  For Selected Forum
     *
     * @access  public
     * @param   int        $fid             Forum's ID
     */
    function GetLastPostForumID($fid)
    {
        $sql = 'SELECT [[forums_posts]].[id], [[forums_posts]].[createtime]
            FROM [[forums_posts]]
            LEFT JOIN [[forums_topics]] ON [[forums_posts]].[tid] = [[forums_topics]].[id]
            LEFT JOIN [[forums]] ON [[forums_topics]].[fid] = [[forums]].[id]
            WHERE [[forums_topics]].[fid] = {fid}
            ORDER BY [[forums_posts]].[id] DESC';

        $params = array();
        $params['fid'] = $fid;
        $result = $GLOBALS['db']->queryRow($sql, $params);

        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_GET_POST_INFO'), _t('FORUM_NAME'));
        }

        return $result;
    }
}